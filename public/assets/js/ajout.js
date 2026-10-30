
async function postData(data) {
    const params = new URLSearchParams(data)
    params.append('_token', window.CSRF_TOKEN || '')
    let req = await fetch('?p=api/add&' + params.toString())
    return req.json()
}


function postAll(e) {
    e.preventDefault()
    e.stopImmediatePropagation()
    const forms = document.querySelectorAll("form")
    const length = forms.length
    const result = []
    const valideds = []
    const success = []
    forms.forEach(async el => {
        const texte = el.querySelector(".field")
        if (texte.checkValidity()) {
            valideds.push(el)
            const data = {
                nom: el.nom.value,
                parent: el.parent.value,
                sexe: el.sexe.value,
            }
            let res = await postData(data);
            result.push(res)
            if (res.status) {
                success.push(res)
            }
        }

    });

    window.location.assign("?p=personne")
}
