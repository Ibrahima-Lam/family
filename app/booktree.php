<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livre</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            min-width: 1000px;
            overflow-x: auto;

        }


        a {
            text-decoration: none;
        }

        a:visited {
            color: blue;
        }

        .striped {
            border-collapse: collapse;



        }



        .striped tr:nth-child(odd) {
            background-color: gainsboro;
        }

        .striped th,
        .striped td {
            padding: 10px;
        }

        fieldset {
            background-color: rgba(250, 235, 215, 0.2);
            border-radius: 0 50px 0 0;

        }

        .section {
            text-decoration: underline;
            font-weight: bolder;
        }

        .arbre {
            background-color: beige;
        }

        .arbre dd {

            font-weight: bold;
            font-size: large;
        }

        dl[class^="G"] {
            border: 1px solid blue;
            /* background-color: blue; */
        }


        dl[class$="5"],
        dl[class$="0"] {
            border: 1px solid green;
            /* background-color: green; */
        }

        dl[class$="6"],
        dl[class$="1"] {
            border: 1px solid red;
            /* background-color: red; */
        }

        dl[class$="7"],
        dl[class$="2"] {
            border: 1px solid black;
            /* background-color: yellow; */
        }

        dl[class$="9"],
        dl[class$="4"] {
            border: 1px solid purple;
            /* background-color: purple; */
        }

        dd {
            cursor: pointer;
        }

        .attente {
            background-color: bisque;
        }
    </style>
    <script src="script0.js" defer></script>
</head>

<body>
    <page>
        <br>
        <br>
        <h1>Livre Familial (LAMMBE DIALADÈ)</h1>
        <blockquote>edition2</blockquote>
        <br>
        <br>
        <br>
    </page>

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


    <h2>Arbre</h2>
    <div id="param">
        <button id="plie">Plier tous</button>
        <button id="deplie">Deplier tous</button>
    </div>

    <div class="arbre">
        <dl>

            <?= $person->getDescent(45) ?>
        </dl>
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

        <p> <strong>...</strong><?= $backparents ?> <?= htmlentities("<") ?>=<strong id="<?= $id ?>"><?= $val->nomPersonne ?></strong></p>
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
</body>

</html>