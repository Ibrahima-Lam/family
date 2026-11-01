<?php
$graphJson = json_encode(
    $graph ?? ['root' => null, 'rootName' => '', 'rootSurname' => '', 'nodes' => [], 'edges' => []],
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
$views = [
    'standard' => 'Standard',
    '2d' => '2D',
    '3d' => '3D',
];
?>

<div class="tree-display-head">
    <div class="tree-view-switch" role="group" aria-label="Affichage de l’arbre">
        <?php foreach ($views as $view => $label): ?>
            <button type="button" data-tree-view-button="<?= e($view) ?>" class="<?= $view === 'standard' ? 'is-active' : '' ?>" aria-pressed="<?= $view === 'standard' ? 'true' : 'false' ?>">
                <?= e($label) ?>
            </button>
        <?php endforeach ?>
    </div>
    <p class="tree-hint" data-graph-hint hidden>Cliquez sur une fiche pour l’ouvrir. Molette : zoom · glisser : déplacer.</p>
</div>

<div class="tree-graph-toolbar" data-graph-toolbar hidden>
    <span class="badge badge-muted" data-graph-caption>Vue graphique</span>
    <div class="tree-graph-actions">
        <button type="button" class="btn btn-default btn-sm" data-tree-zoom="out" aria-label="Réduire">−</button>
        <button type="button" class="btn btn-default btn-sm" data-tree-zoom="reset">Recentrer</button>
        <button type="button" class="btn btn-default btn-sm" data-tree-zoom="in" aria-label="Agrandir">+</button>
        <button type="button" class="btn btn-default btn-sm" data-tree-rotate hidden aria-pressed="false">Rotation auto</button>
    </div>
</div>

<section class="tree-view is-active" data-tree-view-panel="standard" aria-label="Arbre standard">
    <div class="tree-toolbar">
        <button type="button" class="btn btn-default btn-sm" data-tree-action="expand">Tout déplier</button>
        <button type="button" class="btn btn-default btn-sm" data-tree-action="collapse">Tout plier</button>
    </div>

    <div class="person-tree">
        <?= $tree ?>
    </div>
</section>

<section class="tree-view" data-tree-view-panel="2d" aria-label="Arbre en 2D" hidden>
    <div class="tree-graph" data-tree-graph="2d">
        <svg class="tree-svg" role="group" aria-label="Arbre généalogique interactif en deux dimensions"></svg>
    </div>
</section>

<section class="tree-view" data-tree-view-panel="3d" aria-label="Arbre en 3D" hidden>
    <div class="tree-graph tree-graph-3d" data-tree-graph="3d">
        <canvas class="tree-canvas" aria-label="Arbre généalogique interactif en trois dimensions"></canvas>
        <div class="tree-tooltip" data-tree-tooltip hidden></div>
    </div>
</section>

<script type="application/json" id="tree-data"><?= $graphJson ?></script>
