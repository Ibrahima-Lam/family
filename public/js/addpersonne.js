function addforms() {


    const groupe = document.querySelector(".groupe")

    const frm = document.getElementById("frm")
    const grpe = frm.cloneNode(true)

    const add = document.getElementById("add")
    add.addEventListener("input", function (e) {
        const nbr = this.value
        if (!this.checkValidity()) {
            return
        }
        const v = fr.querySelector("select").value
        let grp = grpe.cloneNode(true)
        groupe.append(grp)
        verifier(this.value)

        checkParent(v)

        const forms = document.querySelectorAll(".form")
        forms.forEach(element => {

            element.addEventListener("submit", function (e) {
                e.preventDefault()

                e.stopImmediatePropagation()
                forms.forEach(el => {

                    const texte = el.querySelector(".field")
                    if (texte.checkValidity()) {
                        const tabs = {

                        }

                        const input = el.querySelectorAll(".champ")

                        for (let i = 0; i < input.length; i++) {
                            const lmt = input[i];
                            if (lmt.name === "sexe" && !lmt.checked || lmt.name === "annule") {
                                continue

                            }
                            tabs[lmt.getAttribute("name")] = lmt.value
                        }


                        poste(tabs)
                    }
                });
            })
        });

        const frms = document.querySelectorAll("form")
        frms.forEach(elmte => {
            elmte.querySelector("#texte").addEventListener("focus", function () {
                autocomplete(elmte)
            })
        });
        const check = document.getElementById("filtr").checked
        const froms = document.querySelectorAll("form")
        froms.forEach(emt => {
            const selec = emt.querySelector("select")
            selec.addEventListener("change", function () {
                if (check) {
                    checkParent(this.value)
                }
            })
        });
        cocher()
    })
}

// -------------------out addforms----------------------
addforms()
function poste($tab) {
    let url = ""
    for (const key in $tab) {
        if (Object.hasOwnProperty.call($tab, key)) {
            const element = $tab[key];
            url += key + "=" + element + "&"

        }
    }
    url = "traitepersonne.php?" + url + "token=" + true
    ajaxPoster(url)
}
function ajaxPoster(url, async = false) {
    const xhr = new XMLHttpRequest()
    xhr.onload = function () {

        window.location.assign("sommaire.php")
    }
    xhr.open("POST", url, async)
    xhr.send()
}
const fr = document.querySelector("form")
autocomplete(fr)

function checkParent(v) {
    const check = document.getElementById("filtr").checked
    if (check) {


        const forms = document.querySelectorAll("form")
        forms.forEach(el => {
            const select = el.querySelector("select")
            const option = select.querySelectorAll("option")
            option.forEach(els => {
                els.removeAttribute("selected")
            });
            option.forEach(element => {
                if (element.value === v) {
                    element.setAttribute("selected", "true")
                }
            });
        });
    }
}

function verifier(val) {
    const groupe = document.querySelector(".groupe")
    const forms = document.querySelectorAll("form")
    const frm = document.getElementById("frm")
    const grpe = frm.cloneNode(true)
    if (val < forms.length) {
        for (let i = 1; i <= (forms.length - val); i++) {
            forms[0].parentNode.lastChild.remove()

        }

    } else {
        for (let i = 1; i <= (val - forms.length); i++) {
            let grp = grpe.cloneNode(true)
            groupe.append(grp)
        }

    }
}
function cocher() {
    const check = document.getElementById("filtr")
    check.addEventListener("change", function () {
        if (this.checked) {
            const fr = document.querySelector("form")
            const v = fr.querySelector("select").value
            checkParent(v)
        }
    })
}