<?php
$title = "Details";
?>

<div class="container">
    <div class="row">
        <fieldset>
            <legend>
                <h3> Choisir un Element pour rechercher Ses details</h3>
            </legend>
            <select name="" id="choix">
                <?= $options ?>
            </select>
        </fieldset>
        <h1><?= $data->nomPersonne ?></h1>
        <h2>Les Parents</h2>
        <?= $parentHtml ?>
        <br>

        <h2>Les Freres</h2>
        <?= $freresHtml ?>

        <br>

        <h2>Les Enfants</h2>
        <?= $enfantsHtml ?>

        <script>
            const choix = document.getElementById('choix');
            choix.addEventListener('change', function(e) {
                const value = this.value
                window.location.assign("?p=personne/details/" + value)
            })
        </script>