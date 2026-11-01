<?php
$title = 'Détails';
$nom = ucwords((string) ($data->nomPersonne ?? ''));
$sexe = (string) ($data->sexe ?? '');
$generation = (string) ($data->nomGeneration ?? '');
$parent = (string) ($data->parent ?? '');
?>

<div class="container">
    <div class="detail-hero">
        <div>
            <span class="eyebrow">Fiche personne</span>
            <h1><?= person_name($data->nomPersonne ?? '', $data->surnom ?? null, true) ?></h1>
            <p>
                <?php if ($generation !== ''): ?>
                    <?= e($generation) ?> génération
                <?php endif ?>
                <?php if ($sexe !== ''): ?>
                    · <?= $sexe === 'M' ? 'Homme' : ($sexe === 'F' ? 'Femme' : 'Genre inconnu') ?>
                <?php endif ?>
                <?php if ($parent !== ''): ?>
                    · enfant de <?= person_name($data->parent ?? '', $data->parentSurnom ?? null, true) ?>
                <?php endif ?>
            </p>
        </div>
        <span class="avatar" aria-hidden="true"><?= e(mb_substr($nom !== '' ? $nom : '?', 0, 1)) ?></span>
    </div>

    <div class="panel">
        <h3>Choisir une personne</h3>
        <select id="choix" aria-label="Choisir une personne">
            <?= $options ?>
        </select>
    </div>

    <div class="details-grid">
        <section class="panel">
            <h2>Parents</h2>
            <?= $parentHtml ?>
        </section>

        <section class="panel">
            <h2>Frères et sœurs</h2>
            <?= $freresHtml ?>
        </section>

        <section class="panel">
            <h2>Enfants</h2>
            <?= $enfantsHtml ?>
        </section>
    </div>
</div>

<script>
    const choix = document.getElementById('choix');
    choix.addEventListener('change', function () {
        window.location.assign('?p=personne/details/' + encodeURIComponent(this.value));
    });
</script>
