<?php
$title = 'Arbre généalogique';
?>

<div class="container">
    <div class="page-head">
        <div>
            <span class="eyebrow">Généalogie</span>
            <h1>Arbre des générations</h1>
            <p>Explorez la descendance de <strong><?= person_name($rootName, $rootSurname) ?></strong> avec l’affichage de votre choix.</p>
        </div>
        <div class="page-actions">
            <a class="btn btn-default" href="?p=personne/livre">Voir le livre</a>
        </div>
    </div>

    <?php require __DIR__ . '/_arbre_views.php'; ?>
</div>
