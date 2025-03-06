<?php ob_start(); ?>
<?php require "../autoloader/autoload.php";
$id = $_GET["id"] ?? 1;
?>

<div class="container">
    <div class="row">
        <h2> L'arbre des pedigrés à partir de la <?= $id ?>eme Generation</h2>
        <?php

        $conx = new connexion();
        $tree = new arbre($conx, $id);

        echo $tree->getResult();

        ?>
    </div>

</div>

<?php
$title = "les Pedigres";
$content = ob_get_clean();
require "layout.php";
?>