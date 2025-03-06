function ajaxAction(url, callback, async = true) {
    const xhr = new XMLHttpRequest()
    xhr.onload = function () {

        callback(this.responseText)
    }
    xhr.open("GET", url, async)
    xhr.send()
}
function alerter(val) {
    alert(val)
}


function editer(el) {
    const nom = el.parentNode.innerText
    getForm(el.id, nom)
}

function supprimer(el) {
    if (confirm("Voulez vous Supprimer cet Element")) {
        ajaxAction("ajaxdelete.php?id=" + el.id, alerter)
        const tr = el.parentNode
        tr.style.backgroundColor = "red"
        setTimeout(function () {
            tr.remove()
        }, 1000)

    }

}
function getForm(id, nom) {

    const edition = document.getElementById("edition").content.cloneNode(true)
    const fixed = document.querySelector(".fixeur")
    edition.querySelector("#num").value = id
    edition.querySelector("#demo").innerHTML = nom
    fixed.append(edition)

    envoyer()
    fermer(fixed)
    echanger()
}
const donnee = {

    construit: function (idpers, type, valeur) {
        this.idPersonne = idpers
        this.idSupplement = type
        this.valeur = valeur
    },
    getUrl: function () {
        return "traiteposseder.php?idPersonne=" + this.idPersonne + "&idSupplement=" + this.idSupplement + "&valeur=" + this.valeur
    }

}
function envoyer() {
    const frm = document.getElementById("sup")
    frm.addEventListener("submit", function (e) {
        e.preventDefault()
        const id = frm.querySelector("input[name='id']").value
        const content = frm.querySelector("input[name='content']").value
        const type = frm.querySelector("select[name='type']").value
        donnee.construit(id, type, content)
        ajaxAction(donnee.getUrl(), alerter)
    })
}
function fermer(fixed) {
    const close = document.getElementById("close")
    close.addEventListener("click", function () {
        fixed.lastChild.remove()
    })
}
function echanger() {
    const toggler = document.getElementById("toggler")
    const frm = document.getElementById("sup")
    const frms = document.querySelector(".containes")

    toggler.addEventListener("click", function () {
        if (frms.contains(frm)) {
            const id = frm.querySelector("input[name='id']").value
            ajaxAction("updatepersonne.php?id=" + id, Coller)
            toggler.innerHTML = "Ajouter"

        } else {
            frms.innerHTML = ""
            frms.append(frm)
            toggler.innerHTML = "Modifier"
        }
        modifier()
    })

}
function Coller(val) {
    const frms = document.querySelector(".containes")
    frms.innerHTML = val
}

function modifier() {
    const frm = document.getElementById("updating")
    frm.addEventListener("submit", function (e) {
        e.preventDefault()
        alert("modifié")
    })
}



const fix = document.querySelector(".fixeur")
fix.addEventListener("drag", function () {
    alert("ok")
    document.addEventListener("drop", function (e) {
        alert(e.target)
    })
})