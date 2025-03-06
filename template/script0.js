
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