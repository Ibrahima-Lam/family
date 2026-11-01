<?php
$title = 'Descendances à vérifier';
$data = $data ?? [];
$counts = $counts ?? [];
$statut = $statut ?? 'incomplet';
$descendanceStatuts = $descendanceStatuts ?? [];
?>

<div class="container">
    <div class="page-head">
        <div>
            <span class="eyebrow">Qualité de l’arbre</span>
            <h1>Descendances à vérifier</h1>
            <p>
                Une personne peut avoir des enfants inconnus, manquants ou connus mais pas encore ajoutés.
                Ces statuts permettent de le signaler sans bloquer la navigation.
            </p>
        </div>
        <div class="page-actions">
            <a class="btn btn-default" href="?p=personne">Voir le sommaire</a>
        </div>
    </div>

    <div class="descendance-filter" role="tablist" aria-label="Filtrer par statut de descendance">
        <?php foreach ($descendanceStatuts as $statutOption): ?>
            <?php $total = (int) ($counts[$statutOption] ?? 0); ?>
            <a class="btn btn-default btn-sm <?= $statut === $statutOption ? 'is-active' : '' ?>"
               href="?p=personne/incomplets&amp;statut=<?= e($statutOption) ?>"
               role="tab"
               aria-selected="<?= $statut === $statutOption ? 'true' : 'false' ?>">
                <?= e(descendance_label($statutOption)) ?>
                <span class="badge badge-muted"><?= e($total) ?></span>
            </a>
        <?php endforeach ?>
    </div>

    <p class="badge badge-muted"><?= e(descendance_description($statut)) ?></p>

    <?php if ($data === []): ?>
        <div class="panel">
            <p>Aucune personne ne correspond à ce statut.</p>
        </div>
    <?php else: ?>
        <ul class="descendance-list">
            <?php foreach ($data as $value): ?>
                <?php
                $id = (string) ($value->idPersonne ?? '');
                $enfantsCount = (int) ($value->enfantsCount ?? 0);
                $manquants = (int) ($value->enfantsManquants ?? 0);
                $note = trim((string) ($value->enfantsNote ?? ''));
                ?>
                <li>
                    <a class="descendance-person" href="?p=personne/details/<?= e($id) ?>">
                        <?= person_name($value->nomPersonne ?? '', $value->surnom ?? null, true) ?>
                    </a>
                    <span class="badge badge-muted"><?= e($enfantsCount) ?> enfant<?= $enfantsCount > 1 ? 's' : '' ?> ajouté<?= $enfantsCount > 1 ? 's' : '' ?></span>
                    <?= descendance_badge($value->enfantsStatut ?? 'inconnu', $manquants) ?>
                    <?php if ($manquants > 0): ?>
                        <span><?= e($manquants) ?> non ajouté<?= $manquants > 1 ? 's' : '' ?></span>
                    <?php endif ?>
                    <?php if ($note !== ''): ?>
                        <span class="descendance-note"><?= e($note) ?></span>
                    <?php endif ?>
                </li>
            <?php endforeach ?>
        </ul>
    <?php endif ?>
</div>
