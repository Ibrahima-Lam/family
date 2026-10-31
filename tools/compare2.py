import re, sqlite3, unicodedata, difflib
from itertools import permutations
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DB = ROOT / 'database/famille.db'
MD = ROOT / 'famille-structuree.md'
TOK_TH=0.65
FIRST_TH=0.70
T=0.50

def norm(s):
    s=unicodedata.normalize('NFKD',s.lower())
    s=''.join(c for c in s if not unicodedata.combining(c))
    s=s.replace('\\','/')
    s=re.sub(r'\b(dit|dite|ou)\b',' ',s)
    s=re.sub(r'[^a-z0-9/]+',' ',s)
    return ' '.join(s.split())

def toks(s):
    s=norm(s); out=[]
    for p in s.split('/'):
        for t in p.split():
            if len(t)>1 and t not in out: out.append(t)
    return out

def sim(a,b):
    return 1.0 if a==b else difflib.SequenceMatcher(None,a,b).ratio()

def dice(ta,tb):
    if not ta or not tb: return 0.0
    used=set(); m=0
    for x in ta:
        best=0; bi=None
        for i,y in enumerate(tb):
            if i in used: continue
            s=sim(x,y)
            if s>best: best=s; bi=i
        if bi is not None and best>=TOK_TH:
            used.add(bi); m+=1
    return 2*m/(len(ta)+len(tb))

def nscore(ta,tb):
    sc=dice(ta,tb)
    if ta and tb and sim(ta[0],tb[0])>=FIRST_TH: sc+=0.25
    return sc

# ---- DB ----
con=sqlite3.connect(DB); con.row_factory=sqlite3.Row
rows=con.execute('SELECT idPersonne id, nomPersonne nom, generation gen, sexe FROM personne').fetchall()
kids={r['id']:[] for r in rows}; parent={}
for r in con.execute('SELECT idPersonne c, idParent p FROM etre'):
    kids.setdefault(r['p'],[]).append(r['c']); parent[r['c']]=r['p']
byid={r['id']:{'id':r['id'],'nom':r['nom'],'gen':r['gen'],'kids':kids.get(r['id'],[])} for r in rows}
db_roots=[i for i in byid if i not in parent]
db_toks={i:toks(byid[i]['nom']) for i in byid}

# ---- MD ----
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
    m=re.match(r'([A-C])\.',ref.text); letter=m.group(1) if m else ''
    nm=b.text.replace('*','').strip()
    ref.text=f'{letter}. {nm}'
    ref.kids=b.kids
    for k in ref.kids: k.parent=ref
root=main[0]

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

def md_toks(n): return toks(clean_name(n.text))

def kids_overlap(md, did):
    A=[md_toks(k) for k in md.kids]
    B=[db_toks[c] for c in byid[did]['kids']]
    if not A or not B: return 0.0
    used=set(); tot=0
    for a in A:
        best=0; bi=None
        for j,b in enumerate(B):
            if j in used: continue
            s=nscore(a,b)
            if s>best: best=s; bi=j
        if bi is not None: used.add(bi); tot+=best
    return tot/max(len(A),len(B))

def pair_score(md, did):
    return nscore(md_toks(md), db_toks[did]) + 0.8*kids_overlap(md, did)

def assign(md_kids, db_kids):
    m,n=len(md_kids),len(db_kids)
    if m==0 or n==0: return []
    S=[[pair_score(k, d) for d in db_kids] for k in md_kids]
    best={'val':-1e9,'pairs':[]}
    def rec(i, used, pairs, val):
        if i==m:
            if val>best['val']: best['val']=val; best['pairs']=list(pairs)
            return
        rec(i+1, used, pairs, val)
        for j in range(n):
            if used>>j & 1: continue
            if S[i][j]-T <= 0: continue
            rec(i+1, used|1<<j, pairs+[(i,j)], val+S[i][j]-T)
    rec(0,0,[],0.0)
    return [(md_kids[i], db_kids[j], S[i][j]) for i,j in best['pairs']]

mapping={}; used=set(); amb=[]
# root
rs=[(root, r, pair_score(root,r)) for r in db_roots]
root_pair=max(rs, key=lambda x:x[2])
if root_pair[2]-T>0:
    mapping[root.id]=root_pair[1]; used.add(root_pair[1]); root.db=root_pair[1]

queue=[root]
while queue:
    n=queue.pop(0)
    if n.id not in mapping:
        continue
    for k, did, sc in assign(n.kids, byid[mapping[n.id]]['kids']):
        if did in used: continue
        mapping[k.id]=did; used.add(did); k.db=did
        if sc<0.75: amb.append((k,byid[did]['nom'],sc))
    queue.extend(n.kids)

def subtree(n):
    yield n
    for k in n.kids: yield from subtree(k)
def dbsub(i):
    yield i
    for c in byid[i]['kids']: yield from dbsub(c)

md_all=list(subtree(root))
md_only=[n for n in md_all if n.id not in mapping]
db_only=[i for i in byid if i not in used]

print('=== COUNTS ===')
print(f"DB: {len(byid)} personnes, {sum(len(v) for v in kids.values())} liens")
print(f"MD: {len(md_all)} nœuds")
print(f"appariés: {len(mapping)} | MD seuls: {len(md_only)} | DB seuls: {len(db_only)}")
print(f"appariements à vérifier (score<0.75): {len(amb)}")

def top(nodes):
    ids={n.id for n in nodes}
    return [n for n in nodes if not (n.parent and n.parent.id in ids)]
print('\n=== PRÉSENTS DANS LE MD, ABSENTS DE LA DB ===')
for n in top(md_only):
    print(f"- {clean_name(n.text)}  [+{len(list(subtree(n)))-1} descendants]")

dbonly=set(db_only)
print('\n=== PRÉSENTS DANS LA DB, ABSENTS DU MD ===')
for i in db_only:
    if parent.get(i) in dbonly: continue
    print(f"- {byid[i]['nom']} (gen {byid[i]['gen']})  [+{len(list(dbsub(i)))-1} descendants]")

print('\n=== APPARIEMENTS DOUTEUX ===')
for n,nm,sc in amb:
    print(f"- {clean_name(n.text)}  ->  {nm}   ({sc:.2f})")

# moved subtrees: md-only with a strong match among db-only
print('\n=== DÉPLACEMENTS POSSIBLES (même personne, autre parent en DB) ===')
for n in top(md_only):
    best=None
    for i in db_only:
        sc=nscore(md_toks(n),db_toks[i])
        if best is None or sc>best[1]: best=(i,sc)
    ta=md_toks(n)
    if best and best[1]>=0.90:
        i=best[0]; tb=db_toks[i]
        if not (ta and tb and sim(ta[0],tb[0])>=FIRST_TH):
            continue
        p=parent.get(i)
        print(f"- MD '{clean_name(n.text)}'  ~  DB '{byid[i]['nom']}'  (score {best[1]:.2f}, parent DB: {byid[p]['nom'] if p else 'racine'})")

print('\n=== RÉPARTITION PAR PROFONDEUR / GÉNÉRATION ===')
from collections import Counter
md_depth={}
def setdepth(n,d=0):
    md_depth[n.id]=d
    for k in n.kids: setdepth(k,d+1)
setdepth(root)
print('MD (profondeur):', sorted(Counter(md_depth.values()).items()))
print('DB (génération):', sorted(Counter(byid[i]['gen'] for i in byid).items()))
c=Counter()
for n in md_all:
    if n.id in mapping: c[(md_depth[n.id], byid[mapping[n.id]]['gen'])]+=1
print('couples (profondeur MD, génération DB):')
for (d,g),k in sorted(c.items()): print(f'  d{d} -> G{g}: {k}')

print('\n=== APPARIEMENTS (score < 0.95) ===')
for n in md_all:
    if n.id in mapping:
        d=byid[mapping[n.id]]
        sc=pair_score(n, mapping[n.id])
        if sc<0.95:
            print(f"- {clean_name(n.text)}  ->  {d['nom']}   ({sc:.2f})")

print('\n=== DOUBLONS DANS LE MD (nom normalisé) ===')
from collections import Counter
def key(n): return ' '.join(md_toks(n))
c=Counter(key(n) for n in md_all)
print('nœuds:',len(md_all),'clés uniques:',len(c))
for k,v in c.items():
    if v>1: print(f'  {v}x {k}')
