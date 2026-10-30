<?php
http_response_code(404);
exit("Page introuvable.");
?>
<?php ob_start(); ?>
<?php require "../autoloader/autoload.php"; ?>




<?php
$conx = new Connexion();
$first = $conx->firstId();
$id = $_GET["id"] ?? $first;
$element = new Personnage($conx, $id);
$tab = $element->element;

?>

<div class="container">

    <div class="row">
        <fieldset>
            <legend>
                <h3> Choisir un Element pour rechercher Ses details</h3>
            </legend>
            <select name="" id="choix">
                <?= Html::option($conx, "select * from personne", "idPersonne", "nomPersonne", $id) ?>
            </select>
        </fieldset>
        <h1><?= $tab->nomPersonne ?></h1>

        <h2>Les Parents</h2>
        <br>
        <?php
        Html::$target = $id;
        echo Html::table($element->parent);
        ?>

        <br>

        <h2>Les freres et Soeurs</h2>
        <br>
        <?php
        echo Html::table($element->freres);
        ?>

        <br>

        <h2>Les Enfants</h2>
        <br>
        <?php
        echo  Html::table($element->enfants);
        ?>
    </div>

</div>
<script>
    const choix = document.getElementById('choix');
    choix.addEventListener('change', function(e) {
        const value = this.value
        window.location.assign("personne.php?id=" + value)
    })
</script>
<?php
$title = "Personne";
$content = ob_get_clean();
require "layout.php";
?>