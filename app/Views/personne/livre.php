<?php
$title = 'Livre';

use Src\Html;
use Src\Personnage;
?>

<div class="container">
    <div class="page-head">
        <div>
            <span class="eyebrow">Livre de famille</span>
            <h1>Sommaire</h1>
            <p>Une lecture généalogique complète, pensée pour parcourir les branches et imprimer la mémoire familiale.</p>
        </div>
        <div class="page-actions">
            <a class="btn btn-default" href="javascript:window.print()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>
                Imprimer
            </a>
        </div>
    </div>

    <div class="row page">
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
                            <td><a href="#<?= e($value->idPersonne) ?>"><?= person_name($value->nomPersonne ?? '', $value->surnom ?? null, true) ?></a></td>
                            <td><span class="badge badge-gold"><?= e($value->nomGeneration) ?></span></td>
                            <td><?= ($value->sexe ?? '') === 'M' ? 'Homme' : (($value->sexe ?? '') === 'F' ? 'Femme' : 'Inconnu') ?></td>
                            <td>
                                <?php $enfantsCount = (int) ($value->enfantsCount ?? 0); ?>
                                <span class="badge badge-muted"><?= e($enfantsCount) ?> enfant<?= $enfantsCount > 1 ? 's' : '' ?></span>
                                <?= descendance_badge($value->enfantsStatut ?? 'inconnu', $value->enfantsManquants ?? 0) ?>
                            </td>
                            <td>
                                <?php if (!empty($value->idParent)): ?>
                                    <a href="#<?= e($value->idParent) ?>"><?= person_name($value->parent ?? '', $value->parentSurnom ?? null, true) ?></a>
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

    <?php foreach ($tab as $value): ?>
        <div class="row elements">
            <span class="eyebrow">Génération <?= e($value->nomGeneration) ?></span>
            <h2 id="<?= e($value->idPersonne) ?>"><?= person_name($value->nomPersonne ?? '', $value->surnom ?? null, true) ?></h2>

            <?php
            $livreEnfants = (int) ($value->enfantsCount ?? 0);
            $livreManquants = (int) ($value->enfantsManquants ?? 0);
            ?>
            <div class="descendance-summary">
                <?= descendance_badge($value->enfantsStatut ?? 'inconnu', $livreManquants) ?>
                <p>
                    <?= e($livreEnfants) ?> enfant<?= $livreEnfants > 1 ? 's' : '' ?> ajouté<?= $livreEnfants > 1 ? 's' : '' ?>.
                    <?= e(descendance_description($value->enfantsStatut ?? 'inconnu')) ?>
                </p>
            </div>
            <?php if (trim((string) ($value->enfantsNote ?? '')) !== ''): ?>
                <p class="descendance-note"><?= e($value->enfantsNote) ?></p>
            <?php endif ?>
            <?php
            $id = $value->idPersonne;
            $lineage = $lineages[(string) $id] ?? '';
            $element = new Personnage($id);
            Html::$target = $id;
            ?>

            <?php if ($lineage !== ''): ?>
                <section class="lineage-card">
                    <h3 class="lineage-title">La lignée de <span class="lineage-person"><?= person_name($value->nomPersonne ?? '', $value->surnom ?? null, true) ?></span></h3>
                    <?= $lineage ?>
                </section>
            <?php endif ?>

            <div class="details-grid">
                <section class="panel">
                    <h3>Le parent</h3>
                    <?= Html::table($element->parent ? [$element->parent] : [], true) ?>
                </section>

                <section class="panel">
                    <h3>Frères et sœurs</h3>
                    <?= Html::table($element->freres, true) ?>
                </section>

                <section class="panel">
                    <h3>Enfants</h3>
                    <?= Html::table($element->enfants, true) ?>
                </section>
            </div>
        </div>
    <?php endforeach ?>
</div>
