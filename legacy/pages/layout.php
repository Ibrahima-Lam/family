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
    <title><?= $title ?? "sans titre" ?></title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        #menu {
            position: absolute;
            top: 50px;
            left: -300px;
            border-radius: 6px;
            min-width: 160px;
            height: 200px;
            margin: 10px;
            padding: 10px;

        }

        #menu ul {
            display: block;
            align-items: center;
            justify-content: space-around;
            margin: 10px;
            padding: 10px;
            background-color: cadetblue;
            border-radius: 6px;
            height: 100%;
            width: 100%;
        }

        #menu ul li {
            width: 90%;
            padding: 5px;
            margin: 5px;
            border-bottom: 1px dotted white;
        }

        #menu ul li:last-child {
            border-bottom: none;
        }

        .links {
            display: flex;
            align-items: center;


        }
    </style>
    <link rel="stylesheet" href="../public/css/style2.css">
    <link rel="stylesheet" href="../public/css/style3.css">
    <link rel="stylesheet" href="../public/css/style4.css">
    <link rel="stylesheet" href="../public/css/style5.css">
    <script src="../public/js/script.js" defer></script>
    <script src="../public/js/arbre.js" defer></script>
    <script src="script0.js" defer></script>
</head>

<body>
    <div class="nav-bar">
        <ul>
            <li><a href="#" id="menu-bar">Menu
                    <div id="menu">
                        <ul id="menus" class="menu-action">
                            <li><a href="addpersonne.php">Ajouter une personne</a></li>
                            <li><a href="editer.php">editer des Personne</a></li>
                            <li><a href="livre.php?imprim=true">Imprimer le livre</a></li>
                            <li><a href="booktree.php?imprim=true">Imprimer le livre version arbre</a></li>
                        </ul>
                    </div>
                </a></li>
            <li><a href="index.php">Accueil</a></li>
            <li><input type="search" id="srch" placeholder='Recherche..'></li>
        </ul>
        <div class="links">

            <ul id="link">
                <li><a href="sommaire.php">Sommaire</a></li>
                <li><a href="Personne.php">Personne</a></li>
                <li><a href="arbre.php">Arbre</a></li>
                <li><a href="link.php">Lien</a></li>
                <li><a href="book.php">Book</a></li>
            </ul>
        </div>
        <div class="toggle" id="list-link">

            <ul class="toggle-list">
                <li><a href="#"><img src="../public/icons/three-dots-vertical.svg"></a></li>
            </ul>
        </div>
    </div>
    <?= $content ?>
    <template id="template">
        <img src="../public/icons/triangle-fill.svg" id="triangle">
    </template>

</body>

</html>