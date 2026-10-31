import html
from collections import Counter

from family_data import ROOT, branch_of, inline, load_roots, norm_key, walk

OUT = ROOT / "famille-arbre.html"


def render(nodes, depth=0, branch="main"):
    out = []
    for n in nodes:
        label = inline(n.text)
        count = len(n.children)
        gen = depth + 1
        b = branch_of(n.text, branch)
        attrs = (
            f'data-gen="{gen}" data-branch="{b}" '
            f'data-name="{html.escape(norm_key(label), quote=True)}" '
            f'data-kids="{1 if n.children else 0}"'
        )
        head = (
            f'<span class="gen">G{gen}</span> {label} '
            f'<span class="kids">({count})</span>'
        )
        if n.children:
            op = " open" if depth == 0 else ""
            out.append(f"<dt {attrs}><details{op}><summary>{head}</summary>")
            out.append('<dl class="tree">')
            out.append(render(n.children, depth + 1, b))
            out.append("</dl></details></dt>")
        else:
            out.append(f'<dt class="leaf" {attrs}>{head}</dt>')
    return "\n".join(out)


roots = load_roots()

stats = list(walk(roots))
MAX_GEN = max(d for _, d, _ in stats)
TOTAL = len(stats)
GEN_COUNTS = Counter(d for _, d, _ in stats)
BRANCH_COUNTS = Counter(b for _, _, b in stats)
PARENTS = sum(1 for n, _, _ in stats if n.children)
LEAVES = TOTAL - PARENTS
EDGES = sum(len(n.children) for n, _, _ in stats)
AVG_CHILDREN = EDGES / PARENTS if PARENTS else 0

CSS = """
:root {
  --bg: #f5f7f9;
  --card: #ffffff;
  --ink: #1f2933;
  --muted: #8a94a3;
  --accent: #0f766e;
  --accent-soft: #e6f2f0;
  --line: #d5dde5;
}
* { box-sizing: border-box; }
body {
  margin: 0;
  background: var(--bg);
  color: var(--ink);
  font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
  line-height: 1.5;
}
header {
  background: linear-gradient(135deg, #0f766e, #115e59);
  color: #fff;
  padding: 1.75rem 1.5rem;
}
header h1 { margin: 0; font-size: clamp(1.3rem, 3vw, 1.8rem); }
main { max-width: 1100px; margin: 0 auto; padding: 1.5rem; }
.toolbar {
  display: flex;
  align-items: center;
  gap: .5rem;
  flex-wrap: wrap;
  margin-bottom: 1.25rem;
}
button {
  font: inherit; cursor: pointer;
  border: 1px solid var(--line); background: var(--card); color: var(--ink);
  padding: .45rem .8rem; border-radius: 8px;
}
button:hover { background: var(--accent-soft); border-color: var(--accent); }
input[type="search"], select {
  font: inherit;
  color: var(--ink);
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: 8px;
  padding: .45rem .65rem;
}
input[type="search"] { flex: 1 1 240px; min-width: 200px; }
select { cursor: pointer; }
.check {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  color: var(--muted);
  font-size: .9rem;
  white-space: nowrap;
}
.check input { accent-color: var(--accent); }
.result-count { margin-left: auto; color: var(--muted); font-size: .9rem; }
#empty { color: var(--muted); padding: .5rem .35rem; }
.tree-scroll {
  overflow-x: auto;
  overscroll-behavior-x: contain;
  -webkit-overflow-scrolling: touch;
  padding-bottom: .5rem;
}
section {
  background: var(--card);
  border-radius: 12px;
  padding: 1rem 1.25rem 1.25rem;
  box-shadow: 0 1px 3px rgba(16,24,40,.08);
}
dl.tree {
  margin: .25rem 0 .25rem 1.05rem;
  padding-left: 1.1rem;
  border-left: 1px solid var(--line);
}
dl.tree > dt { position: relative; margin: .2rem 0; }
dl.tree > dt::before {
  content: "";
  position: absolute;
  left: -1.1rem;
  top: .95rem;
  width: .85rem;
  border-top: 1px solid var(--line);
}
dt.leaf { padding: .15rem .35rem .15rem 1.5rem; border-radius: 6px; }
dt.leaf::after {
  content: "";
  position: absolute;
  left: .95rem;
  top: .8rem;
  width: .4rem;
  height: .4rem;
  border-radius: 50%;
  background: var(--line);
}
summary {
  cursor: pointer;
  padding: .2rem .35rem;
  border-radius: 6px;
  font-weight: 600;
}
summary:hover { background: var(--accent-soft); }
summary:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
.gen {
  display: inline-block;
  min-width: 2.3rem;
  padding: .02rem .35rem;
  margin-right: .15rem;
  border-radius: 999px;
  background: var(--accent-soft);
  color: var(--accent);
  font-size: .75rem;
  font-weight: 700;
  text-align: center;
  letter-spacing: .02em;
}
.kids {
  color: var(--muted);
  font-variant-numeric: tabular-nums;
  font-weight: 500;
  white-space: nowrap;
}
#arbre dt.match > details > summary,
#arbre dt.leaf.match {
  background: #fff3b0;
  box-shadow: inset 0 0 0 1px #e8c95a;
}
section + section { margin-top: 1rem; }
.panel h2 { margin: .1rem 0 .5rem; font-size: 1.15rem; }
.panel h3 {
  margin: 1rem 0 .4rem;
  font-size: .78rem;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: .05em;
}
.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
  gap: .7rem;
  margin: .75rem 0 .5rem;
}
.stat {
  background: var(--bg);
  border: 1px solid var(--line);
  border-radius: 10px;
  padding: .7rem .5rem;
  text-align: center;
}
.stat-value {
  display: block;
  font-size: 1.45rem;
  font-weight: 700;
  color: var(--accent);
  line-height: 1.2;
}
.stat-label { color: var(--muted); font-size: .82rem; }
.stats-columns {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.1rem;
}
.table-wrap { overflow-x: auto; }
.stats-table {
  width: 100%;
  border-collapse: collapse;
  font-size: .9rem;
}
.stats-table th, .stats-table td {
  padding: .35rem .45rem;
  border-bottom: 1px solid var(--line);
  text-align: left;
  vertical-align: middle;
}
.stats-table th {
  color: var(--muted);
  font-weight: 600;
  font-size: .78rem;
  text-transform: uppercase;
  letter-spacing: .03em;
}
.stats-table .num {
  text-align: right;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}
.stats-table tr.total td { font-weight: 700; border-bottom: 0; }
.bar-track {
  display: block;
  background: var(--accent-soft);
  border-radius: 999px;
  height: .55rem;
  min-width: 100px;
  overflow: hidden;
}
.bar-fill {
  display: block;
  background: var(--accent);
  height: 100%;
  border-radius: 999px;
}
.help { margin: .5rem 0; padding-left: 1.2rem; }
.help li { margin: .3rem 0; }
.help code, .source code {
  background: var(--bg);
  border: 1px solid var(--line);
  border-radius: 5px;
  padding: .05rem .3rem;
  font-size: .85em;
}
.source { color: var(--muted); font-size: .85rem; margin: .75rem 0 0; }
strong { color: #0b4f4a; }
@media (max-width: 600px) {
  main { padding: 1rem; }
  section { padding: .85rem; }
  .tree-scroll { margin: 0 -.15rem; padding: 0 .15rem .5rem; }
  #arbre > .tree-scroll > dl.tree { min-width: calc(100% + 360px); }
  dl.tree { margin-left: .55rem; padding-left: .8rem; }
}
"""

JS = """
function setAll(open) {
  document.querySelectorAll('#arbre details').forEach(function (d) {
    if (open) { d.setAttribute('open', ''); }
    else { d.removeAttribute('open'); }
  });
}

(function () {
  var search = document.getElementById('search');
  var genFilter = document.getElementById('genFilter');
  var branchFilter = document.getElementById('branchFilter');
  var withDesc = document.getElementById('withDesc');
  var countEl = document.getElementById('resultCount');
  var emptyEl = document.getElementById('empty');
  var reset = document.getElementById('reset');
  var rootDl = document.querySelector('#arbre .tree-scroll > dl.tree');
  if (!rootDl) return;
  var all = Array.prototype.slice.call(document.querySelectorAll('#arbre dt'));

  function norm(s) {
    return (s || '')
      .normalize('NFD')
      .replace(/[\\u0300-\\u036f]/g, '')
      .toLowerCase()
      .trim();
  }

  function childrenOf(dt) {
    var dl = dt.querySelector(':scope > details > dl.tree');
    if (!dl) return [];
    return Array.prototype.slice.call(dl.querySelectorAll(':scope > dt'));
  }

  function ancestorsOf(dt) {
    var out = [];
    var p = dt.parentElement;
    while (p) {
      if (p.tagName === 'DT') out.push(p);
      p = p.parentElement;
    }
    return out;
  }

  function resetAll() {
    all.forEach(function (dt) {
      dt.classList.remove('match', 'path');
      dt.style.display = '';
    });
    document.querySelectorAll('#arbre details').forEach(function (d) {
      d.removeAttribute('open');
    });
    var first = document.querySelector('#arbre .tree-scroll > dl.tree > dt > details');
    if (first) first.setAttribute('open', '');
    countEl.textContent = '';
    emptyEl.hidden = true;
  }

  function apply() {
    var q = norm(search.value);
    var gen = genFilter.value;
    var branch = branchFilter.value;
    var active = q || gen || branch;
    if (!active) {
      resetAll();
      return;
    }

    all.forEach(function (dt) {
      dt.classList.remove('match', 'path');
      dt.style.display = 'none';
    });

    var matched = new Set();
    var hits = 0;

    all.forEach(function (dt) {
      var name = dt.getAttribute('data-name') || '';
      var ok = true;
      if (q && name.indexOf(q) === -1) ok = false;
      if (gen && dt.getAttribute('data-gen') !== gen) ok = false;
      if (branch && dt.getAttribute('data-branch') !== branch) ok = false;
      if (!ok) return;

      hits++;
      dt.classList.add('match');
      matched.add(dt);
      ancestorsOf(dt).forEach(function (a) {
        matched.add(a);
        a.classList.add('path');
      });
      if (q && withDesc.checked && !gen && !branch) {
        dt.querySelectorAll(':scope details dt').forEach(function (x) {
          matched.add(x);
        });
      }
    });

    function visible(dt) {
      var kids = childrenOf(dt);
      var kidVisible = false;
      for (var i = 0; i < kids.length; i++) {
        if (visible(kids[i])) kidVisible = true;
      }
      var v = matched.has(dt) || kidVisible;
      dt.style.display = v ? '' : 'none';
      var d = dt.querySelector(':scope > details');
      if (d && v && kidVisible) {
        d.open = true;
      }
      return v;
    }

    Array.prototype.slice.call(rootDl.children).forEach(function (dt) {
      if (dt.tagName === 'DT') visible(dt);
    });

    countEl.textContent = hits + (hits > 1 ? ' résultats' : ' résultat');
    emptyEl.hidden = hits !== 0;
  }

  search.addEventListener('input', apply);
  genFilter.addEventListener('change', apply);
  branchFilter.addEventListener('change', apply);
  withDesc.addEventListener('change', apply);
  reset.addEventListener('click', function () {
    search.value = '';
    genFilter.value = '';
    branchFilter.value = '';
    withDesc.checked = true;
    resetAll();
    search.focus();
  });

  apply();
})();
"""

out = []
out.append("<!DOCTYPE html>")
out.append('<html lang="fr">')
out.append("<head>")
out.append('<meta charset="UTF-8">')
out.append('<meta name="viewport" content="width=device-width, initial-scale=1">')
out.append("<title>Arbre généalogique — Famille Téguela</title>")
out.append("<style>" + CSS + "</style>")
out.append("</head>")
out.append("<body>")
out.append("<header>")
out.append("<h1>Généalogie de la famille Téguela</h1>")
out.append("</header>")
out.append("<main>")
out.append('<div class="toolbar">')
out.append('<input id="search" type="search" placeholder="Rechercher un nom…" aria-label="Rechercher un nom">')
out.append('<select id="genFilter" aria-label="Filtrer par génération">')
out.append('<option value="">Toutes les générations</option>')
for g in range(1, MAX_GEN + 1):
    out.append(f'<option value="{g}">G{g}</option>')
out.append("</select>")
out.append('<select id="branchFilter" aria-label="Filtrer par branche">')
out.append('<option value="">Toutes les branches</option>')
out.append('<option value="main">Lignée principale</option>')
out.append('<option value="A">Branche A</option>')
out.append('<option value="B">Branche B</option>')
out.append('<option value="C">Branche C</option>')
out.append("</select>")
out.append('<label class="check"><input type="checkbox" id="withDesc" checked> Inclure les descendants</label>')
out.append('<button type="button" id="reset">Réinitialiser</button>')
out.append('<span id="resultCount" class="result-count" aria-live="polite"></span>')
out.append("</div>")
out.append('<div class="toolbar">')
out.append('<button type="button" onclick="setAll(true)">Tout ouvrir</button>')
out.append('<button type="button" onclick="setAll(false)">Tout fermer</button>')
out.append("</div>")
out.append('<section id="arbre">')
out.append('<div class="tree-scroll">')
out.append('<dl class="tree">')
out.append(render(roots))
out.append("</dl>")
out.append("</div>")
out.append('<p id="empty" hidden>Aucun résultat.</p>')
out.append("</section>")
out.append('<section id="stats" class="panel">')
out.append("<h2>Statistiques</h2>")
out.append('<div class="stat-grid">')
for value, label in [
    (TOTAL, "personnes"),
    (MAX_GEN, "générations"),
    (PARENTS, "avec enfants"),
    (LEAVES, "sans enfant"),
    (f"{AVG_CHILDREN:.1f}".replace(".", ","), "enfants / parent"),
]:
    out.append(
        f'<div class="stat"><span class="stat-value">{value}</span>'
        f'<span class="stat-label">{label}</span></div>'
    )
out.append("</div>")
out.append('<div class="stats-columns">')
out.append('<div><h3>Par génération</h3><div class="table-wrap">')
out.append('<table class="stats-table">')
out.append(
    '<thead><tr><th>Génération</th><th class="num">Personnes</th>'
    "<th>Répartition</th></tr></thead><tbody>"
)
for g in range(1, MAX_GEN + 1):
    n = GEN_COUNTS.get(g, 0)
    pct = (n / TOTAL * 100) if TOTAL else 0
    out.append(
        f'<tr><td>G{g}</td><td class="num">{n}</td>'
        f'<td><span class="bar-track"><span class="bar-fill" '
        f'style="width:{pct:.1f}%"></span></span></td></tr>'
    )
out.append(
    f'<tr class="total"><td>Total</td><td class="num">{TOTAL}</td>'
    "<td></td></tr>"
)
out.append("</tbody></table></div></div>")
out.append('<div><h3>Par branche</h3><div class="table-wrap">')
out.append('<table class="stats-table">')
out.append(
    '<thead><tr><th>Branche</th><th class="num">Personnes</th>'
    "<th>Répartition</th></tr></thead><tbody>"
)
for key, label in [
    ("main", "Lignée principale"),
    ("A", "Branche A"),
    ("B", "Branche B"),
    ("C", "Branche C"),
]:
    n = BRANCH_COUNTS.get(key, 0)
    pct = (n / TOTAL * 100) if TOTAL else 0
    out.append(
        f'<tr><td>{label}</td><td class="num">{n}</td>'
        f'<td><span class="bar-track"><span class="bar-fill" '
        f'style="width:{pct:.1f}%"></span></span></td></tr>'
    )
out.append(
    f'<tr class="total"><td>Total</td><td class="num">{TOTAL}</td>'
    "<td></td></tr>"
)
out.append("</tbody></table></div></div>")
out.append("</div>")
out.append("</section>")
out.append('<section id="aide" class="panel">')
out.append("<h2>Fonctionnalités</h2>")
out.append('<ul class="help">')
out.append(
    "<li><strong>Recherche</strong> : filtre les noms sans tenir compte des "
    "accents ni de la casse. Les ancêtres des personnes trouvées restent "
    "visibles pour garder le contexte.</li>"
)
out.append(
    f"<li><strong>Génération</strong> : limite l’affichage à une génération "
    f"(G1 = racine, G{MAX_GEN} = la plus récente) tout en conservant les "
    "ancêtres nécessaires.</li>"
)
out.append(
    "<li><strong>Branche</strong> : isole la lignée principale ou l’une des "
    "branches A, B, C.</li>"
)
out.append(
    "<li><strong>Inclure les descendants</strong> : avec une recherche, "
    "affiche aussi toute la descendance des personnes trouvées. L’option est "
    "ignorée si un filtre de génération ou de branche est actif.</li>"
)
out.append(
    "<li><strong>Tout ouvrir / Tout fermer</strong> : déploie ou replie "
    "l’arbre entier.</li>"
)
out.append(
    "<li><strong>Mobile</strong> : l’arbre défile horizontalement pour "
    "conserver la hiérarchie des générations.</li>"
)
out.append(
    "<li><strong>Réinitialiser</strong> : efface la recherche et les filtres "
    "et revient à l’affichage initial.</li>"
)
out.append(
    '<li>Les badges <span class="gen">G5</span> indiquent la génération ; le '
    "nombre entre parenthèses indique le nombre d’enfants.</li>"
)
out.append("</ul>")
out.append(
    '<p class="source">Source : <code>famille-structuree.md</code>, enrichi '
    "à partir de la base <code>famille.db</code>.</p>"
)
out.append("</section>")
out.append("</main>")
out.append("<script>" + JS + "</script>")
out.append("</body>")
out.append("</html>")

open(OUT, "w", encoding="utf-8").write("\n".join(out) + "\n")
print("wrote", OUT)
