


const menubar = document.getElementById('menu-bar');
const menu = document.getElementById('menu')
menubar.addEventListener("click", function (e) {
    e.preventDefault()
    e.stopPropagation()
    if (menu.style.left != "-40px") {
        menu.style.left = "-40px"
    } else {

        menu.style.left = "-300px"
    }

})
document.addEventListener("click", function () {

    if (menu.style.left != "-300px") {
        menu.style.left = "-300px"
    }
})
const link = document.getElementById('link');

const listlink = document.getElementById('list-link');
listlink.addEventListener("click", function (e) {
    e.preventDefault()

    if (link.style.display !== "block") {
        link.style.display = "block"
        e.stopPropagation()
    }
    document.addEventListener("click", function () {
        link.style.display = "none"

    })
})