

async function deleteData(id) {
    let req = await fetch('?p=api/delete/' + id)
    return await req.json()
}


function editer(el) {
    showDialog(el.dataset.i, el.dataset.p)
}

async function supprimer(el) {
    if (confirm("Voulez vous Supprimer cet Element")) {
        let res = await deleteData(el.id)
        if (res.status == 1) alert(res.message)
        const tr = el.parentNode
        tr.style.backgroundColor = "red"
        setTimeout(function () {
            tr.remove()
        }, 1000)


    }

}
async function getOptions(selected) {
    let req = await fetch('?p=api/options&selected=' + selected)
    let data = await req.text()
    return data
}
async function sendData(data) {

    let req = await fetch('?p=api/edit&' + data,)
    return await req.json()
}
async function showDialog(i, p) {
    if (globalData.length == 0) {
        globalData = await fetchData()
    }
    let element = globalData.find(element => element.idPersonne == + i && element.idParent == +p)
    const dialog = document.getElementById("dialog")
    dialog.querySelector('.dialog-body').innerHTML = formulaire({ ...element, options: await getOptions(p) })
    const form = dialog.querySelector('form')
    dialog.showModal()
    form.addEventListener("submit", async function (e) {
        e.preventDefault()
        const data = new FormData(form)
        let dt = 'id=' + i + '&'
        data.forEach(function (value, key) {
            dt += key + '=' + value + '&'
        })

        let req = await sendData(dt)
        alert(req.message)
        if (req.status) {
            dialog.close()
            window.location.reload()
        }


    })


    const close = document.getElementById("close")
    close.addEventListener("click", function () {
        dialog.close()

    })

}



function formulaire({ nomPersonne, sexe, options }) {
    return `<form action="" method="" class="form" id="frm">
            <div class="form-block">
                <label for="">Le nom de la personne:</label>
                <div>
                    <input type="text" id="texte" name='nom' class='field champ' value='${nomPersonne ?? ''}' placeholder='Entrer le nom et Le prenom' autocomplete="off" required>
                    <div class="listes">
                    </div>
                </div>
            </div>
            <div class="form-block">
                <label for="">Le parent:</label>
                <div>
                    <select name="parent" id="parent" class='champ field' step="5">
                       ${options}
                    </select>
                </div>

            </div>
            <div class="form-block">
                <label for=" ">le sexe:</label>
                <div class="radio field">
                    <span>masculin:</span>
                    <input type="radio" name='sexe' value='M'  ${sexe == 'M' ? 'checked' : ''} class="champ">
                    <span> feminin:</span>
                    <input type="radio" name='sexe'value='F'  ${sexe == 'F' ? 'checked' : ''} class="champ">
                </div>
            </div>

            <div class="action">
               <button type="reset" id="annule" name='annule'  class='btn btn-default champ'>Annuler</button>
                <button type="submit" id="envoi" name='envoi'  class='btn btn-success champ'>Envoyer</button>

            </div>
        </form>`
}