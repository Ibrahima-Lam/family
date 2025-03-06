 option={
            root:null,
            rootMargin:'0px',
            threshold:0.5
        }
        function callback(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    
                    console.log(entry)
                }
            });
        }
       const p=document.querySelector("p")
       let observer=new IntersectionObserver(callback,option)
       observer.observe(p)