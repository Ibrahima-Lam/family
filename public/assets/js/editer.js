function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;')
}

function jsonHeaders() {
    return {
        'Accept': 'application/json',
        'X-CSRF-Token': window.CSRF_TOKEN || ''
    }
}

async function deleteData(id) {
    const body = new URLSearchParams({ _token: window.CSRF_TOKEN || '' })
    const req = await fetch('?p=api/delete/' + encodeURIComponent(id), {
        method: 'POST',
        headers: {
            ...jsonHeaders(),
            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
        },
        body: body.toString()
    })
    return await req.json()
}

function editer(el) {
    showDialog(el.dataset.i, el.dataset.p)
}

async function supprimer(el) {
    if (confirm("Voulez vous Supprimer cet Element")) {
        const res = await deleteData(el.id)
        if (res.status == 1) {
            alert(res.message)
            const tr = el.parentNode
            tr.style.backgroundColor = "red"
            setTimeout(function () {
                tr.remove()
            }, 1000)
        } else {
            alert(res.message)
        }
    }
}

async function getOptions(selected) {
    let req = await fetch('?p=api/options&selected=' + encodeURIComponent(selected), {
        headers: jsonHeaders()
    })
    return await req.text()
}

async function sendData(params) {
    const req = await fetch('?p=api/edit', {
        method: 'POST',
        headers: {
            ...jsonHeaders(),
            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
        },
        body: params.toString()
    })
    return await req.json()
}

async function showDialog(i, p) {
    if (globalData.length == 0) {
        globalData = await fetchData()
    }
    let element = globalData.find(element => element.idPersonne == +i && element.idParent == +p)
    const dialog = document.getElementById("dialog")
    dialog.querySelector('.dialog-body').innerHTML = formulaire({ ...element, options: await getOptions(p) })
    const form = dialog.querySelector('form')
    dialog.showModal()
    form.addEventListener("submit", async function (e) {
        e.preventDefault()
        const data = new FormData(form)
        const params = new URLSearchParams()
        params.append('id', i)
        data.forEach(function (value, key) {
            params.append(key, value)
        })
        params.append('_token', window.CSRF_TOKEN || '')

        let req = await sendData(params)
        alert(req.message)
        if (req.status) {
            dialog.close()
            window.location.reload()
        }
    })

    const close = dialog.querySelector("#close")
    close.onclick = function () {
        dialog.close()
    }
}

function formulaire({ nomPersonne, surnom, sexe, options }) {
    return `<form action="" method="post" class="form" id="frm">
            <input type="hidden" name="_token" value="${escapeHtml(window.CSRF_TOKEN || '')}">
            <div class="form-block">
                <label for="">Le nom de la personne:</label>
                <div>
                    <input type="text" id="texte" name='nom' class='field champ' value='${escapeHtml(nomPersonne ?? '')}' placeholder='Entrer le nom et Le prenom' autocomplete="off" required>
                    <div class="listes">
                    </div>
                </div>
            </div>
            <div class="form-block">
                <label for="">Le surnom de la personne:</label>
                <div>
                    <input type="text" id="surnom" name="surnom" class="field" value="${escapeHtml(surnom ?? '')}" placeholder="Surnom facultatif" autocomplete="off">
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
