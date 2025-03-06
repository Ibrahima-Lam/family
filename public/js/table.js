const srch2 = document.getElementById('search');
let globalData = [];
const Paramettre = {
    param: "",
    ordrd: "idPersonne",
    ord: "asc",
    url: "",


    envoyer: function () {
        this.setUrl()
        affichage()
    },
    setOrder: function (ordrd) {
        this.ordrd = ordrd

    },
    setUrl: function () {
        this.url = 'ajaxrecherche.php?param=' + this.param + '&ordrd=' + this.ordrd + '&ord=' + this.ord
        const page = window.location.href
        if (page.search(/editer.php/i) != -1) {
            this.url += "&edite=true"

        }
    }
}

window.addEventListener('load', function (e) {

    th.forEach(el => {
        const value = el.querySelector("data").value
        if (Paramettre.ord === "desc") {
            icon.classList.add("desc")
        }
        if (value === Paramettre.ordrd) {
            el.append(icon)
            el.classList.add("active")
        }
    });
    Paramettre.envoyer()
})


/* function ajaxGet(url, async = false) {

    const xhr = new XMLHttpRequest()
    xhr.onreadystatechange = function () {
        if (this.status == 200 && this.readyState == 4) {
            document.querySelector("tbody").style.opacity = "1"
            affichage(this.responseText, "contains")
        } else {
            document.querySelector("tbody").style.opacity = "0.6"
        }


    }
    xhr.open("GET", url, async)
    xhr.send()
} */

async function fetchData() {
    const url = '?p=api/personnes'
    const res = await fetch(url);
    const data = await res.json();
    return data;
}

const HtmlTable2 = {
    thead: ` <thead>
                <tr>
                    <th class="sorters"><data value='idPersonne'> ID</data></th>
                    <th class="sorters"><data value='nomPersonne'> NOM</data></th>
                    <th class="sorters"><data value='nomGeneration'> GENERATION</data></th>
                    <th class="sorters"><data value='sexe'> SEXE</data></th>
                    <th class="sorters"><data value='parent'> PARENT</data></th>
                    <th><data value='id'> Editer</data></th>
                    <th><data value='id'>Supprimer</data></th>
                </tr>
            </thead>`,
    table: (data) => `<table id="table" class="table striped ">${HtmlTable2.thead} ${data}</table>`,
    tbody: (data) => `<tbody id="contains">${data.map(value => `<tr>
        <td>${value.idPersonne}</td>
        <td><a href="?p=personne/details/${value.idPersonne}">${value.nomPersonne}</a></td>
        <td>${value.nomGeneration}</td><td>${value.sexe}</td>
        <td><a href="?p=personne/details/${value.idParent}">${value.parent ?? ''}</a></td>
                    ${value.idParent ? `<td id="${value.idPersonne}"data-i="${value.idPersonne}"data-p="${value.idParent}" title="Editer" class="edite" onclick="editer(this)"><a href="#"><img src="../public/icons/pencil.svg" width="15px"></a></td>` : `<td></td>`}
                        <td id="${value.idPersonne}" title="Supprimer" class="delete" onclick="supprimer(this)"><a href="#"><img src="../public/icons/basket.svg" width="15px"></a></td>
        </tr>`).join('')}
        </tbody>`,

}

async function affichage() {
    if (globalData.length == 0) {
        globalData = await fetchData()
    }
    let elements = globalData


    if (srch2.value != "") {
        elements = globalData.filter(value => value.nomPersonne.toLowerCase().includes(srch2.value.toLowerCase()))
    }
    if (Paramettre.ord == "asc") {
        elements = elements.sort((a, b) => {
            return a[Paramettre.ordrd] < b[Paramettre.ordrd] ? -1 : a[Paramettre.ordrd] > b[Paramettre.ordrd] ? 1 : 0
        })
    } else {
        elements = elements.sort((a, b) => {
            return a[Paramettre.ordrd] > b[Paramettre.ordrd] ? -1 : a[Paramettre.ordrd] < b[Paramettre.ordrd] ? 1 : 0
        })
    }
    const parent = document.getElementById("contains");
    parent.innerHTML = HtmlTable2.tbody(elements)
    Matcher()


}
function Matcher() {

    const val = srch2.value
    const td = document.querySelectorAll("td")
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


srch2.addEventListener("input", function (e) {

    let val = this.value
    Paramettre.param = val
    Paramettre.envoyer()

})

const template = document.getElementById('template');
const icon = template.content.cloneNode(true).querySelector("img")
const th = document.querySelectorAll(".sorters")
th.forEach(element => {
    element.addEventListener("click", function () {

        const value = this.querySelector("data").value
        const th2 = document.querySelectorAll(".sorters")

        th2.forEach(elmt => {
            const img = elmt.querySelector("img")
            elmt.classList.remove("active")
            if (elmt.contains(img)) {
                elmt.removeChild(img)
            }
        });
        if (value === Paramettre.ordrd) {
            Paramettre.ord = (Paramettre.ord === "asc") ? "desc" : "asc"
            icon.classList.toggle("desc")
        }
        this.classList.add("active")
        this.append(icon)
        Paramettre.setOrder(value)
        Paramettre.envoyer()
    })

});
