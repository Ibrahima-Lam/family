(function () {
    function normalize(value) {
        return String(value ?? '')
            .normalize('NFD')
            .replace(/\p{Diacritic}/gu, '')
            .toLowerCase()
    }

    function filter(input) {
        const selector = input.dataset.staticSearch
        if (!selector) return

        const rows = Array.from(document.querySelectorAll(selector))
        const empty = document.querySelector(input.dataset.staticSearchEmpty || '')

        input.addEventListener('input', function () {
            const term = normalize(input.value).trim()
            let visible = 0

            rows.forEach(function (row) {
                const match = term === '' || normalize(row.textContent).includes(term)
                row.hidden = !match
                if (match) visible++
            })

            if (empty) empty.hidden = visible !== 0
        })
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-static-search]').forEach(filter)
    })
})()
