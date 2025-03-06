
function ajaxGets(url,async=false) {
    const xhr=new XMLHttpRequest()
    xhr.onload=function () {
         affichages(this.responseText,"contains")
    }
    xhr.open("GET",url,async)
    xhr.send()
}

const target={
    self:"",
    params:"",
    store:[],
    node:"",
    setNode:function () {
        const node=document.createElement("dd")
         node.innerHTML=this.params
         this.node=node
    },
    check:function (id) {
        for (const element of this.store) {
            if (element==id) {
                return false
            }
        }
        this.store.push(id)
        return true
    }
}

function affichages(params) {
    target.params=params
   const element=target.self;
   target.setNode()
     element.append(target.node)
  
}




// const details=document.querySelectorAll("details")
// for (let i = 0; i < details.length; i++) {
//     const element = details[i];
      
//     element.addEventListener("toggle",charge)
// }
function charge(element) {
    target.self=element
    id=element.id
    if (!target.check(id)) return 
    ajaxGets("ajaxarbre.php?id="+id)

}