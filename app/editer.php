<?php
http_response_code(404);
exit("Page introuvable.");
?>
<?php ob_start(); ?>
<?php require "../autoloader/autoload.php"; ?>

<script src="../public/js/table.js" defer></script>
<script src="../public/js/editer.js" defer></script>


<?php
$conx = new Connexion();
$prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent";
$res = $conx->pdo->query("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
    left join personne d on(e.idParent=d.idPersonne) ");
$res->setFetchMode(PDO::FETCH_CLASS, "Personne");
$tab = $res->fetchAll();
$req = "select * from supplement";

?>

<div class="container">
    <div class="row">
        <table class='striped'>
            <thead>
                <tr>
                    <th class="sorters"><data value='id'> ID</data></th>
                    <th class="sorters"><data value='nom'> NOM</data></th>
                    <th class="sorters"><data value='nomGeneration'> GENERATION</data></th>
                    <th class="sorters"><data value='sexe'> SEXE</data></th>
                    <th class="sorters"><data value='parent'> PARENT</data></th>
                    <th><data value='id'> Editer</data></th>
                    <th><data value='id'>Supprimer</data></th>
                </tr>
            </thead>
            <tbody id="contains">
                <?php foreach ($tab as $value) : ?>
                    <tr>
                        <td><?= $value->idPersonne ?></td>
                        <td><a href="personne.php?id=<?= $value->idPersonne ?>"><?= ucwords($value->nomPersonne) ?></a></td>
                        <td><?= $value->nomGeneration ?></td>
                        <td><?= $value->sexe ?></td>
                        <td><a href="personne.php?id=<?= $value->idPersonne ?>"><?= ucwords($value->parent) ?></a></td>
                        <td id="<?= $value->idPersonne ?>" title="Editer" class="edite" onclick="editer(this)"><a href="#"><img src="../public/icons/pencil.svg" width="15px"></a></td>
                        <td id="<?= $value->idPersonne ?>" title="Supprimer" class="delete" onclick="supprimer(this)"><a href="#"><img src="../public/icons/basket.svg" width="15px"></a></td>
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
                        <?= html::option($conx, $req, "idSupplement", "nomSupplement") ?>
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

<?php
$title = "Editer";
$content = ob_get_clean();
require "layout.php";
?>