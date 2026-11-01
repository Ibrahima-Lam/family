async function postData(data) {
    const params = new URLSearchParams(data)
    params.append('_token', window.CSRF_TOKEN || '')

    const response = await fetch('?p=api/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
            'Accept': 'application/json',
            'X-CSRF-Token': window.CSRF_TOKEN || ''
        },
        body: params.toString()
    })

    return response.json()
}

async function postAll(e) {
    e.preventDefault()
    e.stopImmediatePropagation()

    const forms = Array.from(document.querySelectorAll("form"))
    const requests = []

    forms.forEach(el => {
        const texte = el.querySelector(".field")
        if (texte && texte.checkValidity()) {
            const data = {
                nom: el.nom.value,
                surnom: el.surnom?.value ?? '',
                parent: el.parent.value,
                sexe: el.sexe.value,
            }
            requests.push(postData(data))
        }
    })

    const results = await Promise.all(requests)
    const failed = results.filter(res => res.status !== 1)

    if (failed.length > 0 && failed[0].message) {
        alert(failed[0].message)
        return
    }

    window.location.assign("?p=personne")
}
