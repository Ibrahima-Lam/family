(function () {
    const buttons = document.querySelectorAll('[data-tree-action]')
    if (buttons.length === 0) return

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            const open = button.dataset.treeAction === 'expand'
            document.querySelectorAll('.person-tree details').forEach(function (details) {
                details.open = open
            })
        })
    })
})()
