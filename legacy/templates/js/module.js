/* const list = document.getElementById('list');
const tbody = list.querySelector("tbody") */

async function charge() {
    tbody.innerHTML = ""
    if (dataTable.length === 0) await getdata()
    let elements = dataTable


    const ordered = param.ordered
    if (ordered) {
        const order = param.order
        elements = elements.sort((a, b) => {
            const p = a[ordered] < b[ordered] ? 1 : -1
            return order === "desc" ? p : -p
        })
    }

    const value = param.value
    if (value) {
        elements = elements.filter(elmnt => {
            const { id, nom, parent } = elmnt
            return nom.toUpperCase().includes(value.toUpperCase())
                || (parent ?? "").toUpperCase().includes(value.toUpperCase())
                || id.toString().includes(value.toString())
        })
    }

    elements.forEach(data => {

        const { id, nom, sexe, generation, parent, idParent } = data;
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

    })

    addListener()
    function addListener() {

        const td = document.querySelectorAll('td')
        td.forEach(element => {
            element.addEventListener('click', function (e) {
                e.preventDefault()
                alert("clicked!")
            })
        });
    }

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
}