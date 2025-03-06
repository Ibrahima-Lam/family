
const slider = document.querySelector('.slider')
slider.addEventListener('click', function (e) {

    e.stopPropagation()

    const navlink = document.querySelector('.nav-link')
    navlink.classList.toggle("show")

    document.addEventListener('click', function (e) {
        e.stopPropagation()
        navlink.classList.remove("show")
    })

}, true)

function hideNavBar() {
    document.querySelector(".container")?.classList.add("move-container")
    document.querySelector(".nav-bar")?.classList.add("hide-nav-bar")
}
function showNavBar() {
    document.querySelector(".container")?.classList.remove("move-container")
    document.querySelector(".nav-bar")?.classList.remove("hide-nav-bar")

}


const scrollTop = document.getElementById('scrollTop')
scrollTop?.addEventListener('click', function (e) {
    window.scrollTo(0, 0)
})


document.addEventListener('scroll', function (e) {
    e.stopImmediatePropagation()

    const top = document.documentElement.scrollTop
    if (top < 50) {
        scrollTop?.classList.add("hid-scroll")
    } else {
        scrollTop?.classList.remove("hid-scroll")
    }
})

document.addEventListener('dblclick', function (e) {
    document.querySelector(".nav-bar").classList.toggle("hidden")
})