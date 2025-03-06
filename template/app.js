
const elements = document.querySelectorAll('.pseudo-element')
elements.forEach(element => {
    const texte = element.innerText
    const id = element.dataset.id
    const share = element.querySelector('.share')
    share.addEventListener('click', function (e) {

        const shareData = {
            title: "famille ",
            text: texte
        }
        navigator.share(shareData)
    })

    const copy = element.querySelector('.copy')
    copy.addEventListener('click', function (e) {
        navigator.clipboard.writeText(texte)
        alert("copié!")
    })

    const bookmark = element.querySelector('.bookmark')
    bookmark.addEventListener('click', function (e) {
        sessionStorage.setItem("id", id)
        alert(id)
    })


    const menu = element.querySelector('.pseudo-menu')
    menu.addEventListener('click', function (e) {
        const menuItems = document.getElementById('menu-items').content.cloneNode(true);
        menu.appendChild(menuItems)
        alert("menu!")
    })

    const profiles = element.querySelectorAll('.profile *')
    profiles.forEach(profile => {
        profile.addEventListener('click', function (e) {
            e.stopPropagation()
            alert("profile!")
        })
    });
});

const scrollTop = document.getElementById('scrollTop')
scrollTop.addEventListener('click', function (e) {
    window.scrollTo(0, 0)
})

/* const scrollBottom = document.getElementById('scrollBottom')
scrollBottom?.addEventListener('click', function (e) {
    window.scrollTo(0, document.documentElement.scrollHeight)
}) */

document.addEventListener('scroll', function (e) {
    e.stopImmediatePropagation()

    const top = document.documentElement.scrollTop
    if (top < 50) {
        scrollTop.classList.add("hid-scroll")
    } else {
        scrollTop.classList.remove("hid-scroll")

    }

    /*  const bottom = document.documentElement.scrollHeight - top
     alert(bottom)
     if (bottom < 50) {
         scrollBottom.classList.add("hid-scroll")
     } else {
         scrollBottom.classList.remove("hid-scroll")
 
     } */
})

