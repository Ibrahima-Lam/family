<?php
http_response_code(404);
exit("Page introuvable.");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arbre</title>

    <link rel="stylesheet" href="css/style1.css">
    <script src="js/script0.js" defer></script>
</head>

<body>
    <div class="nav-bar">
        <h3>Famille Lammbe Dialade</h3>
        <div class="slider">
            <span>&#9776;</span>
        </div>
        <div class="nav-link">

            <ul>
                <li><a href="index.html"> Accueil </a></li>
                <li><a href="element.html"> Eléments</a></li>
                <li><a href="liste.html"> Liste </a></li>
                <li><a href="arbre.html"> Arbre </a></li>
                <li><a href="livre.html"> Détails </a></li>
            </ul>
        </div>
    </div>
    <div class="container">

        <div class="content">
            <h2>Arbre</h2>

            <?php require "../autoloader/autoload.php"; ?>


            <?php
            // $user = App::getAuth()->getUserAuth(["admin"]);


            ?>
            <?php if (isset($_GET["imprim"])) : ?>
                <?php if ($_GET["imprim"] == true) : ?>
                    <script>
                        window.print()
                    </script>
                <?php endif ?>
            <?php endif ?>

            <?php
            $conx = new Connexion();
            $prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent,e.idParent as idParent";
            $res = $conx->pdo->query("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
    left join personne d on(e.idParent=d.idPersonne)");
            $res->setFetchMode(PDO::FETCH_CLASS, "Personne");
            $tab = $res->fetchAll();
            $person = new Altoperson($conx, 45);

            ?>



            <div id="param">
                <button id="plie">Plier tous</button>
                <button id="deplie">Deplier tous</button>
            </div>
            <div class="arbre-parent">
                <div class="arbre">
                    <dl>

                        <?= $person->getDescent(45) ?>
                    </dl>
                </div>
            </div>

            <br>
            <br>
            <br>
            <h2>Les Details</h2>
            <?php foreach ($tab as $val) :
                $id = $val->idPersonne;
                $idp = $val->idParent;

                $person = new Altoperson($conx, $id);

                $backparents = $person->getBackParents($idp);



            ?>
                <div class="detail-element">
                    <p> <strong>...</strong><?= $backparents ?> <?= htmlentities("<") ?>=<strong id="<?= $id ?>"><?= $val->nomPersonne ?></strong></p>
                </div>
                <hr>

            <?php endforeach ?>
            <br>
            <br>
            <br>
            <br>
            <table>
                <tr>
                    <td>
                        <dl>
                            <dd><span>Auteur:</span><strong>Dialadé Lam</strong> </dd>
                            <dd><span>Adresse:</span>
                                <address>NKTT, Thialgou (Boghé)</address>
                            </dd>
                            <dd><span>Tel:</span><strong>46507096</strong></dd>
                            <dd><span>WhatsAPP:</span><strong>+22248453730</strong></dd>
                        </dl>
                    </td>
                    <td>
                        <dl>
                            <dd><span>Editeur:</span><strong>Ibrahima Abou Lam (Amadou Dialadé Lam)</strong> </dd>
                            <dd><span>Adresse:</span>
                                <address>NKTT, Thialgou (Boghé)</address>
                            </dd>
                            <dd><span>Email:</span>
                                <address>ibrahimaaboulam@gmail.com</address>
                            </dd>
                            <dd><span>Tel:</span><strong>41916418/41022382</strong></dd>
                            <dd><span>WhatsAPP:</span><strong>+22241916418</strong></dd>
                        </dl>
                    </td>
                </tr>
            </table>
            <script>
                const plie = document.getElementById('plie');
                plie.addEventListener('click', function(e) {
                    const details = document.querySelectorAll('details')
                    details.forEach(element => {
                        element.open = false
                    });
                })

                const deplie = document.getElementById('deplie');
                deplie.addEventListener('click', function(e) {
                    const details = document.querySelectorAll('details')
                    details.forEach(element => {
                        element.open = true
                    });
                })
            </script>
        </div>
    </div>

</body>

</html>