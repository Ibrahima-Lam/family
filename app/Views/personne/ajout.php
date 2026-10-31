<?php
$title = 'Formulaire'
?>
<div class="container">
    <script src="assets/js/completion.js" defer></script>
    <script src="assets/js/addpersonne.js" defer></script>
    <script src="assets/js/ajout.js" defer></script>


    <div class='groupe'>
        <form action="" method="post" class="form" id="frm" onsubmit="postAll(event)">

            <div class="control">
                <label for="">Le nom de la personne:</label>
                <div>
                    <input type="text" id="texte" name='nom' class='field champ' placeholder='Entrer le Nom et Le Prenom' autocomplete="off" required>
                    <div class="listes">
                    </div>
                </div>
            </div>
            <div class="control">
                <label for="">le parent:</label>
                <div>
                    <select name="parent" id="parent" class='champ field' step="5">
                        <?=
                        $html
                        ?>
                    </select>
                </div>

            </div>



            <div class="control">
                <label for=" ">le sexe:</label>
                <div class="radio field">
                    <span>masculin:</span>
                    <input type="radio" name='sexe' value='M' checked class="champ">
                    <span> feminin:</span>
                    <input type="radio" name='sexe' class="champ" value='F'>
                </div>
            </div>

            <div class="action">
                <input type="reset" name="annule" value='annuler' class='btn btn-default '>
                <input type="submit" name='envoi' value='envoyer' class='btn btn-success champ'>
            </div>
        </form>


        <div class="corner">
            <label for="">filtre du parent</label>
            <select name="generation" id="gene">
                <option value="0">Toutes</option>
                <?= $gen ?>
            </select>
            <label for="">Ajouter</label>
            <input type="number" name="ajout" id="add" min='1' max="10" value="1" style="width:50px">
            <input type="checkbox" name="filtreur" id="filtr" checked>
        </div>
    </div>
</div>


<script>
    function ajax(url, async = false) {
        const xhr = new XMLHttpRequest()
        xhr.onload = function() {
            afficher(this.responseText)
        }
        xhr.open("GET", url, async)
        xhr.send()
    }

    function afficher(params) {
        const check = document.getElementById("filtr").checked
        if (check) {
            const forms = document.querySelectorAll("form")
            forms.forEach(element => {
                element.querySelector("select").innerHTML = params
                return
            });
        }
        const parent = document.getElementById('parent');
        parent.innerHTML = params

    }

    const gene = document.getElementById('gene');
    gene.addEventListener("change", function() {
        let id = this.value
        ajax("?p=api/options/" + id)

    })
</script>