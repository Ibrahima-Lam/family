<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style1.css">
    <script src="../script0.js" defer></script>
</head>

<body>


    <div class="nav-bar">
        <h3>Famille Lammbe Dialade</h3>
        <div class="slider">
            <span>&#9776;</span>
        </div>
        <div class="nav-link">

            <ul>
                <li><a href="index.html"> home </a></li>
                <li><a href="liste.html"> liste </a></li>
                <li><a href="arbre.html"> Arbre </a></li>
                <li><a href="livre.html"> detail </a></li>
            </ul>
        </div>
    </div>

    <div class="container">



        <?php
        require "../autoloader/autoload.php";
        $db = new Connexion();
        $person = new Altoperson($db);
        $elements = $person->getAllElements();
        /*  $tab = [];
    foreach ($elements as $key => $value) {
        $id = $value->id;
        $tab[$id] = $value;
        $tab[$id]->children = $person->getChildren($id);
    }
    file_put_contents("liste.json", json_encode($tab)); */

        ?>
        <table class="table">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Sexe</th>
                <th>GENERATION</th>
            </tr>
            <?php foreach ($elements as $element) : ?>
                <tr>
                    <td><?= $element->id ?></td>
                    <td data-id="<?= $element->id ?> " class="nom"><a href="#"><?= $element->nom ?></a></td>
                    <td><?= $element->sexe ?></td>
                    <td><?= $element->generation ?></td>
                </tr>
            <?php endforeach ?>
        </table>
        <dialog id="children">
            <h3 class="pseudo"></h3>
            <fieldset>

                <legend>

                    <h4>Les Enfants</h4>
                </legend>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NOM</th>
                            <th>Sexe</th>
                            <th>Generation</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
                <br>
            </fieldset>
            <br>
            <button id="close">Fermer</button>
        </dialog>
        <script>
            const fermer = document.getElementById('close');
            const dialog = document.getElementById('children');
            const fieldset = dialog.querySelector('fieldset')
            const compte = document.createElement("em")


            fermer.addEventListener('click', function(e) {
                dialog.close()
            })

            const noms = document.querySelectorAll('.nom')
            noms.forEach(element => {
                element.addEventListener('click', show)
            })

            async function show(e) {

                e.preventDefault()
                const id = parseInt(this.dataset.id)
                const elements = await fetch("liste.json")
                const res = await elements.json()
                const elemnt = res[id]
                const result = elemnt.children
                const dialog = document.getElementById('children');
                const pseudo = dialog.querySelector('.pseudo')
                pseudo.innerHTML = elemnt.nom
                const table = dialog.querySelector('table')
                const children = table.querySelector('tbody')
                children.innerHTML = ""

                const nombre = result.length
                result.forEach(child => {
                    const tr = document.createElement("tr")
                    const id = document.createElement("td")
                    id.innerHTML = child.id
                    tr.appendChild(id)
                    const nom = document.createElement("td")
                    const a = document.createElement("a")
                    a.href = "#"
                    a.innerHTML = child.nom
                    nom.dataset.id = child.id
                    nom.classList.add("nom")
                    nom.append(a)
                    tr.appendChild(nom)
                    const sexe = document.createElement("td")
                    sexe.innerHTML = child.sexe
                    tr.appendChild(sexe)
                    const generation = document.createElement("td")
                    generation.innerHTML = child.nomGeneration
                    tr.appendChild(generation)
                    children.append(tr)

                });
                compte.innerHTML = nombre + " enfant(s)"
                fieldset.append(compte)
                if (dialog.open == true) dialog.close()
                dialog.showModal()
                const childName = dialog.querySelectorAll('.nom')
                childName.forEach(element => {

                    element.addEventListener('click', show)

                });

            }
        </script>
    </div>
</body>

</html>