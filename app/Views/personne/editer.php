<?php
$title = "Editer";
?>

<script src="assets/js/table.js" defer></script>
<script src="assets/js/editer.js" defer></script>
<div class="container">
    <div class="row">
        <table class='striped'>
            <thead>
                <tr>
                    <th class="sorters"><data value='idPersonne'> ID</data></th>
                    <th class="sorters"><data value='nomPersonne'> NOM</data></th>
                    <th class="sorters"><data value='generation'> GENERATION</data></th>
                    <th class="sorters"><data value='sexe'> SEXE</data></th>
                    <th class="sorters"><data value='parent'> PARENT</data></th>
                    <th><data value='id'> Editer</data></th>
                    <th><data value='id'>Supprimer</data></th>
                </tr>
            </thead>
            <tbody id="contains">
                <?php foreach ($data as $value) : ?>
                    <tr>
                        <td><?= e($value->idPersonne) ?></td>
                        <td><a href="personne.php?id=<?= e($value->idPersonne) ?>"><?= e(ucwords($value->nomPersonne ?? '')) ?></a></td>
                        <td><?= e($value->nomGeneration) ?></td>
                        <td><?= e($value->sexe) ?></td>
                        <td><a href="personne.php?id=<?= e($value->idPersonne) ?>"><?= e(ucwords($value->parent ?? '')) ?></a></td>
                        <?php if ($value->idParent): ?>
                            <td id="<?= e($value->idPersonne) ?>" data-i="<?= e($value->idPersonne) ?>" data-p="<?= e($value->idParent) ?>" title="Editer" class="edite" onclick="editer(this)"><a href="#"><img src="assets/icons/pencil.svg" width="15px"></a></td>
                        <?php else: ?>
                            <td></td>
                        <?php endif ?>
                        <td id="<?= e($value->idPersonne) ?>" title="Supprimer" class="delete" onclick="supprimer(this)"><a href="#"><img src="assets/icons/basket.svg" width="15px"></a></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<template id="edition">
    <div class="formes">
        <button class="btn btn-primary" id="toggler">modifier</button>
        <button class="btn btn-danger" id="close">fermer</button>
        <div class="containes">
            <form action="" method="post" id="sup">

                <div class="frms">
                    <h3 id="demo"></h3>
                    <input type="hidden" name="id" id="num">
                    <label for="">Contenu</label>
                    <input type="text" name="content" required>
                    <label for="">type</label>
                    <select name="type" id="">

                    </select>
                    <div>
                        <input type="reset" value="annuler" class="btn btn-default">
                        <input type="submit" value="envoyer" class="btn btn-success">
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
<div class="fixeur" draggable="true">


</div>
<dialog id="dialog" class='dialog'>
    <div class="dialog-head">
        <strong>Formulaire</strong>
    </div>
    <div class="dialog-body">

    </div>
    <div class="dialog-foot"><button id="close" class="btn btn-danger">Fermer</button></div>
</dialog>