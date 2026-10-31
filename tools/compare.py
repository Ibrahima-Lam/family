import re, sqlite3, unicodedata, difflib
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DB = ROOT / 'database/famille.db'
MD = ROOT / 'famille-structuree.md'

def norm(s):
    s=unicodedata.normalize('NFKD',s.lower())
    s=''.join(c for c in s if not unicodedata.combining(c))
    s=s.replace('\\','/')
    s=re.sub(r'\b(dit|dite|ou)\b',' ',s)
    s=re.sub(r'[^a-z0-9/]+',' ',s)
    return ' '.join(s.split())

def toks(s):
    s=norm(s)
    parts=re.split(r'[/]',s)
    out=[]
    for p in parts:
        for t in p.split():
            if len(t)>1 and t not in out: out.append(t)
    return out

def sim(a,b):
    if a==b: return 1.0
    return difflib.SequenceMatcher(None,a,b).ratio()

def tmatch(a,b,th=0.82):
    return a==b or sim(a,b)>=th

def score(ta,tb):
    # greedy one-to-one token matching
    used=set(); m=0
    for x in ta:
        best=0; bi=None
        for i,y in enumerate(tb):
            if i in used: continue
            s=sim(x,y)
            if s>best: best=s; bi=i
        if bi is not None and best>=0.82:
            used.add(bi); m+=1
    if not ta or not tb: return 0.0
    return 2*m/(len(ta)+len(tb))

# ---------- DB ----------
con=sqlite3.connect(DB); con.row_factory=sqlite3.Row
rows=con.execute('SELECT idPersonne id, nomPersonne nom, generation gen, sexe FROM personne').fetchall()
children={r['id']:[] for r in rows}
parent={}
for r in con.execute('SELECT idPersonne c, idParent p FROM etre'):
    children.setdefault(r['p'],[]).append(r['c'])
    parent[r['c']]=r['p']
byid={r['id']:{'id':r['id'],'nom':r['nom'],'gen':r['gen'],'sexe':r['sexe'],'kids':children.get(r['id'],[])} for r in rows}
roots=[i for i in byid if i not in parent]

# ---------- MD ----------
class N:
    def __init__(s,t): s.text=t; s.kids=[]; s.parent=None; s.id=None; s.db=None

def parse(lines):
    roots=[]; st=[]
    for raw in lines:
        m=re.match(r'^( *)- (.*)$',raw.rstrip('\n'))
        if not m: continue
        ind=len(m.group(1)); n=N(m.group(2).strip())
        while st and st[-1][0]>=ind: st.pop()
        if st: st[-1][1].kids.append(n); n.parent=st[-1][1]
        else: roots.append(n)
        st.append((ind,n))
    return roots

text=open(MD,encoding='utf-8').read()
parts=re.split(r'^## ',text,flags=re.M)
main=None; branches=[]
for p in parts[1:]:
    ls=p.splitlines(); title=ls[0].strip(); body=ls[1:]
    if title.startswith('Lignée'): main=parse(body)
    elif title.startswith('Branche'): branches.append(parse(body)[0])

def find(n,needle):
    if needle in n.text: return n
    for k in n.kids:
        f=find(k,needle)
        if f: return f
    return None

demba=find(main[0],'Demba Maloum')
for ref,b in zip(demba.kids,branches):
    m=re.match(r'([A-C])\.',ref.text)
    letter=m.group(1) if m else ''
    nm=b.text.replace('*','').strip()
    ref.text=f'{letter}. {nm}'
    ref.kids=b.kids
    for k in ref.kids: k.parent=ref
root=main[0]

# assign ids
counter=0
def walk(n):
    global counter
    n.id=counter; counter+=1
    for k in n.kids: walk(k)
walk(root)

def clean_name(s):
    s=s.replace('*','')
    s=re.sub(r'\s*→.*$','',s)
    s=re.sub(r'\([^)]*\)','',s)
    s=re.sub(r'^\s*[A-C0-9]+\.\s*','',s)
    return s.strip()

def name_toks(n): return toks(clean_name(n.text))

# BFS matching
mapping={}   # md id -> db id
used=set()
ambiguous=[]

def pick(n,cands):
    ta=name_toks(n)
    best=None; bs=-1
    for did in cands:
        if did in used: continue
        tb=toks(byid[did]['nom'])
        sc=score(ta,tb)
        fm = bool(ta and tb and tmatch(ta[0],tb[0]))
        key=(sc,1 if fm else 0)
        if best is None or key>(bs,bk):
            best=did; bs,bk=sc,1 if fm else 0
    if best is None: return None,0
    if bs>=0.34 or (bk and bs>0):
        return best,bs
    return None,bs

# root against all
did,sc=pick(root,list(byid))
if did is not None:
    mapping[root.id]=did; used.add(did); root.db=did
else:
    did,sc=None,0
print('ROOT MATCH:',did,sc,byid[did]['nom'] if did else None)

queue=[root]
while queue:
    n=queue.pop(0)
    if n.id not in mapping:
        # try global if parent unknown
        did,sc=pick(n,[i for i in byid if i not in used])
        if did is not None: mapping[n.id]=did; used.add(did); n.db=did
        else: continue
    dbid=mapping[n.id]; n.db=dbid
    kids=[k for k in n.kids]
    cands=[c for c in byid[dbid]['kids']]
    for k in kids:
        did,sc=pick(k,cands)
        if did is not None:
            mapping[k.id]=did; used.add(did); k.db=did
            if sc<0.7: ambiguous.append((k,byid[did]['nom'],sc))
        queue.append(k)

# descendants of unmatched md nodes: mark all unmatched
def subtree(n):
    yield n
    for k in n.kids: yield from subtree(k)

md_all=list(subtree(root))
md_only=[n for n in md_all if n.id not in mapping]
db_only=[i for i in byid if i not in used]

print('\n=== COUNTS ===')
print('DB persons:',len(byid),'links:',sum(len(v) for v in children.values()))
print('MD nodes:',len(md_all))
print('matched:',len(mapping),'md only:',len(md_only),'db only:',len(db_only))
print('ambiguous (score<0.7):',len(ambiguous))

# collapse missing branches
def top_missing(nodes):
    ids={n.id for n in nodes}
    return [n for n in nodes if not (n.parent and n.parent.id in ids)]

print('\n=== MD ONLY (top of missing branch, count incl. descendants) ===')
for n in top_missing(md_only):
    print(f"- {clean_name(n.text)}  [{len(list(subtree(n)))} nœuds]")

print('\n=== DB ONLY (top, count incl. descendants) ===')
def dbsub(i):
    yield i
    for c in byid[i]['kids']: yield from dbsub(c)
dbonly_set=set(db_only)
for i in db_only:
    p=parent.get(i)
    if p in dbonly_set: continue
    print(f"- {byid[i]['nom']}  (gen {byid[i]['gen']})  [{len(list(dbsub(i)))} nœuds]")

print('\n=== AMBIGUOUS MATCHES (md -> db, score) ===')
for n,nm,sc in ambiguous:
    print(f"- {clean_name(n.text)}  ->  {nm}   ({sc:.2f})")

print('\n=== MATCHED PAIRS (md | db | score) ===')
for n in md_all:
    if n.id in mapping:
        d=byid[mapping[n.id]]
        sc=score(name_toks(n),toks(d['nom']))
        print(f"{clean_name(n.text)} | {d['nom']} | {sc:.2f}")
