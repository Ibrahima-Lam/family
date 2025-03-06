
window.addEventListener('load', function (_e) {
    const list = document.getElementById('list');
    const tbody = list.querySelector("tbody")

    this.fetch("liste.json").then(res => {
        return res.json()
    }).then(data => {
        for (const key in data) {
            if (data.hasOwnProperty.call(data, key)) {
                const { id, nom, sexe, generation, parent, idParent } = data[key];
                const tr = document.createElement("tr")

                const ids = tdElement(id)
                tr.append(ids)

                const name = tdAndAnchorElement(nom, `#${id}`)
                tr.append(name)

                const sex = tdElement(sexe)
                tr.append(sex)

                const gene = tdElement(generation)
                tr.append(gene)

                if (parent) {
                    let prt = tdAndAnchorElement(parent, `#${idParent}`)
                    tr.append(prt)
                } else {
                    let prt = tdElement("")
                    tr.append(prt)
                }


                tbody.append(tr)

            }
        }
        addListener()
    })

})

function tdElement(text) {
    const td = document.createElement("td")
    td.innerHTML = text
    return td
}

function tdAndAnchorElement(text, link) {
    const a = document.createElement("a")
    a.append(text)
    a.href = link
    const td = document.createElement("td")
    td.append(a)
    return td
}

function addListener() {

    const td = document.querySelectorAll('td')
    td.forEach(element => {
        element.addEventListener('click', function (e) {
            e.preventDefault()
            alert("clicked!")
        })
    });
}