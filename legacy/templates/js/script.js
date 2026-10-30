const param = {
    value: null,
    order: "asc",
    ordered: null
}

const search = document.getElementById('search');
search.addEventListener('input', function (e) {
    param.value = this.value
    chargeIndex()
})

search.addEventListener('focus', function (e) {
    document.querySelector('.form-group').style.display = "none"
    hideNavBar()
})

search.addEventListener('blur', function (e) {
    document.querySelector('.form-group').style.display = "inline-flex"
    showNavBar()
})

const ordered = document.getElementById('ordered');
ordered.addEventListener('change', function (e) {
    param.ordered = this.value
    chargeIndex()
})

const orders = document.querySelectorAll('.orders')
orders.forEach(element => {
    element.addEventListener('change', function (e) {
        param.order = this.value
        param.ordered = ordered.value ?? param.ordered ?? "id"
        chargeIndex()
    })
});

window.addEventListener('load', async function (e) {
    await chargeIndex()
})


const indexList = document.getElementById("index-container")
let dataTable = []

async function getdata() {

    const res = await fetch("data/liste.json")
    const data = await res.json()
    for (const key in data) {
        if (data.hasOwnProperty.call(data, key)) {
            dataTable.push(data[key])

        }
    }
}






async function chargeIndex() {
    indexList.innerHTML = ""
    if (dataTable.length == 0) await getdata()
    let elements = dataTable.sort((a, b) => {
        return a.nom < b.nom ? -1 : 1
    })

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
                || id.toString().includes(value.toString())
                || (parent ?? "").toUpperCase().includes(value.toUpperCase())
        })
    }


    const elements1 = elements.filter((element, index, tab) => {
        return index <= tab.length / 2
    })
    let ul = createList(elements1)
    indexList.append(ul)


    const elements2 = elements.filter((element, index, tab) => {
        return index > tab.length / 2
    })

    ul = createList(elements2)
    indexList.append(ul)
    addIndexListener()

    function createList(elements) {

        const ul = document.createElement("ul")
        elements.forEach(element => {
            const li = liAndAnchorElement(element)
            ul.append(li)
        });
        return ul
    }
    function addIndexListener() {
        const tempDialog = document.getElementById("temp-dialog").content.cloneNode(true)
        const dialog = tempDialog.querySelector('.dialog');
        const closeDialog = dialog.querySelector('.close');
        closeDialog.addEventListener('click', function (e) {
            dialog.close()
            document.body.removeChild(dialog)
        })



        const li = indexList.querySelectorAll('.item-list')
        li.forEach(element => {
            let { id, nom, generation, sexe, children, siblings, parents, parent, backparents } = element.dataset
            backparents = backparents?.replace("#", "element.html#")
            const parentslist = constructList(parents, "Parents", "parents")
            const siblingslist = constructList(siblings, "Fréres et Soeurs", "siblings")
            const childrenlist = constructList(children, "Enfants", "children")

            const menu = element.querySelector(".element-index-menu")
            const list = menu.querySelector(".list-content")
            menu.addEventListener('click', function (e) {
                e.stopPropagation()
                list.querySelector(".nom").innerHTML = `${nom}`
                list.style.display = list.style.display === "block" ? "none" : "block"
                document.addEventListener('click', function (e) {
                    list.style.display = "none"
                })

                const menuItems = list.querySelectorAll('.list-menu-item')
                menuItems.forEach(item => {
                    item.addEventListener('click', function (e) {
                        e.stopImmediatePropagation()
                        const elmt = this.dataset.element
                        let text = ``
                        const texte = allInfos()
                        list.style.display = "none"
                        switch (elmt) {
                            case "infos":
                                const infos = infosPersonnelle()
                                alert(infos)
                                return false
                                break;
                            case "lignee":
                                text = getLigneeText()
                                break;

                            case "parents":
                                text = simpleList(parents, "Parent")
                                break;
                            case "siblings":
                                text = simpleList(siblings, "Frére ni Soeur")
                                break;
                            case "children":
                                text = simpleList(children, "Enfant")

                                break;
                            case "element":
                                showDialog()
                                return true
                                break;

                            case "copy":
                                navigator.clipboard.writeText(texte)
                                return true
                                break;
                            case "share":

                                const shareData = {
                                    title: "Famille Lammbe Dialadé",
                                    text: texte
                                }
                                if (navigator.canShare(shareData)) navigator.share(shareData)
                                return true
                                break;


                            default:
                                break;
                        }
                        alert(text)
                    }, true)
                });
            })

            const elementId = element.querySelector('.element-id')
            elementId.addEventListener('click', function (e) {
                showDialog()

            })

            const elementFragment = element.querySelector(".element-fragment")
            elementFragment.addEventListener('click', function (e) {
                if (!["none", ""].includes(list.style.display)) return false
                e.stopImmediatePropagation()
                const infos = infosPersonnelle()
                alert(infos)
            })

            const link = element.querySelector(".element-fragment a")
            link.addEventListener('click', function (e) {
                e.stopImmediatePropagation()
            })

            function getLigneeText() {
                if (!backparents) return null
                const div = document.createElement("div")
                div.innerHTML = backparents
                const contenu = div.innerText
                const text = `...${contenu}<=${nom}\n\n`
                return text
            }

            function infosPersonnelle() {
                return ` ID : ${id}\n Nom : ${nom}\n Genre : ${sexe}\n Génération : ${generation} \n Parent :  ${parent ?? ""}\n\n`

            }

            function allInfos() {
                let texte = ""
                texte += `${infosPersonnelle()}`
                texte += `Lignée\n\n${getLigneeText() ?? ""}`
                texte += `${simpleList(parents, "Parent", "Parents")}`
                texte += `${simpleList(siblings, "Frére ni Soeur", "Fréres et Soeurs")}`
                texte += `${simpleList(children, "Enfant", "Enfants")}`
                return texte
            }

            function showDialog() {
                dialog.querySelector(".dialog-header").innerHTML = `${nom}`
                const container = dialog.querySelector(".dialog-content")
                container.innerHTML = backparents.length !== 0 ? `${backparents}<=${nom}` : ""
                container.append(parentslist ?? "")
                container.append(siblingslist ?? "")
                container.append(childrenlist ?? "")
                document.body.append(dialog)
                if (dialog.open) dialog.close()
                dialog.showModal()

            }
        });


        function simpleList(elmnts, text = "Resultat", title = "") {
            const elements = JSON.parse(elmnts)
            if (elements.length === 0) return `Pas ${text === "Enfant" ? "d'" : "de "}${text}\n\n`
            let list = `${title}\n`
            elements.forEach(element => {
                const { nom } = element
                list += `\n${nom}`
            });
            return list + "\n\n"
        }


        function constructList(elmnts, title, clss = '') {
            const elements = JSON.parse(elmnts)
            if (elements.length === 0) return null
            const div = document.createElement("div")
            const heading = document.createElement("h4")
            heading.innerHTML = title
            heading.classList.add("title")
            div.append(heading)
            div.classList.add(clss)
            elements.forEach(element => {
                const { id, nom } = element
                const dd = constructElement(id, nom)
                div.append(dd)
            });
            return div
        }

        function constructElement(id, nom) {
            const dd = document.createElement("dd")
            const a = document.createElement("a")
            a.href = `#${id}`
            a.innerHTML = nom
            dd.append(a)
            return dd
        }
    }
}



function liAndAnchorElement(element) {
    const { id, nom, generation, sexe, children, siblings, idg, parent, parents, backparents } = element
    const a = document.createElement("a")
    a.append(nom)
    const div = document.createElement("div")
    div.classList.add("sub-text")
    div.innerHTML = `${parent ?? ""}    |${generation}|${sexe}`
    a.href = `element.html#${id}`
    const li = document.getElementById('li-element').content.cloneNode(true);
    const liElement = li.querySelector('li')
    liElement.dataset.id = id
    liElement.dataset.nom = nom
    liElement.dataset.generation = generation
    liElement.dataset.sexe = sexe
    liElement.dataset.parent = parent
    liElement.dataset.idg = idg
    liElement.dataset.children = JSON.stringify(children ?? [])
    liElement.dataset.siblings = JSON.stringify(siblings ?? [])
    liElement.dataset.parents = JSON.stringify(parents ?? [])
    liElement.dataset.backparents = backparents ?? ""

    li.querySelector('.element-id').innerHTML = `<strong>${id}</strong>`
    const fragment = li.querySelector('.element-fragment')
    fragment.append(a)
    fragment.append(div)

    return li
}


