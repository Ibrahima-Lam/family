<?php
$title = 'Ajouter une personne';
?>

<script src="assets/js/completion.js" defer></script>
<script src="assets/js/addpersonne.js" defer></script>
<script src="assets/js/ajout.js" defer></script>

<div class="container">
    <div class="page-head">
        <div>
            <span class="eyebrow">Nouvelle entrée</span>
            <h1>Ajouter une personne</h1>
            <p>Renseignez le nom, le surnom éventuel, le parent et le sexe. Vous pouvez préparer plusieurs fiches en une seule fois.</p>
        </div>
    </div>

    <div class="groupe">
        <form action="" method="post" class="form" id="frm" onsubmit="postAll(event)">
            <?= csrf_field() ?>

            <div class="control">
                <label for="texte">Nom</label>
                <div>
                    <input type="text" id="texte" name="nom" class="field champ" placeholder="Prénom et nom" autocomplete="off" required>
                    <div class="listes"></div>
                </div>
            </div>

            <div class="control">
                <label for="surnom">Surnom <span class="badge badge-muted">facultatif</span></label>
                <input type="text" id="surnom" name="surnom" class="field" placeholder="Autre nom d’usage" autocomplete="off">
            </div>

            <div class="control">
                <label for="parent">Parent</label>
                <select name="parent" id="parent" class="champ field">
                    <?= $html ?>
                </select>
            </div>

            <div class="control">
                <label>Sexe</label>
                <div class="radio field">
                    <label for="sexe-m">
                        <input type="radio" id="sexe-m" name="sexe" value="M" checked class="champ">
                        Masculin
                    </label>
                    <label for="sexe-f">
                        <input type="radio" id="sexe-f" name="sexe" value="F" class="champ">
                        Féminin
                    </label>
                </div>
            </div>

            <div class="action">
                <input type="reset" name="annule" value="Annuler" class="btn btn-default">
                <input type="submit" name="envoi" value="Enregistrer" class="btn btn-success champ">
            </div>
        </form>

        <aside class="corner">
            <span class="eyebrow">Aide à la saisie</span>
            <label for="gene">Filtrer les parents par génération</label>
            <select name="generation" id="gene">
                <option value="0">Toutes les générations</option>
                <?= $gen ?>
            </select>
            <label for="add">Nombre de fiches à préparer</label>
            <input type="number" name="ajout" id="add" min="1" max="10" value="1">
            <label for="filtr">
                <input type="checkbox" name="filtreur" id="filtr" checked>
                Appliquer le parent sélectionné aux nouvelles fiches
            </label>
        </aside>
    </div>
</div>

<script>
    function ajax(url, async = false) {
        const xhr = new XMLHttpRequest()
        xhr.onload = function () {
            afficher(this.responseText)
        }
        xhr.open('GET', url, async)
        xhr.send()
    }

    function afficher(params) {
        const check = document.getElementById('filtr').checked
        if (check) {
            const forms = document.querySelectorAll('form')
            forms.forEach(element => {
                const select = element.querySelector('select')
                if (select) select.innerHTML = params
            })
        }
        const parent = document.getElementById('parent')
        parent.innerHTML = params
    }

    const gene = document.getElementById('gene')
    gene.addEventListener('change', function () {
        ajax('?p=api/options/' + encodeURIComponent(this.value))
    })
</script>
