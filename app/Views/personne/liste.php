<?php
$title = 'Sommaire';
$tab = $data ?? [];
?>

<div class="container">
    <div class="page-head">
        <div>
            <span class="eyebrow">Répertoire familial</span>
            <h1>Sommaire</h1>
            <p><?= e(count($tab)) ?> personne<?= count($tab) > 1 ? 's' : '' ?> recensée<?= count($tab) > 1 ? 's' : '' ?> dans l’histoire de la famille.</p>
        </div>
        <div class="page-actions">
            <?php if ($sortAZ): ?>
                <a class="btn btn-default" href="?p=personne" aria-pressed="true">Ordre habituel</a>
            <?php else: ?>
                <a class="btn btn-default" href="?p=personne&amp;tri=az" aria-pressed="false">Trier A–Z</a>
            <?php endif ?>
            <?php if (\App\Security\Auth::canEdit()): ?>
                <a class="btn btn-success" href="?p=personne/ajout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Ajouter une personne
                </a>
            <?php endif ?>
        </div>
    </div>

    <div class="row">
        <div class="table-wrap">
            <table class="striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Génération</th>
                        <th>Sexe</th>
                        <th>Descendance</th>
                        <th>Parent</th>
                    </tr>
                </thead>
                <tbody id="contains">
                    <?php foreach ($tab as $value): ?>
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
                                <?php $enfantsCount = (int) ($value->enfantsCount ?? 0); ?>
                                <span class="badge badge-muted"><?= e($enfantsCount) ?> enfant<?= $enfantsCount > 1 ? 's' : '' ?></span>
                                <?= descendance_badge($value->enfantsStatut ?? 'inconnu', $value->enfantsManquants ?? 0) ?>
                            </td>
                            <td>
                                <?php if (!empty($value->idParent)): ?>
                                    <a href="?p=personne/details/<?= e($value->idParent) ?>"><?= person_name($value->parent ?? '', $value->parentSurnom ?? null, true) ?></a>
                                <?php else: ?>
                                    <span class="badge badge-muted">Racine</span>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
