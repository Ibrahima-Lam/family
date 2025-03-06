

const loadData = async function (id = null) {
    const url = id ? `?p=api/personne/${id}` : '?p=api/personnes'
    const res = await fetch(url);
    const data = await res.json();
    return data;
}

const HtmlTable = {
    thead: `<thead><tr><th>id</th><th>nom</th><th>generation</th><th>sexe</th><th>parent</th></tr></thead>`,
    table: (data) => `<table id="table" class="table striped ">${HtmlTable.thead} ${data}</table>`,
    tbody: (data) => `<tbody id="contains" class="body">${data.map(value => `<tr><td>${value.idPersonne}</td><td><a href="?p=personne/details/${value.idPersonne}">${value.nomPersonne}</a></td><td>${value.nomGeneration}</td><td>${value.sexe}</td><td><a href="?p=personne/details/${value.idParent}">${value.parent}</a></td></tr>`).join('')}</tbody>`,

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
chargement();

/* -------------------------------------events----------------------------------------------------------- */

srch?.addEventListener('input', async function (e) {
    if (data.lenght == 0) await charge();
    searchContainer.innerHTML = '';
    const value = e.target.value;
    const results = data.filter(person => person.nomPersonne.toLowerCase().includes(value.toLowerCase()));
    searchContainer.innerHTML = HtmlTable.table(HtmlTable.tbody(results));
    searchContainer.classList.add('search-container');
    searchContainer.style.display = 'block';
    if (results.length == 0 || value.length == 0) searchContainer.style.display = 'none';
    Matcher();
})

document.addEventListener('click', function (e) {

    searchContainer.style.display = 'none';

})

function Matcher() {

    const val = srch.value
    const body = document.querySelector('.body')
    const td = body.querySelectorAll("td")
    for (let i = 0; i < td.length; i++) {
        let element = td[i];
        let text = element.innerText
        let a = element.querySelector("a");
        const reg = new RegExp(val, "i")
        if (text.search(reg) != -1 && !element.contains(a)) {
            let txt = text.replace(reg, "<strong><span class='matched'>" + val + "</span></strong>");
            let tag = document.createElement("div");
            tag.innerHTML = txt;
            element.replaceChildren(tag);

        } else if (text.search(reg) != -1 && element.contains(a)) {
            text = a.innerHTML

            let txt = text.replace(reg, "<strong><span class='matched'>" + val + "</span></strong>");
            a.innerHTML = txt;
        }
    }
}