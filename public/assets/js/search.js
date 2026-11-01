

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;')
}

function personLabel(name, surname) {
    return `${escapeHtml(name ?? '')}${surname ? ` <span class="person-surname">${escapeHtml(surname)}</span>` : ''}`
}

function escapeRegExp(value) {
    return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
}

const loadData = async function (id = null) {
    const url = id ? `?p=api/personne/${id}` : '?p=api/personnes'
    const res = await fetch(url);
    const data = await res.json();
    return data;
}

const HtmlTable = {
    thead: `<thead><tr><th>id</th><th>nom</th><th>generation</th><th>sexe</th><th>parent</th></tr></thead>`,
    table: (data) => `<table id="table" class="table striped ">${HtmlTable.thead} ${data}</table>`,
    tbody: (data) => `<tbody id="contains" class="body">${data.map(value => `<tr><td>${escapeHtml(value.idPersonne)}</td><td><a href="?p=personne/details/${encodeURIComponent(value.idPersonne)}">${personLabel(value.nomPersonne, value.surnom)}</a></td><td>${escapeHtml(value.nomGeneration)}</td><td>${escapeHtml(value.sexe)}</td><td><a href="?p=personne/details/${encodeURIComponent(value.idParent ?? '')}">${personLabel(value.parent, value.parentSurnom)}</a></td></tr>`).join('')}</tbody>`,

}

/* -------------------------------------variables----------------------------------------------------------- */
const srch = document.getElementById('srch');
const searchContainer = document.getElementById('search-container');
let data = [];
/* -------------------------------------fonctions----------------------------------------------------------- */
async function chargement() {
    data = await loadData();
}
/* -------------------------------------initialisation------------------------------------------------------ */
if (srch && searchContainer) {
    chargement();
}

/* -------------------------------------events----------------------------------------------------------- */

srch?.addEventListener('input', async function (e) {
    if (!searchContainer) return;
    if (data.length === 0) await chargement();
    searchContainer.innerHTML = '';
    const value = e.target.value.trim();
    const results = value === ''
        ? []
        : data.filter(person => `${person.nomPersonne ?? ''} ${person.surnom ?? ''}`.toLowerCase().includes(value.toLowerCase()));
    searchContainer.innerHTML = HtmlTable.table(HtmlTable.tbody(results));
    searchContainer.classList.add('search-container');
    searchContainer.style.display = results.length > 0 ? 'block' : 'none';
    Matcher();
})

document.addEventListener('click', function (e) {
    if (!searchContainer) return;
    if (e.target !== srch && !searchContainer.contains(e.target)) {
        searchContainer.style.display = 'none';
    }
})

function Matcher() {

    const val = srch.value
    const body = document.querySelector('.body')
    if (!body || val === '') return
    let reg
    try {
        reg = new RegExp(escapeRegExp(val), "i")
    } catch (e) {
        return
    }
    const td = body.querySelectorAll("td")
    for (let i = 0; i < td.length; i++) {
        let element = td[i];
        let text = element.innerText
        let a = element.querySelector("a");
        if (text.search(reg) != -1 && !element.contains(a)) {
            let txt = text.replace(reg, "<strong><span class='matched'>" + escapeHtml(val) + "</span></strong>");
            let tag = document.createElement("div");
            tag.innerHTML = txt;
            element.replaceChildren(tag);

        } else if (text.search(reg) != -1 && element.contains(a)) {
            text = a.innerHTML

            let txt = text.replace(reg, "<strong><span class='matched'>" + escapeHtml(val) + "</span></strong>");
            a.innerHTML = txt;
        }
    }
}