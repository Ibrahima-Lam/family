<?php
$title = "Sommaire";
$tab = $data ?? [];
?>



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
                        <td><?= e($value->idPersonne) ?></td>
                        <td><a href="?p=personne/details/<?= e($value->idPersonne) ?>"><?= e(ucwords($value->nomPersonne ?? '')) ?></a></td>
                        <td><?= e($value->nomGeneration) ?></td>
                        <td><?= e($value->sexe) ?></td>
                        <td><a href="?p=personne/details/<?= e($value->idPersonne) ?>"><?= e(ucwords($value->parent ?? '')) ?></a></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
