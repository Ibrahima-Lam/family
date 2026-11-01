(() => {
    const form = document.querySelector('[data-multi-form]')
    if (!form) return

    const rowsContainer = form.querySelector('[data-rows]')
    const template = document.getElementById('person-row-template')
    const addButton = form.querySelector('[data-add-row]')
    const submitButton = form.querySelector('[data-submit]')
    const submitLabel = form.querySelector('[data-submit-label]')
    const errorBox = form.querySelector('[data-form-error]')
    const countLabel = form.querySelector('[data-row-count]')
    const applyParent = document.getElementById('apply-parent')
    const generationFilter = document.getElementById('gene')
    const namesList = document.getElementById('personnes-noms')
    const singleDialog = document.getElementById('single-person-dialog')
    const singleForm = document.getElementById('single-person-form')
    const singleError = singleForm?.querySelector('[data-single-error]')
    const singleSubmit = singleForm?.querySelector('[data-single-submit]')
    const singleSubmitLabel = singleForm?.querySelector('[data-single-submit-label]')
    const maxRows = 10
    let nextIndex = 0

    const rows = () => Array.from(rowsContainer.querySelectorAll('[data-row]'))
    const firstRow = () => rows()[0] ?? null
    const parentSelect = (row) => row?.querySelector('[data-field="parent"]') ?? null

    function setError(message) {
        errorBox.textContent = message
        errorBox.hidden = message === ''
    }

    function setLoading(loading) {
        submitButton.disabled = loading
        submitButton.classList.toggle('is-loading', loading)
        submitLabel.textContent = loading ? 'Enregistrement…' : 'Créer les fiches'
        addButton.disabled = loading || rows().length >= maxRows
    }

    function syncRows() {
        const list = rows()
        list.forEach((row, index) => {
            row.querySelector('[data-row-number]').textContent = String(index + 1)
            const remove = row.querySelector('[data-remove-row]')
            remove.hidden = list.length === 1
            remove.setAttribute('aria-label', `Supprimer la fiche ${index + 1}`)
        })

        countLabel.textContent = `${list.length} fiche${list.length > 1 ? 's' : ''}`
        addButton.disabled = list.length >= maxRows
        addButton.title = list.length >= maxRows ? `Maximum de ${maxRows} fiches atteint` : ''
    }

    function addRow({ focus = true } = {}) {
        if (rows().length >= maxRows) return null

        const index = nextIndex
        nextIndex += 1

        const holder = document.createElement('div')
        holder.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(index)).trim()
        const row = holder.firstElementChild

        rowsContainer.append(row)
        syncRows()

        if (focus) {
            row.querySelector('[data-field="nom"]')?.focus()
        }

        return row
    }

    function applyFirstParent() {
        if (!applyParent.checked) return

        const value = parentSelect(firstRow())?.value ?? ''
        rows().slice(1).forEach((row) => {
            const select = parentSelect(row)
            if (select && Array.from(select.options).some((option) => option.value === value)) {
                select.value = value
            }
        })
    }

    async function postPerson(person) {
        const body = new URLSearchParams({
            _token: window.CSRF_TOKEN || '',
            nom: person.nom,
            surnom: person.surnom,
            parent: person.parent,
            sexe: person.sexe
        })

        const response = await fetch('?p=api/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                'Accept': 'application/json',
                'X-CSRF-Token': window.CSRF_TOKEN || ''
            },
            body: body.toString()
        })

        const result = await response.json().catch(() => ({}))
        if (!response.ok || (result.status !== 1 && result.status !== '1')) {
            throw new Error(result.message || 'La fiche n’a pas pu être enregistrée.')
        }

        return result
    }

    rowsContainer.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-row]')
        if (!button || rows().length <= 1) return

        button.closest('[data-row]')?.remove()
        syncRows()
    })

    rowsContainer.addEventListener('change', (event) => {
        if (event.target === parentSelect(firstRow())) {
            applyFirstParent()
        }
    })

    applyParent.addEventListener('change', applyFirstParent)
    addButton.addEventListener('click', () => addRow())

    generationFilter.addEventListener('change', async () => {
        const selectedParents = rows().map((row) => parentSelect(row)?.value ?? '')
        addButton.disabled = true

        try {
            const endpoint = generationFilter.value === '0'
                ? '?p=api/options'
                : '?p=api/options/' + encodeURIComponent(generationFilter.value)
            const response = await fetch(endpoint, {
                headers: { Accept: 'text/html' }
            })
            if (!response.ok) throw new Error('Impossible de charger les parents.')

            const options = await response.text()
            rows().forEach((row, index) => {
                const select = parentSelect(row)
                if (!select) return

                const selectedParent = selectedParents[index] ?? ''
                select.innerHTML = '<option value="">Aucun parent (racine)</option>' + options
                if (Array.from(select.options).some((option) => option.value === selectedParent)) {
                    select.value = selectedParent
                }
            })

            applyFirstParent()
        } catch (error) {
            setError(error.message)
        } finally {
            addButton.disabled = rows().length >= maxRows
        }
    })

    form.addEventListener('submit', async (event) => {
        event.preventDefault()
        setError('')

        if (!form.reportValidity()) {
            return
        }

        const payloads = rows().map((row, index) => ({
            nom: row.querySelector('[data-field="nom"]')?.value.trim() ?? '',
            surnom: row.querySelector('[data-field="surnom"]')?.value.trim() ?? '',
            parent: parentSelect(row)?.value ?? '',
            sexe: row.querySelector('input[type="radio"]:checked')?.value ?? '',
            index
        }))

        const invalid = payloads.find((person) => person.nom === '' || !['M', 'F'].includes(person.sexe))
        if (invalid) {
            setError(`La fiche ${invalid.index + 1} est incomplète.`)
            return
        }

        setLoading(true)

        try {
            for (const person of payloads) {
                try {
                    await postPerson(person)
                } catch (error) {
                    throw new Error(`${error.message} Fiche ${person.index + 1}.`)
                }
            }

            window.location.assign('?p=personne')
        } catch (error) {
            setLoading(false)
            setError(error.message || 'Une erreur est survenue pendant l’enregistrement.')
        }
    })

    function setSingleError(message) {
        if (!singleError) return
        singleError.textContent = message
        singleError.hidden = message === ''
    }

    function setSingleLoading(loading) {
        if (!singleSubmit || !singleSubmitLabel) return
        singleSubmit.disabled = loading
        singleSubmit.classList.toggle('is-loading', loading)
        singleSubmitLabel.textContent = loading ? 'Enregistrement…' : 'Créer la fiche'
    }

    function openSingleDialog() {
        if (!singleDialog) return
        setSingleError('')
        setSingleLoading(false)
        singleDialog.showModal()
        singleForm?.querySelector('#single-nom')?.focus()
    }

    document.querySelectorAll('[data-open-single]').forEach((button) => {
        button.addEventListener('click', openSingleDialog)
    })

    document.querySelectorAll('[data-close-single]').forEach((button) => {
        button.addEventListener('click', () => singleDialog?.close())
    })

    singleDialog?.addEventListener('click', (event) => {
        if (event.target !== singleDialog) return

        const rect = singleDialog.getBoundingClientRect()
        const outside = event.clientX < rect.left || event.clientX > rect.right
            || event.clientY < rect.top || event.clientY > rect.bottom

        if (outside) singleDialog.close()
    })

    singleForm?.addEventListener('submit', async (event) => {
        event.preventDefault()
        setSingleError('')

        if (!singleForm.reportValidity()) {
            return
        }

        const person = {
            nom: singleForm.querySelector('#single-nom')?.value.trim() ?? '',
            surnom: singleForm.querySelector('#single-surnom')?.value.trim() ?? '',
            parent: singleForm.querySelector('#single-parent')?.value ?? '',
            sexe: singleForm.querySelector('input[name="sexe"]:checked')?.value ?? ''
        }

        setSingleLoading(true)

        try {
            await postPerson(person)
            window.location.assign('?p=personne')
        } catch (error) {
            setSingleLoading(false)
            setSingleError(error.message || 'La fiche n’a pas pu être enregistrée.')
        }
    })

    async function loadKnownNames() {
        try {
            const response = await fetch('?p=api/names', { headers: { Accept: 'application/json' } })
            if (!response.ok) return

            const names = await response.json()
            if (!Array.isArray(names)) return

            const fragment = document.createDocumentFragment()
            names.forEach((name) => {
                const option = document.createElement('option')
                option.value = String(name)
                fragment.append(option)
            })
            namesList.replaceChildren(fragment)
        } catch {
            // L’autocomplétion est une aide : son échec ne bloque pas la saisie.
        }
    }

    addRow({ focus: false })
    loadKnownNames()
})()
