<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>demo</title>
    <link rel="stylesheet" href="style1.css">
    <link rel="stylesheet" href="style.css">
    <script src="app.js" defer></script>
</head>

<body>
    <div class="container">
        <div class="content">
            <?php
            require "../autoloader/autoload.php";
            $db = new Connexion();
            $person = new Altoperson($db);
            $elements = $person->getAllElements();
            ?>
            <?php foreach ($elements as $element) : ?>
                <?php
                $id = $element->id;
                $idp = $element->idParent;
                $nom = $element->nom;
                $sexe = $element->sexe;
                $generation = $element->generation;
                $children = $person->getChildren($id);
                $siblings = $person->getSiblings($idp);
                $parents = $person->getParents($idp);
                $backparent = $person->getBackParents($idp);
                ?>
                <div class="pseudo-element" data-id="<?= $id ?>">
                    <div class="pseudo-element-head flex">
                        <div class="profile ">
                            <img src="../public/icons/person.svg" alt="profile">
                            <span id="<?= $id ?>" class="element-name"><strong><?= $nom ?></strong></span>
                        </div>
                        <div class="pseudo-menu">
                            <img src="../public/icons/three-dots-vertical.svg" alt="profile">

                        </div>
                    </div>
                    <div class="pseudo-element-body">
                        <dd><strong>ID : </strong><?= $id ?></dd>
                        <dd><strong>Genre : </strong><?= $sexe ?></dd>
                        <dd><strong>Génération : </strong><?= $generation ?></dd>
                        <div class="partie">
                            <h4 class="sub-header">La lignée</h4>
                            <div class="data">
                                <p class="lignee"> ...<?= $backparent ?>&lt;=<?= $nom ?></p>
                            </div>
                        </div>

                        <?php if ($siblings) : ?>
                            <div class="partie">
                                <h4 class="sub-header">Le(s) Frére(s) et Soeur(s)</h4>
                                <?php foreach ($siblings as $sibling) : ?>
                                    <dd><a href="#<?= $sibling["id"] ?>"><?= $sibling["nom"] ?></a></dd>
                                <?php endforeach ?>
                            </div>
                        <?php endif ?>

                        <?php if ($children) : ?>
                            <div class="partie">
                                <h4 class="sub-header">Les Enfants</h4>
                                <?php foreach ($children as $child) : ?>
                                    <dd><a href="#<?= $child["id"] ?>"><?= $child["nom"] ?></a></dd>
                                <?php endforeach ?>
                            </div>
                        <?php endif ?>





                        <?php if ($parents) : ?>
                            <div class="partie">
                                <h4 class="sub-header">Le(s) Parent(s)</h4>
                                <?php foreach ($parents as $parent) : ?>
                                    <dd><a href="#<?= $parent["id"] ?>"><?= $parent["nom"] ?></a></dd>
                                <?php endforeach ?>
                            </div>
                        <?php endif ?>
                    </div>


                    <div class="pseudo-element-foot ">
                        <div class="bookmark" title="bookmark">
                            <img src="../public/icons/bookmark.svg" alt="profile">
                        </div>
                        <div class="copy" title="Copier">
                            <img src="../public/icons/clipboard.svg" alt="profile">
                        </div>
                        <div class="share" title="partager">
                            <img src="../public/icons/share.svg" alt="profile">
                        </div>
                    </div>
                </div>
            <?php endforeach ?>

            <div class="scroller" id="scrollTop">
                <img src="../public/icons/caret-up-fill.svg" alt="profile">
            </div>

            <template id="menu-items">

                <div class="menus">
                    <ul>
                        <li>item1</li>
                        <li>item2</li>
                        <li>item3</li>
                        <li>item4</li>
                        <li>item5</li>
                    </ul>
                </div>

            </template>
        </div>
    </div>
</body>

</html>