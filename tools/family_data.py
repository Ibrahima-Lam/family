import html
import re
import unicodedata
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / "famille-structuree.md"
BRANCH_RE = re.compile(r"^\*{0,2}([A-C])\.")


def inline(s):
    s = html.escape(s, quote=False)
    s = re.sub(r"\[([^\]]+)\]\([^)]+\)", r"\1", s)
    s = re.sub(r"\*\*([^*]+)\*\*", r"<strong>\1</strong>", s)
    s = re.sub(r"(?<!\*)\*[^*]+\*(?!\*)", "", s)
    s = re.sub(r"\s{2,}", " ", s).strip()
    return s


def norm_key(s):
    s = unicodedata.normalize(
        "NFKD", html.unescape(re.sub(r"<[^>]+>", "", s)).lower()
    )
    s = "".join(c for c in s if not unicodedata.combining(c))
    s = re.sub(r"[^a-z0-9]+", " ", s)
    return " ".join(s.split())


def display_text(s):
    if re.match(r"^\*{0,2}[A-C]\.\s", s):
        s = re.sub(r"^\*{0,2}[A-C]\.\s*", "", s)
        s = s.replace("**", "")
    return s


class Node:
    __slots__ = ("text", "children")

    def __init__(self, text):
        self.text = text
        self.children = []


def parse_tree(lines):
    roots = []
    stack = []
    for raw in lines:
        m = re.match(r"^( *)- (.*)$", raw.rstrip("\n"))
        if not m:
            continue
        indent = len(m.group(1))
        node = Node(m.group(2).strip())
        while stack and stack[-1][0] >= indent:
            stack.pop()
        if stack:
            stack[-1][1].children.append(node)
        else:
            roots.append(node)
        stack.append((indent, node))
    return roots


def find(node, needle):
    if needle in node.text:
        return node
    for child in node.children:
        found = find(child, needle)
        if found:
            return found
    return None


def branch_of(text, current):
    m = BRANCH_RE.match(text)
    return m.group(1) if m else current


def load_roots():
    text = SRC.read_text(encoding="utf-8")
    parts = re.split(r"^## ", text, flags=re.M)

    main_body = None
    branch_bodies = []
    for part in parts[1:]:
        lines = part.splitlines()
        title = lines[0].strip()
        body = lines[1:]
        if title.startswith("Lignée"):
            main_body = body
        elif title.startswith("Branche"):
            branch_bodies.append(body)

    main_roots = parse_tree(main_body)
    demba = find(main_roots[0], "Demba Maloum")
    branch_roots = [parse_tree(b)[0] for b in branch_bodies]

    for ref, branch in zip(demba.children, branch_roots):
        m = re.match(r"([A-C])\.", ref.text)
        letter = m.group(1) if m else ""
        name = branch.text.replace("*", "").strip()
        ref.text = f"**{letter}. {name}**"
        ref.children = branch.children

    return main_roots


def walk(nodes, depth=1, branch="main"):
    for n in nodes:
        b = branch_of(n.text, branch)
        yield n, depth, b
        yield from walk(n.children, depth + 1, b)
