<?php
$title = 'Liens';
$hasSelection = !empty($id1) && !empty($id2);
?>

<div class="container">
    <div class="page-head">
        <div>
            <span class="eyebrow">Parenté</span>
            <h1>Les liens</h1>
            <p>Choisissez deux personnes pour découvrir le lien exact qui les unit.</p>
        </div>
    </div>

    <div class="panel">
        <form action="?p=personne/lien" method="post" class="form">
            <?= csrf_field() ?>
            <div class="details-grid">
                <div class="control">
                    <label for="id1">Première personne</label>
                    <select name="id1" id="id1">
                        <?= $options1 ?>
                    </select>
                </div>
                <div class="control">
                    <label for="id2">Deuxième personne</label>
                    <select name="id2" id="id2">
                        <?= $options2 ?>
                    </select>
                </div>
            </div>
            <div class="action">
                <button type="submit" name="envoi" class="btn btn-success">Calculer le lien</button>
            </div>
        </form>
    </div>

    <?php if ($hasSelection): ?>
        <div class="details-grid">
            <section class="panel">
                <h2>Le lien</h2>
                <div class="link-result">
                    <p><strong><?= person_name($element1->nomPersonne ?? '', $element1->surnom ?? null, true) ?></strong> est le/la <mark><?= e($links->getDifference($id1)) ?></mark> de <strong><?= person_name($element2->nomPersonne ?? '', $element2->surnom ?? null, true) ?></strong>.</p>
                    <p><strong><?= person_name($element2->nomPersonne ?? '', $element2->surnom ?? null, true) ?></strong> est le/la <mark><?= e($links->getDifference($id2)) ?></mark> de <strong><?= person_name($element1->nomPersonne ?? '', $element1->surnom ?? null, true) ?></strong>.</p>
                </div>
            </section>

            <section class="panel">
                <h2>La liaison</h2>
                <div class="link-result">
                    <?php if ($links->isDirect()): ?>
                        <?php $child = $links->getChild(); ?>
                        <div class="branch">
                            <?php foreach ($parents1 as $key => $value): ?>
                                <strong><?= person_name($value->nomPersonne ?? '', $value->surnom ?? null, true) ?></strong>
                                <span>→</span>
                            <?php endforeach ?>
                            <strong><?= person_name($child->nomPersonne ?? '', $child->surnom ?? null, true) ?></strong>
                        </div>
                    <?php else: ?>
                        <div class="branch">
                            <?php foreach ($parents1 as $key => $value): ?>
                                <strong><?= person_name($value->nomPersonne ?? '', $value->surnom ?? null, true) ?></strong>
                                <span>→</span>
                            <?php endforeach ?>
                            <strong><?= person_name($element1->nomPersonne ?? '', $element1->surnom ?? null, true) ?></strong>
                        </div>
                        <div class="branch">
                            <?php foreach ($parents2 as $key => $value): ?>
                                <strong><?= person_name($value->nomPersonne ?? '', $value->surnom ?? null, true) ?></strong>
                                <span>→</span>
                            <?php endforeach ?>
                            <strong><?= person_name($element2->nomPersonne ?? '', $element2->surnom ?? null, true) ?></strong>
                        </div>
                    <?php endif ?>
                </div>
            </section>
        </div>
    <?php else: ?>
        <div class="panel">
            <p class="badge badge-muted">Sélectionnez deux personnes pour afficher leur lien de parenté.</p>
        </div>
    <?php endif ?>
</div>
