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

            <?php
            $descStatut = $descendance['statut'] ?? 'inconnu';
            $descManquants = (int) ($descendance['manquants'] ?? 0);
            $descNote = $descendance['note'] ?? null;
            ?>

            <div class="descendance-summary">
                <?= descendance_badge($descStatut, $descManquants) ?>
                <p>
                    <?= e($childrenCount) ?> enfant<?= $childrenCount > 1 ? 's' : '' ?> ajouté<?= $childrenCount > 1 ? 's' : '' ?>.
                    <?= e(descendance_description($descStatut)) ?>
                    <?php if ($descStatut === 'incomplet' && $descManquants > 0): ?>
                        <strong><?= e($descManquants) ?> enfant<?= $descManquants > 1 ? 's' : '' ?> connu<?= $descManquants > 1 ? 's' : '' ?> non ajouté<?= $descManquants > 1 ? 's' : '' ?>.</strong>
                    <?php endif ?>
                </p>
            </div>

            <?php if ($descNote !== null): ?>
                <p class="descendance-note"><?= e($descNote) ?></p>
            <?php endif ?>

            <?= $enfantsHtml ?>

            <?php if (\App\Security\Auth::canEdit()): ?>
                <form action="?p=personne/descendance" method="post" class="descendance-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e($data->idPersonne ?? '') ?>">
                    <h3>Complétude de la descendance</h3>

                    <div class="control">
                        <label for="enfants-statut">Statut</label>
                        <select name="statut" id="enfants-statut" class="field">
                            <?php foreach ($descendanceStatuts as $statutOption): ?>
                                <option value="<?= e($statutOption) ?>" <?= $descStatut === $statutOption ? 'selected' : '' ?>>
                                    <?= e(descendance_label($statutOption)) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="control">
                        <label for="enfants-manquants">Enfants connus non ajoutés</label>
                        <input type="number" name="manquants" id="enfants-manquants" class="field" min="0" max="999" value="<?= e($descManquants) ?>">
                    </div>

                    <div class="control">
                        <label for="enfants-note">Note <span class="badge badge-muted">facultatif</span></label>
                        <input type="text" name="note" id="enfants-note" class="field" maxlength="1000" value="<?= e($descNote ?? '') ?>" placeholder="Ex. deux filles citées dans le récit familial, à ajouter">
                    </div>

                    <div class="action">
                        <button type="submit" class="btn btn-success">Enregistrer la descendance</button>
                    </div>
                </form>
            <?php endif ?>
        </section>
    </div>
</div>

<script>
    const choix = document.getElementById('choix');
    choix.addEventListener('change', function () {
        window.location.assign('?p=personne/details/' + encodeURIComponent(this.value));
    });
</script>
