from collections import Counter

from family_data import ROOT, branch_of, display_text, inline, load_roots

OUT = ROOT / "famille-livre.html"

BRANCH_LABELS = {
    "main": "Lignée principale",
    "A": "Branche A",
    "B": "Branche B",
    "C": "Branche C",
}


class Person:
    __slots__ = (
        "pid",
        "index",
        "gen",
        "branch",
        "parent",
        "children",
        "siblings",
        "label",
    )

    def __init__(self, pid, index, gen, branch, parent, label):
        self.pid = pid
        self.index = index
        self.gen = gen
        self.branch = branch
        self.parent = parent
        self.children = []
        self.siblings = []
        self.label = label


roots = load_roots()
people = []


def build(node, depth, branch, parent):
    b = branch_of(node.text, branch)
    pid = f"p{len(people) + 1:03d}"
    person = Person(
        pid,
        len(people) + 1,
        depth,
        b,
        parent,
        inline(display_text(node.text)),
    )
    if parent:
        parent.children.append(person)
    people.append(person)
    for child in node.children:
        build(child, depth + 1, b, person)
    return person


for root in roots:
    build(root, 1, "main", None)

for person in people:
    if person.parent:
        person.siblings = [
            child for child in person.parent.children if child is not person
        ]

TOTAL = len(people)
MAX_GEN = max(person.gen for person in people)
GEN_COUNTS = Counter(person.gen for person in people)
BRANCH_COUNTS = Counter(person.branch for person in people)


def branch_class(branch):
    return "branch-main" if branch == "main" else f"branch-{branch.lower()}"


def ref(person):
    return (
        f'<a class="ref" href="#{person.pid}">{person.label}'
        f'<span class="g">G{person.gen}</span></a>'
    )


def relation(title, items, empty):
    if items:
        links = "".join(f"<li>{ref(person)}</li>" for person in items)
        body = f'<ul class="links">{links}</ul>'
    else:
        body = f'<p class="none">{empty}</p>'
    return (
        f'<section class="rel"><h3>{title} '
        f'<span class="rel-count">{len(items)}</span></h3>{body}</section>'
    )


def entry(person):
    child_count = len(person.children)
    if child_count == 0:
        child_text = "Aucun enfant"
    elif child_count == 1:
        child_text = "1 enfant"
    else:
        child_text = f"{child_count} enfants"
    if person.parent is None:
        child_text = f"Racine · {child_text}"

    return "\n".join(
        [
            f'<article class="entry {branch_class(person.branch)}" id="{person.pid}">',
            '<header class="entry-head">',
            '<div class="entry-kicker">',
            f'<span class="entry-no">N° {person.index:03d}</span>',
            f'<span class="gen-badge">G{person.gen}</span>',
            f'<span class="branch-label">'
            f'{BRANCH_LABELS.get(person.branch, "Branche")}</span>',
            "</div>",
            f"<h2>{person.label}</h2>",
            f'<p class="entry-sub">{child_text}</p>',
            '<a class="to-toc" href="#sommaire">↑ Sommaire</a>',
            "</header>",
            '<div class="relations">',
            relation(
                "Parent",
                [person.parent] if person.parent else [],
                "Aucun parent référencé",
            ),
            relation("Frères et sœurs", person.siblings, "Aucun frère ou sœur"),
            relation("Enfants", person.children, "Aucun enfant"),
            "</div>",
            "</article>",
        ]
    )


def toc_group(gen):
    group = [person for person in people if person.gen == gen]
    count = len(group)
    word = "personne" if count == 1 else "personnes"
    links = "".join(
        f'<li><a href="#{person.pid}">{person.label}</a></li>' for person in group
    )
    return (
        f'<section class="toc-group">'
        f'<h3><span class="gen-badge">G{gen}</span> {count} {word}</h3>'
        f'<ul class="toc-list">{links}</ul></section>'
    )


CSS = """
:root {
  --paper: #f3ead8;
  --page: #fffdf7;
  --ink: #2b2118;
  --muted: #7a6a58;
  --line: #d8c9ae;
  --accent: #0f5f56;
  --accent-soft: #e7f1ee;
  --gold: #b08d4f;
  --topbar: 56px;
  --shadow: 0 18px 40px rgba(59, 42, 20, .12);
  --ui: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
  --serif: "Iowan Old Style", "Palatino Linotype", Palatino, Georgia,
    "Times New Roman", serif;
}
* { box-sizing: border-box; }
html { scroll-behavior: smooth; }
body {
  margin: 0;
  background-color: var(--paper);
  background-image: radial-gradient(rgba(90, 65, 30, .06) 1px, transparent 1px);
  background-size: 4px 4px;
  color: var(--ink);
  font-family: var(--serif);
  line-height: 1.6;
}
a { color: var(--accent); text-decoration: none; }
a:hover { text-decoration: underline; text-underline-offset: .15em; }
a:focus-visible {
  outline: 2px solid var(--gold);
  outline-offset: 3px;
  border-radius: 4px;
}
[id] { scroll-margin-top: calc(var(--topbar) + 1rem); }

.topbar {
  position: sticky;
  top: 0;
  z-index: 20;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  height: var(--topbar);
  padding: 0 1.25rem;
  background: rgba(243, 234, 216, .93);
  border-bottom: 1px solid var(--line);
  backdrop-filter: blur(10px);
  font-family: var(--ui);
  font-size: .9rem;
}
.brand { font-weight: 700; color: var(--ink); letter-spacing: .02em; }
.topnav { display: flex; gap: 1rem; }
.topnav a { color: var(--muted); }
.topnav a:hover { color: var(--accent); }

main {
  max-width: 960px;
  margin: 0 auto;
  padding: 1.5rem 1.25rem 4rem;
}

.cover {
  position: relative;
  overflow: hidden;
  margin: 1rem 0 2rem;
  padding: clamp(2.5rem, 6vw, 4.5rem) clamp(1.5rem, 4vw, 3.5rem);
  background: linear-gradient(145deg, #0d3f3a, #125b51);
  color: #f7f0e0;
  border-radius: 4px;
  box-shadow: var(--shadow);
  text-align: center;
}
.cover::before,
.cover::after {
  content: "";
  position: absolute;
  border: 1px solid rgba(240, 226, 195, .35);
  pointer-events: none;
}
.cover::before { inset: 14px; border-radius: 2px; }
.cover::after { inset: 20px; border-color: rgba(240, 226, 195, .18); }
.cover .kicker { color: #d8c7a4; }
.cover h1 {
  margin: .35rem 0 .5rem;
  font-size: clamp(2.2rem, 7vw, 4.6rem);
  line-height: 1.05;
  font-weight: 600;
  letter-spacing: .01em;
}
.subtitle {
  max-width: 36rem;
  margin: 0 auto 1.2rem;
  color: #e9dfc9;
  font-size: clamp(1rem, 2.4vw, 1.2rem);
}
.cover-meta {
  margin: 0 0 1.6rem;
  color: #cbbd9f;
  font-family: var(--ui);
  font-size: .9rem;
  letter-spacing: .04em;
  text-transform: uppercase;
}
.cover-actions {
  display: flex;
  justify-content: center;
  gap: .75rem;
  flex-wrap: wrap;
}
.button {
  display: inline-block;
  padding: .65rem 1.15rem;
  border: 1px solid #d8c7a4;
  border-radius: 999px;
  background: #f7f0e0;
  color: #0d3f3a;
  font-family: var(--ui);
  font-size: .9rem;
  font-weight: 600;
}
.button:hover { background: #fffaf0; text-decoration: none; }
.button.ghost {
  background: transparent;
  color: #f7f0e0;
  border-color: rgba(247, 240, 224, .5);
}
.button.ghost:hover { background: rgba(247, 240, 224, .1); }

.kicker {
  margin: 0;
  color: var(--muted);
  font-family: var(--ui);
  font-size: .75rem;
  font-weight: 700;
  letter-spacing: .14em;
  text-transform: uppercase;
}

.page {
  background: var(--page);
  border: 1px solid var(--line);
  border-radius: 4px;
  box-shadow: var(--shadow);
  padding: clamp(1.25rem, 3vw, 2.25rem);
  margin-bottom: 2rem;
}
.page-head { margin-bottom: 1.5rem; }
.page h2 {
  margin: .2rem 0 .5rem;
  font-size: clamp(1.6rem, 4vw, 2.2rem);
  font-weight: 600;
}
.page-intro { margin: 0; color: var(--muted); }

.toc-group { margin-top: 1.4rem; }
.toc-group + .toc-group {
  border-top: 1px solid var(--line);
  padding-top: 1rem;
}
.toc-group h3 {
  display: flex;
  align-items: center;
  gap: .5rem;
  margin: 0 0 .5rem;
  font-size: 1rem;
  font-weight: 600;
}
.toc-list {
  margin: 0;
  padding: 0;
  list-style: none;
  columns: 1;
  column-gap: 1.5rem;
}
.toc-list li { break-inside: avoid; margin: .18rem 0; }
.toc-list a { color: var(--ink); }
.toc-list a:hover { color: var(--accent); }
@media (min-width: 640px) { .toc-list { columns: 2; } }
@media (min-width: 900px) { .toc-list { columns: 3; } }

.gen-badge {
  display: inline-block;
  min-width: 2.2rem;
  padding: .05rem .4rem;
  border: 1px solid var(--line);
  border-radius: 999px;
  background: var(--accent-soft);
  color: var(--accent);
  font-family: var(--ui);
  font-size: .7rem;
  font-weight: 700;
  letter-spacing: .04em;
  text-align: center;
  line-height: 1.6;
}

.entry {
  --branch: #0f5f56;
  position: relative;
  margin: 0 0 1.5rem;
  padding: clamp(1.25rem, 3vw, 2rem) clamp(1.25rem, 3vw, 2.25rem);
  background: var(--page);
  border: 1px solid var(--line);
  border-left: 4px solid var(--branch);
  border-radius: 4px;
  box-shadow: var(--shadow);
  scroll-margin-top: calc(var(--topbar) + 1rem);
}
.entry.branch-a { --branch: #1d4ed8; }
.entry.branch-b { --branch: #b45309; }
.entry.branch-c { --branch: #7e22ce; }
.entry-head {
  position: relative;
  padding-bottom: .75rem;
  border-bottom: 1px solid var(--line);
}
.entry-kicker {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: .45rem;
  margin-bottom: .35rem;
  font-family: var(--ui);
}
.entry-no {
  color: var(--muted);
  font-size: .75rem;
  letter-spacing: .08em;
  text-transform: uppercase;
}
.branch-label {
  color: var(--branch);
  font-size: .75rem;
  font-weight: 700;
  letter-spacing: .04em;
  text-transform: uppercase;
}
.entry h2 {
  margin: .15rem 0 .2rem;
  font-size: clamp(1.45rem, 3.5vw, 2rem);
  font-weight: 600;
  line-height: 1.2;
}
.entry h2 strong { font-weight: 600; }
.entry-sub { margin: 0; color: var(--muted); font-size: .9rem; }
.to-toc {
  position: absolute;
  top: 0;
  right: 0;
  font-family: var(--ui);
  font-size: .78rem;
  color: var(--muted);
}
.to-toc:hover { color: var(--accent); }

.relations {
  display: grid;
  gap: .8rem;
  margin-top: 1rem;
}
@media (min-width: 720px) {
  .relations { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.25rem; }
}
.rel h3 {
  display: flex;
  align-items: baseline;
  gap: .4rem;
  margin: 0 0 .35rem;
  color: var(--muted);
  font-family: var(--ui);
  font-size: .75rem;
  font-weight: 700;
  letter-spacing: .1em;
  text-transform: uppercase;
}
.rel-count { color: var(--branch); font-variant-numeric: tabular-nums; }
.links {
  display: flex;
  flex-wrap: wrap;
  gap: .25rem .75rem;
  margin: 0;
  padding: 0;
  list-style: none;
}
.links li { margin: 0; }
.ref {
  display: inline-flex;
  align-items: baseline;
  gap: .35rem;
  color: var(--ink);
}
.ref:hover { color: var(--accent); }
.ref .g {
  display: inline-block;
  padding: 0 .3rem;
  border: 1px solid var(--line);
  border-radius: 999px;
  background: #fbf6ea;
  color: var(--muted);
  font-family: var(--ui);
  font-size: .65rem;
  line-height: 1.5;
}
.none { margin: 0; color: var(--muted); font-style: italic; }

.entry.is-target { animation: flash 1.6s ease; }
@keyframes flash {
  0%, 100% { box-shadow: var(--shadow); }
  15% { box-shadow: 0 0 0 4px rgba(176, 141, 79, .45), var(--shadow); }
}

.colophon {
  margin-top: 2rem;
  padding-top: 1.25rem;
  border-top: 1px solid var(--line);
  color: var(--muted);
  text-align: center;
  font-size: .9rem;
}
.colophon p { margin: .3rem 0; }
.colophon code {
  background: rgba(255, 255, 255, .5);
  border: 1px solid var(--line);
  border-radius: 4px;
  padding: .05rem .3rem;
  font-size: .85em;
}

@media (max-width: 600px) {
  main { padding: 1rem .85rem 3rem; }
  .cover { margin-top: .5rem; }
  .to-toc { position: static; display: inline-block; margin-top: .4rem; }
  .entry-head { padding-right: 0; }
}

@media (prefers-reduced-motion: reduce) {
  html { scroll-behavior: auto; }
  .entry.is-target { animation: none; }
}

@media print {
  body { background: #fff; }
  .topbar, .cover-actions, .to-toc, .colophon a { display: none; }
  .cover {
    background: #0d3f3a;
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
  }
  .page, .entry { box-shadow: none; break-inside: avoid; }
}
"""

JS = """
(function () {
  function flash(target) {
    if (!target) return;
    target.classList.remove('is-target');
    void target.offsetWidth;
    target.classList.add('is-target');
    setTimeout(function () { target.classList.remove('is-target'); }, 1600);
  }

  document.addEventListener('click', function (event) {
    var link = event.target.closest ? event.target.closest('a[href^="#"]') : null;
    if (!link) return;
    var id = link.getAttribute('href');
    if (!id || id === '#') return;
    var target = document.querySelector(id);
    if (target) flash(target);
  });

  if (location.hash) {
    var initial = document.querySelector(location.hash);
    if (initial) setTimeout(function () { flash(initial); }, 150);
  }
})();
"""

TOPBAR = """
<header class="topbar">
  <a class="brand" href="#cover">Famille Téguela</a>
  <nav class="topnav" aria-label="Navigation">
    <a href="#sommaire">Sommaire</a>
    <a href="famille-arbre.html">Arbre</a>
  </nav>
</header>
"""

COVER = f"""
<section class="cover" id="cover">
  <p class="kicker">Généalogie familiale</p>
  <h1>La famille Téguela</h1>
  <p class="subtitle">Livre des personnes, de leurs parents, de leurs frères et
  sœurs et de leurs enfants</p>
  <p class="cover-meta">{TOTAL} personnes · {MAX_GEN} générations ·
  {len(BRANCH_COUNTS)} branches</p>
  <div class="cover-actions">
    <a class="button" href="#sommaire">Ouvrir le sommaire</a>
    <a class="button ghost" href="famille-arbre.html">Voir l’arbre</a>
  </div>
</section>
"""

TOC = f"""
<section id="sommaire" class="page toc">
  <header class="page-head">
    <p class="kicker">Sommaire</p>
    <h2>Les personnes du livre</h2>
    <p class="page-intro">Chaque nom ouvre la page de la personne. Les liens de
    parenté sont également cliquables.</p>
  </header>
  {''.join(toc_group(gen) for gen in range(1, MAX_GEN + 1))}
</section>
"""

COLOPHON = f"""
<footer class="colophon">
  <p>Livre composé à partir de <code>famille-structuree.md</code>.</p>
  <p>{TOTAL} personnes · {MAX_GEN} générations · {len(BRANCH_COUNTS)} branches.</p>
  <p><a href="#sommaire">Retour au sommaire</a></p>
</footer>
"""

out = []
out.append("<!DOCTYPE html>")
out.append('<html lang="fr">')
out.append("<head>")
out.append('<meta charset="UTF-8">')
out.append('<meta name="viewport" content="width=device-width, initial-scale=1">')
out.append("<title>Livre de la famille Téguela</title>")
out.append("<style>" + CSS + "</style>")
out.append("</head>")
out.append("<body>")
out.append(TOPBAR)
out.append("<main>")
out.append(COVER)
out.append(TOC)
out.append('<section class="entries" aria-label="Personnes">')
out.extend(entry(person) for person in people)
out.append("</section>")
out.append(COLOPHON)
out.append("</main>")
out.append("<script>" + JS + "</script>")
out.append("</body>")
out.append("</html>")

OUT.write_text("\n".join(out) + "\n", encoding="utf-8")
print("wrote", OUT)
