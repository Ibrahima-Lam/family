// function ajaxGetter(url, callback, async = false) {
//     const xhr = new XMLHttpRequest()
//     xhr.onload = function () {
//         callback(this.responseText)
//     }
//     xhr.open("GET", url, async)
//     xhr.send()
// }
function autocomplete(formulaire) {

    const texte = formulaire.querySelector('#texte');
    texte.addEventListener("input", function (e) {
        getText(formulaire)


    })
    async function getText(formulaire) {
        const file = await fetch("?p=api/names")
        const text = await file.json()
        getList(text, formulaire)
    }
    const counter = {
        value: 0
    }
    const champs = {
        value: "",

    }


    function getList(text, formulaire) {

        const texte = formulaire.querySelector('input');
        const listes = formulaire.querySelector(".listes")
        remove(listes)
        const ul = document.createElement("ul")
        ul.style.backgroundColor = "darkgray"
        for (const key in text) {
            if (Object.hasOwnProperty.call(text, key)) {
                const element = text[key];

                let txt = texte.value
                const tab = txt.split(" ")
                tabs = tab

                txt = tab[tab.length - 1]
                if (element.toUpperCase().substring(0, txt.length) === txt.toUpperCase() && txt !== "" && element.length !== txt.length) {
                    const li = document.createElement("li")
                    li.setAttribute("class", "item")
                    li.innerText = element
                    ul.append(li)
                }
            }
        }
        if (ul.hasChildNodes()) {
            ul.firstChild.classList.add("actived")
        }

        listes.append(ul)
        const items = formulaire.querySelectorAll(".item")
        items.forEach(elmt => {
            elmt.addEventListener("click", function (e) {
                tabs[tabs.length - 1] = this.innerText
                texte.value = tabs.join(" ")
                texte.focus()
                remove(listes)
                e.stopPropagation()
            })
        });
        document.addEventListener("click", function () {

            remove(listes)

        })
        texte.addEventListener("keydown", function (e) {
            if (e.which === 13) {
                e.preventDefault()
                e.stopImmediatePropagation()
                const val = listes.querySelector(".actived").innerText
                tabs[tabs.length - 1] = val
                texte.value = tabs.join(" ")
                texte.focus()
                remove(listes)

            }

        })
        texte.addEventListener("keydown", function (e) {
            e.stopImmediatePropagation()

            if (e.which === 40) {
                counter.value++
                activer(listes)
            } else if (e.which === 38) {
                counter.value--
                activer(listes)
            }
        })
    }

    function activer(listes) {
        const elements = listes.querySelectorAll(".item")
        if (counter.value < 0) {
            counter.value = elements.length - 1
        } else if (counter.value >= elements.length) {
            counter.value = 0
        }
        elements.forEach(element => {
            element.classList.remove("actived")
        });
        for (let i = 0; i < elements.length; i++) {
            const el = elements[i];
            if (i === counter.value) {
                el.classList.add("actived")
            }
        }
    }

    function remove(listes) {
        if (listes.hasChildNodes()) {
            listes.firstChild.remove()
        }

    }
}
