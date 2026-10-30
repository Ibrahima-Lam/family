
export const loadData = async function (id = null) {
    const url = id ? `?p=api/personne/${id}` : '?p=api/personnes'
    const res = await fetch(url);
    const data = await res.json();
    return data;
}

export const HtmlTable = {
    thead: `<thead><tr><th>id</th><th>nom</th><th>generation</th><th>sexe</th><th>parent</th></tr></thead>`,
    table: (data) => `<table id="table" class="table striped ">${HtmlTable.thead} ${data}</table>`,
    tbody: (data) => `<tbody id="contains">${data.map(value => `<tr><td>${value.idPersonne}</td><td><a href="#${value.idPersonne}">${value.nomPersonne}</a></td><td>${value.nomGeneration}</td><td>${value.sexe}</td><td><a href="#${value.idParent}">${value.parent}</a></td></tr>`).join('')}</tbody>`,

}
