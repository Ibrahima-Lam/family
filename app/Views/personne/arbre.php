<?php
$title = 'Arbre généalogique';
?>

<div class="container">
    <div class="page-head">
        <div>
            <span class="eyebrow">Généalogie</span>
            <h1>Arbre des générations</h1>
            <p>Explorez la descendance de <strong><?= person_name($rootName, $rootSurname) ?></strong> et dépliez les branches au fil des générations.</p>
        </div>
        <div class="page-actions">
            <a class="btn btn-default" href="?p=personne/livre">Voir le livre</a>
        </div>
    </div>

    <div class="tree-toolbar">
        <button type="button" class="btn btn-default btn-sm" data-tree-action="expand">Tout déplier</button>
        <button type="button" class="btn btn-default btn-sm" data-tree-action="collapse">Tout plier</button>
    </div>

    <div class="person-tree">
        <?= $tree ?>
    </div>
</div>
