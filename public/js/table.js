const data = {
    param: "",
    ordrd: "id",
    ord: "asc",
    url: "",


    envoyer: function () {
        this.setUrl()
        ajaxGet(this.url, true)
    },
    setOrder: function (ordrd) {
        this.ordrd = ordrd

    },
    setUrl: function () {
        this.url = 'ajaxrecherche.php?param=' + this.param + '&ordrd=' + this.ordrd + '&ord=' + this.ord
        const page = window.location.href
        if (page.search(/editer.php/i) != -1) {
            this.url += "&edite=true"

        }
    }
}


function ajaxGet(url, async = false) {
    const srch = document.getElementById('srch');
    const xhr = new XMLHttpRequest()
    xhr.onreadystatechange = function () {
        if (this.status == 200 && this.readyState == 4) {
            document.querySelector("tbody").style.opacity = "1"
            affichage(this.responseText, "contains")
        } else {
            document.querySelector("tbody").style.opacity = "0.6"
        }


    }
    xhr.open("GET", url, async)
    xhr.send()
}

function affichage(params, id) {
    const parent = document.getElementById(id);
    parent.innerHTML = params
    Matcher()


}
function Matcher() {
    const srch = document.getElementById('srch');
    const val = srch.value
    const td = document.querySelectorAll("td")
    for (let i = 0; i < td.length; i++) {
        let element = td[i];
        let text = element.innerText
        let a = element.querySelector("a");
        const reg = new RegExp(val, "i")
        if (text.search(reg) != -1 && !element.contains(a)) {
            let txt = text.replace(reg, "<strong><span class='matched'>" + val + "</span></strong>");
            let tag = document.createElement("div");
            tag.innerHTML = txt;
            element.replaceChildren(tag);

        } else if (text.search(reg) != -1 && element.contains(a)) {
            text = a.innerHTML

            let txt = text.replace(reg, "<strong><span class='matched'>" + val + "</span></strong>");
            a.innerHTML = txt;
        }
    }
}

const srch = document.getElementById('srch');
srch.addEventListener("input", function () {
    let val = this.value
    data.param = val
    data.envoyer()

})

const template = document.getElementById('template');
const icon = template.content.cloneNode(true).querySelector("img")
const th = document.querySelectorAll(".sorters")
th.forEach(element => {
    element.addEventListener("click", function () {

        const value = this.querySelector("data").value
        const th2 = document.querySelectorAll(".sorters")

        th2.forEach(elmt => {
            const img = elmt.querySelector("img")
            elmt.classList.remove("active")
            if (elmt.contains(img)) {
                elmt.removeChild(img)
            }
        });
        if (value === data.ordrd) {
            data.ord = (data.ord === "asc") ? "desc" : "asc"
            icon.classList.toggle("desc")
        }
        this.classList.add("active")
        this.append(icon)
        data.setOrder(value)
        data.envoyer()
    })

});
document.addEventListener('loaded', function (e) {

    th.forEach(el => {
        const value = el.querySelector("data").value
        if (data.ord === "desc") {
            icon.classList.add("desc")
        }
        if (value === data.ordrd) {
            el.append(icon)
            el.classList.add("active")
        }
        data.envoyer()
    });
})