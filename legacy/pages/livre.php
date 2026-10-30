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
    <title>Livre</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
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
    </style>
    <script src="script0.js" defer></script>
</head>

<body>
    <page>
        <br>
        <br>
        <h1>Livre Familial (LAMMBE DIALADÈ)</h1>
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
    $index = [];
    ?>
    <h2 id="sommaire">Le Sommaire</h2>
    <table class='striped'>

        <tr>
            <th class="sorters"><data value='id'> ID</data></th>
            <th class="sorters"><data value='nom'> NOM</data></th>
            <th class="sorters"><data value='nomGeneration'> GENERATION</data></th>
            <th class="sorters"><data value='sexe'> SEXE</data></th>
            <th class="sorters"><data value='parent'> PARENT</data></th>
        </tr>


        <?php foreach ($tab as $value) : ?>
            <tr>
                <td><?= $value->idPersonne ?></td>
                <td><a href="#<?= $value->idPersonne ?>"><?= ucwords($value->nomPersonne) ?></a></td>
                <td><?= $value->nomGeneration ?></td>
                <td><?= $value->sexe ?></td>
                <td><a href="#<?= $value->idPersonne ?>"><?= ucwords($value->parent) ?></a></td>
            </tr>
        <?php endforeach ?>

    </table>
    <br>
    <br>
    <br>
    <h2>Les Details</h2>
    <?php foreach ($tab as $val) :
        $id = $val->idPersonne;
        $index[$id] = $val->nomPersonne;
        $person = new Altoperson($conx, $id);

        $children = $person->getChildren($id);

        $idp = $val->idParent;
        $siblings = $person->getSiblings($idp);


        $parents = $person->getParents($idp);

        $backparents = $person->getBackParents($idp)
    ?>
        <fieldset>
            <legend>
                <h2 id="<?= $id ?>"><?= $val->nomPersonne ?></h2>

            </legend>
            <?php if ($backparents) : ?>
                <h3>La lignée de <?= $val->nomPersonne ?></h3>
                <p> ...<?= $backparents ?> <?= htmlentities("<") ?>=<strong><?= $val->nomPersonne ?></strong></p>
                <hr>
            <?php endif ?>
            <?php if ($children) : ?>
                <dl>
                    <dt>
                        <h3>Le(s) Enfant(s) de <?= $val->nomPersonne ?></h3>
                    </dt>
                    <?php foreach ($children as $child) : ?>
                        <dd><a href="#<?= $child["id"] ?>"><?= $child["nom"] ?></a> </dd>
                    <?php endforeach ?>
                </dl>
                <hr>
            <?php endif ?>
            <?php if ($siblings) : ?>
                <dl>

                    <dt>
                        <h3>Les Freres et Soeurs de <?= $val->nomPersonne ?></h3>
                    </dt>
                    <?php foreach ($siblings as $sibling) : ?>
                        <dd><a href="#<?= $sibling["id"] ?>"><?= $sibling["nom"] ?></a> </dd>
                    <?php endforeach ?>
                </dl>
                <hr>
            <?php endif ?>

            <?php if ($parents) : ?>
                <dl>
                    <dt>
                        <h3>Le(s) Parent(s) de <?= $val->nomPersonne ?></h3>
                    </dt>
                    <?php foreach ($parents as $parent) : ?>
                        <dd><a href="#<?= $parent["id"] ?>"><?= $parent["nom"] ?></a> </dd>
                    <?php endforeach ?>
                </dl>
            <?php endif ?>
        </fieldset>
        <a href="#index" class="section">index</a> &nbsp;&nbsp;&nbsp;
        <a href="#sommaire" class="section">sommaire</a>
        <br>
        <br>

    <?php endforeach ?>
    <br>
    <br>
    <br>

    <h2 id="index">Index</h2>
    <table>
        <?php

        asort($index);
        $cles = array_keys($index);
        $size = ceil(count($index) / 3);

        for ($i = 0; $i < $size; $i++) :
            $k1 = $cles[$i];
            $k2 = $cles[$size + $i];
            $k3 = (key_exists(2 * $size + $i, $cles)) ? $cles[2 * $size + $i] : null;
        ?>
            <tr>
                <td>

                    <li><a href="#<?= $k1 ?>"><?= $index[$k1] ?> (<?= $k1 ?>)</a></li>
                </td>
                <?php if (key_exists($k2, $index)) : ?>
                    <td>
                        <li><a href="#<?= $k2 ?>"><?= $index[$k2] ?> (<?= $k2 ?>)</a></li>
                    </td>
                <?php endif ?>
                <?php if (key_exists($k3, $index)) : ?>
                    <td>
                        <li><a href="#<?= $k3 ?>"><?= $index[$k3] ?> (<?= $k3 ?>)</a></li>
                    </td>
                <?php endif ?>
            </tr>
        <?php endfor ?>
    </table>
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
</body>

</html>