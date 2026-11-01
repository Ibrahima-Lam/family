<?php
$title = 'Gérer les personnes';
?>

<script src="assets/js/table.js" defer></script>
<script src="assets/js/editer.js" defer></script>

<div class="container">
    <div class="page-head">
        <div>
            <span class="eyebrow">Administration</span>
            <h1>Gérer les personnes</h1>
            <p>Modifiez une fiche ou retirez une personne de l’arbre. Les changements sont appliqués immédiatement.</p>
        </div>
        <div class="page-actions">
            <a class="btn btn-success" href="?p=personne/ajout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Ajouter une personne
            </a>
        </div>
    </div>

    <div class="row">
        <div class="table-wrap">
            <table class="striped">
                <thead>
                    <tr>
                        <th class="sorters"><data value="idPersonne">ID</data></th>
                        <th class="sorters"><data value="nomPersonne">Nom</data></th>
                        <th class="sorters"><data value="nomGeneration">Génération</data></th>
                        <th class="sorters"><data value="sexe">Sexe</data></th>
                        <th class="sorters"><data value="parent">Parent</data></th>
                        <th>Modifier</th>
                        <th>Supprimer</th>
                    </tr>
                </thead>
                <tbody id="contains">
                    <?php foreach ($data as $value): ?>
                        <tr>
                            <td><span class="badge badge-muted">#<?= e($value->idPersonne) ?></span></td>
                            <td>
                                <a href="?p=personne/details/<?= e($value->idPersonne) ?>">
                                    <?= person_name($value->nomPersonne ?? '', $value->surnom ?? null, true) ?>
                                </a>
                            </td>
                            <td><span class="badge badge-gold"><?= e($value->nomGeneration) ?></span></td>
                            <td>
                                <?php if (($value->sexe ?? '') === 'M'): ?>
                                    <span class="badge badge-green">Homme</span>
                                <?php elseif (($value->sexe ?? '') === 'F'): ?>
                                    <span class="badge badge-clay">Femme</span>
                                <?php else: ?>
                                    <span class="badge badge-muted">Inconnu</span>
                                <?php endif ?>
                            </td>
                            <td>
                                <?php if (!empty($value->idParent)): ?>
                                    <a href="?p=personne/details/<?= e($value->idParent) ?>"><?= person_name($value->parent ?? '', $value->parentSurnom ?? null, true) ?></a>
                                <?php else: ?>
                                    <span class="badge badge-muted">Racine</span>
                                <?php endif ?>
                            </td>
                            <?php if (!empty($value->idParent)): ?>
                                <td id="<?= e($value->idPersonne) ?>" data-i="<?= e($value->idPersonne) ?>" data-p="<?= e($value->idParent) ?>" title="Modifier" class="edite" onclick="editer(this)">
                                    <a href="#" aria-label="Modifier <?= e($value->nomPersonne ?? '') ?>">
                                        <img src="assets/icons/pencil.svg" width="15" height="15" alt="">
                                    </a>
                                </td>
                            <?php else: ?>
                                <td><span class="badge badge-muted">Racine</span></td>
                            <?php endif ?>
                            <td id="<?= e($value->idPersonne) ?>" title="Supprimer" class="delete" onclick="supprimer(this)">
                                <a href="#" aria-label="Supprimer <?= e($value->nomPersonne ?? '') ?>">
                                    <img src="assets/icons/basket.svg" width="15" height="15" alt="">
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<dialog id="dialog" class="dialog">
    <div class="dialog-head">
        <strong>Modifier la fiche</strong>
        <button type="button" class="icon-button" id="close" aria-label="Fermer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
    </div>
    <div class="dialog-body"></div>
</dialog>
