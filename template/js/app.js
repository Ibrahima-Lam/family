
/* --------------function recuperateur des donnes en format json-------- */

let fileData = null
async function getFileData(params) {
    const data = await fetch("data/liste.json")
    fileData = await data.json()
}

/* -------------funtion callback des click pour creer de l'historique---------- */

function anchorsListener(anchors) {
    anchors.forEach(element => {
        element.addEventListener('click', async function (e) {
            if (!fileData) await getFileData()
            let ide = this.href.substring(this.href.indexOf("#") + 1)

            const { id, nom } = fileData[ide]
            const person = {
                id: id,
                nom: nom
            }
            const history = localStorage.getItem("historique") ?? "[]"
            let data = JSON.parse(history).filter((element, index) => {

                return index <= 9 ? true : false
            }).filter((element) => {
                if (element?.id == person.id) return false
                return true
            })
            data = [person, ...data]
            localStorage.setItem("historique", JSON.stringify(data))
            document.querySelector('tbody').innerHTML = ""
            document.querySelector('thead').innerHTML = ""
            document.querySelector('#titre').innerHTML = ""
        })
    });
}


function load() {

    const elements = document.querySelectorAll('.pseudo-element')
    elements.forEach(element => {
        const enfants = element.querySelector(".children")?.innerText ? "\n\n" + element.querySelector(".children")?.innerText : ""
        const prnts = element.querySelector(".parents")?.innerText ? "\n\n" + element.querySelector(".parents")?.innerText : ""
        const freres = element.querySelector(".siblings")?.innerText ? "\n\n" + element.querySelector(".siblings")?.innerText : ""
        const backprnts = element.querySelector(".packparents")?.innerText ? "\n\n" + element.querySelector(".packparents")?.innerText : ""
        const infos = element.querySelector(".personnal-infos")?.innerText ?? ""
        const id = element.dataset.id
        const nom = element.querySelector(".element-name").innerText
        const texte = `Nom : ${nom}\n${infos} ${backprnts}${freres}${enfants}${prnts}`

        /* -------Partager le contenu d'un element------------ */

        const share = element.querySelector('.share')
        share.addEventListener('click', function (e) {

            const shareData = {
                title: "famille Lammbé Dialadé",
                text: texte
            }

            if (navigator.canShare(shareData)) {
                navigator.share(shareData)
            }
        })

        /* ------------Copier le contenu de l'element--------- */

        const copy = element.querySelector('.copy')
        copy.addEventListener('click', function (e) {
            navigator.clipboard.writeText(texte)
            alert("copié!")
        })

        /* ----------ajout au bookmark -------------*/

        const bookmark = element.querySelector('.bookmark')
        bookmark.addEventListener('click', enregitrer)

        /* ---------function d'ajout aux favories et bookmark----------- */

        function enregitrer(e) {

            const person = {
                id: id,
                nom: nom
            }
            const favorie = localStorage.getItem("favorie") ?? "[]"
            let data = JSON.parse(favorie).filter((element) => {
                if (element?.id === person.id) return false
                return true
            })
            data = [person, ...data]
            localStorage.setItem("favorie", JSON.stringify(data))

        }

        /* ----------Afficher le Menu d'un element------------ */

        const menu = element.querySelector('.pseudo-menu')
        const treedot = menu.querySelector('img')
        const menucontainer = menu.querySelector('.menu-container')
        treedot.addEventListener('click', function (e) {
            e.stopPropagation()
            menucontainer.classList.toggle("show-menu")
            document.addEventListener('click', function (e) {
                menucontainer.classList.remove("show-menu")
            })


            /* --------------Copier le lien------------ */

            const copylink = element.querySelector('.copy-link')
            copylink.addEventListener('click', function (e) {

                navigator.clipboard.writeText(getlink())

            })

            /* -------------partager le lien--------- */

            const sharelink = element.querySelector('.share-link')
            sharelink.addEventListener('click', function (e) {

                const shareData = {
                    title: "Famille Lammbe Dialadé",
                    text: getlink()
                }
                if (navigator.canShare(shareData)) {
                    navigator.share(shareData)
                }

            })

            /* -----------Ajouter aux favorie-------------- */

            const favorielink = element.querySelector('.favorie-link')
            favorielink.addEventListener('click', enregitrer)

            /* ------------Alller à la page details-------- */

            const detailslink = element.querySelector('.details-link')
            detailslink.addEventListener('click', function (e) {
                window.location.assign(`livre.html#${id}`)
            })

            /*-----------masquer un element--------  */

            const masquelink = element.querySelector('.masque-link')
            masquelink.addEventListener('click', function (e) {
                element.remove()
            })

            /* -----------Creer un lien local--------- */

            function getlink() {
                let lien = window.location.href
                if (lien.match(/#\d*/)) {
                    return lien.replace(/#\d*/, `#${id}`)
                }
                return lien + `#${id}`

            }
        })

        /* ----------click sur le profile--------- */

        const profiles = element.querySelectorAll('.profile *')
        profiles.forEach(profile => {
            profile.addEventListener('click', function (e) {
                e.stopPropagation()
                alert(texte)
            })
        });

        /* ----------changement de l'historique----------------------- */

        const anchors = element.querySelectorAll('a')
        anchorsListener(anchors)

    });
}





