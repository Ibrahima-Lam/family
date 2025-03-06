
async function postData(data) {
    let dt = ''
    for (let key in data) {
        dt += key + '=' + data[key] + '&'
    }
    let req = await fetch('?p=api/add&' + dt)
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
