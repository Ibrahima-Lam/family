<?php
$title = "Livre";

use Src\Html;
use Src\Personnage;
?>
<div class="container">
    <div class="row page">
        <h1>Sommaire</h1>
        <table class='striped'>
            <thead>
                <tr>
                    <th class="sorters"><data value='id'> ID</data></th>
                    <th class="sorters"><data value='nom'> NOM</data></th>
                    <th class="sorters"><data value='nomGeneration'> GENERATION</data></th>
                    <th class="sorters"><data value='sexe'> SEXE</data></th>
                    <th class="sorters"><data value='parent'> PARENT</data></th>
                </tr>
            </thead>
            <tbody id="contains">
                <?php foreach ($tab as $value): ?>
                    <tr>
                        <td><?= e($value->idPersonne) ?></td>
                        <td><a href="#<?= e($value->idPersonne) ?>"><?= e(ucwords($value->nomPersonne ?? '')) ?></a></td>
                        <td><?= e($value->nomGeneration) ?></td>
                        <td><?= e($value->sexe) ?></td>
                        <td><a href="#<?= e($value->idParent) ?>"><?= e(ucwords($value->parent ?? '')) ?></a></td>
                    </tr>

                <?php endforeach ?>

            </tbody>
        </table>
    </div>
    <?php foreach ($tab as $value) : ?>
        <div>
            <div class="row elements">
                <h2 id="<?= e($value->idPersonne) ?>"><?= e(ucwords($value->nomPersonne ?? '')) ?></h2>
                <?php
                $id = $value->idPersonne;
                $element = new Personnage($id);
                Html::$target = $id;
                ?>

                <fieldset>
                    <legend>
                        <h3>Le Parent</h3>
                    </legend>

                    <?= Html::table([$element->parent], true) ?>
                </fieldset>
                <fieldset>
                    <legend>
                        <h3>les freres et soeurs</h3>
                    </legend>

                    <?= Html::table($element->freres, true) ?>
                </fieldset>

                <fieldset>
                    <legend>
                        <h3>Les enfants</h3>
                    </legend>

                    <?= html::table($element->enfants, true) ?>
                </fieldset>
                <!--   <fieldset>
                    <legend>
                        <h3> Autres Details</h3>
                    </legend>
                    <div class='content'>
                       
                    </div>
                </fieldset> -->

            </div>
        </div>
    <?php endforeach ?>

</div>