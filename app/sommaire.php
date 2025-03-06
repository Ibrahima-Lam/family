<?php ob_start(); ?>
<?php require "../autoloader/autoload.php"; ?>




<?php
$conx = new Connexion();
$prop = "p.idPersonne,p.nomPersonne ,nomGeneration,p.sexe,d.nomPersonne as parent";
$res = $conx->pdo->query("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
    left join personne d on(e.idParent=d.idPersonne) ");
$res->setFetchMode(PDO::FETCH_CLASS, "Personne");
$tab = $res->fetchAll();

?>
<script src="../public/js/table.js" defer></script>
<div class="container">
    <div class="row">
        <table class='striped'>
            <thead>
                <tr>
                    <th class="sorters"><data value='id'> ID</data></th>
                    <th class="sorters"><data value='nom'> NOM</data></th>
                    <th class="sorters"><data value='idGeneration'> GENERATION</data></th>
                    <th class="sorters"><data value='sexe'> SEXE</data></th>
                    <th class="sorters"><data value='parent'> PARENT</data></th>
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
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$title = "Sommaire";
$content = ob_get_clean();
require "layout.php";
?>