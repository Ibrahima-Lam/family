<?php

namespace App\Support;

use App\Models\Personne;
use Core\Support\Config;
use RuntimeException;
use Src\Arbre;
use Src\Html;

final class StaticSiteGenerator
{
    private Personne $model;
    private string $outputDir;
    private string $publicDir;
    private string $resourcesDir;
    private ?string $rootId;
    private string $appName;

    public function __construct(
        string $outputDir,
        ?string $rootId = null,
        ?string $publicDir = null,
        ?string $resourcesDir = null
    ) {
        $root = dirname(__DIR__, 2);

        $this->model = new Personne();
        $this->outputDir = rtrim($outputDir, '/');
        $this->publicDir = rtrim($publicDir ?? $root . '/public', '/');
        $this->resourcesDir = rtrim($resourcesDir ?? $root . '/resources/static', '/');
        $this->rootId = $rootId;
        $this->appName = (string) Config::get('app.name', 'Famille');
    }

    /** @return list<string> */
    public function generate(): array
    {
        $this->prepareDirectories();
        $people = $this->people();

        $files = [];
        $files[] = $this->write('index.html', $this->layout('Accueil', $this->homeContent($people), '', 'index.html'));
        $files[] = $this->write('liste.html', $this->layout('Sommaire', $this->listContent($people), '', 'liste.html'));
        $files[] = $this->write('arbre.html', $this->layout('Arbre généalogique', $this->treeContent(), '', 'arbre.html'));
        $files[] = $this->write('livre.html', $this->layout('Livre de famille', $this->bookContent($people), '', 'livre.html'));
        $files[] = $this->write(
            'descendances.html',
            $this->layout('Descendances à vérifier', $this->descendanceContent($people), '', 'descendances.html')
        );

        foreach ($people as $person) {
            $id = (string) ($person->idPersonne ?? '');
            if ($id === '') {
                continue;
            }

            $files[] = $this->write(
                'personne/' . $id . '.html',
                $this->layout(
                    'Fiche de ' . (string) ($person->nomPersonne ?? ''),
                    $this->personContent($person),
                    '../',
                    'personne/' . $id . '.html'
                )
            );
        }

        $this->copyAssets();

        return $files;
    }

    private function prepareDirectories(): void
    {
        foreach ([
            $this->outputDir,
            $this->outputDir . '/personne',
            $this->outputDir . '/assets',
            $this->outputDir . '/assets/css',
            $this->outputDir . '/assets/js',
        ] as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new RuntimeException('Impossible de créer le dossier : ' . $directory);
            }
        }
    }

    /** @return list<object> */
    private function people(): array
    {
        $people = $this->model->findAll();
        $people = is_array($people) ? $people : [];

        usort($people, static function (object $a, object $b): int {
            return [
                (int) ($a->generation ?? 0),
                (string) ($a->nomPersonne ?? ''),
                (int) ($a->idPersonne ?? 0),
            ] <=> [
                (int) ($b->generation ?? 0),
                (string) ($b->nomPersonne ?? ''),
                (int) ($b->idPersonne ?? 0),
            ];
        });

        return $people;
    }

    private function homeContent(array $people): string
    {
        $total = count($people);
        $generations = 0;

        foreach ($people as $person) {
            $generations = max($generations, (int) ($person->generation ?? 0));
        }

        $counts = $this->model->countByDescendanceStatut();
        $aCompleter = (int) ($counts[Descendance::INCOMPLET] ?? 0) + (int) ($counts[Descendance::INCONNU] ?? 0);

        return '<section class="hero">'
            . '<span class="eyebrow">Mémoire familiale</span>'
            . '<h1>' . e($this->appName) . ', transmise autrement.</h1>'
            . '<p>Parcourez les générations, retrouvez les liens de parenté et faites vivre l’histoire familiale '
            . 'dans un espace clair, chaleureux et partagé. Ce site est entièrement statique : il peut être publié '
            . 'sur n’importe quel hébergement de fichiers.</p>'
            . '<div class="hero-actions">'
            . '<a class="btn btn-success" href="liste.html">Explorer le sommaire</a>'
            . '<a class="btn btn-ghost" href="arbre.html">Voir l’arbre généalogique</a>'
            . '</div>'
            . '<div class="hero-stats">'
            . '<div class="stat-card"><strong>' . e($total) . '</strong><span>Personnes</span></div>'
            . '<div class="stat-card"><strong>' . e($generations) . '</strong><span>Générations</span></div>'
            . '<a class="stat-card" href="descendances.html"><strong>' . e($aCompleter)
            . '</strong><span>Descendances à vérifier</span></a>'
            . '</div>'
            . '</section>'
            . '<section class="feature-grid">'
            . $this->featureCard('liste.html', 'Sommaire', 'Retrouvez chaque membre de la famille dans une liste claire et consultable.', 'Ouvrir le sommaire')
            . $this->featureCard('arbre.html', 'Arbre', 'Explorez les branches et descendez de génération en génération.', 'Voir l’arbre')
            . $this->featureCard('livre.html', 'Livre', 'Parcourez la mémoire familiale dans une version pensée pour la lecture et l’impression.', 'Ouvrir le livre')
            . $this->featureCard('descendances.html', 'Descendances', 'Repérez les enfants inconnus, manquants ou connus mais pas encore ajoutés.', 'Vérifier les descendances')
            . '</section>';
    }

    private function featureCard(string $url, string $title, string $text, string $link): string
    {
        return '<a class="feature-card" href="' . e($url) . '">'
            . '<span class="feature-icon" aria-hidden="true">◆</span>'
            . '<h3>' . e($title) . '</h3>'
            . '<p>' . e($text) . '</p>'
            . '<span>' . e($link) . '</span>'
            . '</a>';
    }

    private function listContent(array $people): string
    {
        Html::setLinkFormat('personne/', '.html');
        $rows = '';

        foreach ($people as $person) {
            $id = (string) ($person->idPersonne ?? '');
            $enfants = (int) ($person->enfantsCount ?? 0);
            $parentId = (string) ($person->idParent ?? '');

            $rows .= '<tr>'
                . '<td><span class="badge badge-muted">#' . e($id) . '</span></td>'
                . '<td><a href="personne/' . e($id) . '.html">'
                . person_name($person->nomPersonne ?? '', $person->surnom ?? null, true) . '</a></td>'
                . '<td><span class="badge badge-gold">' . e($person->nomGeneration ?? '') . '</span></td>'
                . '<td>' . e($this->sexLabel((string) ($person->sexe ?? ''))) . '</td>'
                . '<td><span class="badge badge-muted">' . e($enfants) . ' enfant' . ($enfants > 1 ? 's' : '')
                . '</span> ' . descendance_badge($person->enfantsStatut ?? 'inconnu', $person->enfantsManquants ?? 0) . '</td>'
                . '<td>' . ($parentId !== ''
                    ? '<a href="personne/' . e($parentId) . '.html">'
                        . person_name($person->parent ?? '', $person->parentSurnom ?? null, true) . '</a>'
                    : '<span class="badge badge-muted">Racine</span>') . '</td>'
                . '</tr>';
        }

        return '<div class="container">'
            . '<div class="page-head"><div><span class="eyebrow">Répertoire familial</span>'
            . '<h1>Sommaire</h1><p>' . e(count($people)) . ' personne' . (count($people) > 1 ? 's' : '')
            . ' recensée' . (count($people) > 1 ? 's' : '') . ' dans l’histoire de la famille.</p></div></div>'
            . '<div class="panel"><label for="static-search">Rechercher une personne</label>'
            . '<input type="search" id="static-search" data-static-search="#liste-personnes tbody tr" '
            . 'data-static-search-empty="#liste-vide" placeholder="Nom, surnom, génération…">'
            . '<p id="liste-vide" class="badge badge-muted" hidden>Aucun résultat.</p></div>'
            . '<div class="row"><div class="table-wrap"><table class="striped" id="liste-personnes"><thead><tr>'
            . '<th>ID</th><th>Nom</th><th>Génération</th><th>Sexe</th><th>Descendance</th><th>Parent</th>'
            . '</tr></thead><tbody>' . $rows . '</tbody></table></div></div>'
            . '</div>';
    }

    private function treeContent(): string
    {
        $arbre = new Arbre($this->rootId);
        $arbre->setDetailsLink('personne/', '.html');
        $rootId = $arbre->getRootId();

        if ($rootId === null) {
            return '<div class="container"><div class="panel"><p>Aucune personne dans l’arbre.</p></div></div>';
        }

        $tree = $arbre->getResult();
        $graph = $arbre->toGraph();

        ob_start();
        require dirname(__DIR__, 2) . '/app/Views/personne/_arbre_views.php';
        $views = (string) ob_get_clean();

        return '<div class="container">'
            . '<div class="page-head"><div><span class="eyebrow">Généalogie</span><h1>Arbre des générations</h1>'
            . '<p>Explorez la descendance de <strong>'
            . person_name($arbre->getRootName(), $arbre->getRootSurname()) . '</strong> avec l’affichage de votre choix.</p>'
            . '</div></div>'
            . $views . '</div>';
    }

    private function bookContent(array $people): string
    {
        Html::setLinkFormat('personne/', '.html');
        $arbre = new Arbre($this->rootId);
        $arbre->setDetailsLink('personne/', '.html');

        $rows = '';
        foreach ($people as $person) {
            $id = (string) ($person->idPersonne ?? '');
            $enfants = (int) ($person->enfantsCount ?? 0);
            $parentId = (string) ($person->idParent ?? '');

            $rows .= '<tr>'
                . '<td><span class="badge badge-muted">#' . e($id) . '</span></td>'
                . '<td><a href="#' . e($id) . '">' . person_name($person->nomPersonne ?? '', $person->surnom ?? null, true) . '</a></td>'
                . '<td><span class="badge badge-gold">' . e($person->nomGeneration ?? '') . '</span></td>'
                . '<td>' . e($this->sexLabel((string) ($person->sexe ?? ''))) . '</td>'
                . '<td><span class="badge badge-muted">' . e($enfants) . ' enfant' . ($enfants > 1 ? 's' : '')
                . '</span> ' . descendance_badge($person->enfantsStatut ?? 'inconnu', $person->enfantsManquants ?? 0) . '</td>'
                . '<td>' . ($parentId !== ''
                    ? '<a href="#' . e($parentId) . '">'
                        . person_name($person->parent ?? '', $person->parentSurnom ?? null, true) . '</a>'
                    : '<span class="badge badge-muted">Racine</span>') . '</td>'
                . '</tr>';
        }

        $sections = '';
        foreach ($people as $person) {
            $id = (string) ($person->idPersonne ?? '');
            $lineage = $arbre->getLineage($id, 'personne/', '.html');
            $parent = !empty($person->idParent) ? $this->model->findOne((string) $person->idParent) : null;
            $siblings = !empty($person->idParent) ? $this->model->findSiblings((string) $person->idParent) : [];
            $children = $this->model->findChildren($id);

            $sections .= '<div class="row elements">'
                . '<span class="eyebrow">Génération ' . e($person->nomGeneration ?? '') . '</span>'
                . '<h2 id="' . e($id) . '">' . person_name($person->nomPersonne ?? '', $person->surnom ?? null, true) . '</h2>'
                . $this->descendanceSummary($person)
                . ($lineage !== ''
                    ? '<section class="lineage-card"><h3 class="lineage-title">La lignée de '
                        . person_name($person->nomPersonne ?? '', $person->surnom ?? null, true) . '</h3>'
                        . $lineage . '</section>'
                    : '')
                . '<div class="details-grid">'
                . '<section class="panel"><h3>Le parent</h3>' . Html::table($parent ? [$parent] : []) . '</section>'
                . '<section class="panel"><h3>Frères et sœurs</h3>' . Html::table($siblings) . '</section>'
                . '<section class="panel"><h3>Enfants</h3>' . Html::table($children) . '</section>'
                . '</div></div>';
        }

        return '<div class="container"><div class="page-head"><div><span class="eyebrow">Livre de famille</span>'
            . '<h1>Sommaire</h1><p>Une lecture généalogique complète, pensée pour parcourir les branches et imprimer '
            . 'la mémoire familiale.</p></div></div>'
            . '<div class="row page"><div class="table-wrap"><table class="striped"><thead><tr>'
            . '<th>ID</th><th>Nom</th><th>Génération</th><th>Sexe</th><th>Descendance</th><th>Parent</th>'
            . '</tr></thead><tbody>' . $rows . '</tbody></table></div></div>'
            . $sections . '</div>';
    }

    private function descendanceContent(array $people): string
    {
        $counts = $this->model->countByDescendanceStatut();
        $cards = '';

        foreach (Descendance::statuses() as $statut) {
            $cards .= '<div class="stat-card"><strong>' . e((int) ($counts[$statut] ?? 0)) . '</strong><span>'
                . e(descendance_label($statut)) . '</span></div>';
        }

        $sections = '';
        foreach (Descendance::statuses() as $statut) {
            $filtered = array_values(array_filter(
                $people,
                static fn (object $person): bool => Descendance::normalize($person->enfantsStatut ?? null) === $statut
            ));

            $items = '';
            foreach ($filtered as $person) {
                $id = (string) ($person->idPersonne ?? '');
                $enfants = (int) ($person->enfantsCount ?? 0);
                $manquants = (int) ($person->enfantsManquants ?? 0);
                $note = trim((string) ($person->enfantsNote ?? ''));

                $items .= '<li><a class="descendance-person" href="personne/' . e($id) . '.html">'
                    . person_name($person->nomPersonne ?? '', $person->surnom ?? null, true) . '</a>'
                    . '<span class="badge badge-muted">' . e($enfants) . ' enfant' . ($enfants > 1 ? 's' : '')
                    . ' ajouté' . ($enfants > 1 ? 's' : '') . '</span>'
                    . descendance_badge($statut, $manquants)
                    . ($manquants > 0 ? '<span>' . e($manquants) . ' non ajouté' . ($manquants > 1 ? 's' : '') . '</span>' : '')
                    . ($note !== '' ? '<span class="descendance-note">' . e($note) . '</span>' : '')
                    . '</li>';
            }

            $sections .= '<section class="panel"><h2>' . e(descendance_label($statut))
                . ' <span class="badge badge-muted">' . e(count($filtered)) . '</span></h2>'
                . '<p>' . e(descendance_description($statut)) . '</p>'
                . ($items !== '' ? '<ul class="descendance-list">' . $items . '</ul>' : '<p class="badge badge-muted">Aucune personne.</p>')
                . '</section>';
        }

        return '<div class="container"><div class="page-head"><div><span class="eyebrow">Qualité de l’arbre</span>'
            . '<h1>Descendances à vérifier</h1><p>Une personne peut avoir des enfants inconnus, manquants ou connus '
            . 'mais pas encore ajoutés. Ces statuts permettent de le signaler sans bloquer la navigation.</p></div></div>'
            . '<div class="descendance-stats">' . $cards . '</div>'
            . $sections . '</div>';
    }

    private function personContent(object $person): string
    {
        Html::setLinkFormat('', '.html');
        $id = (string) ($person->idPersonne ?? '');
        $parent = !empty($person->idParent) ? $this->model->findOne((string) $person->idParent) : null;
        $siblings = !empty($person->idParent) ? $this->model->findSiblings((string) $person->idParent) : [];
        $children = $this->model->findChildren($id);
        $descendance = $this->model->getDescendance($id);
        $sexe = (string) ($person->sexe ?? '');

        $arbre = new Arbre($this->rootId);
        $lineage = $arbre->getLineage($id, '', '.html');

        return '<div class="container"><div class="detail-hero"><div><span class="eyebrow">Fiche personne</span>'
            . '<h1>' . person_name($person->nomPersonne ?? '', $person->surnom ?? null, true) . '</h1>'
            . '<p>' . e($person->nomGeneration ?? '') . ' génération · ' . e($this->sexLabel($sexe))
            . (!empty($person->idParent)
                ? ' · enfant de ' . person_name($person->parent ?? '', $person->parentSurnom ?? null, true)
                : ' · racine de l’arbre')
            . '</p></div><span class="avatar" aria-hidden="true">'
            . e(mb_substr((string) ($person->nomPersonne ?? '?'), 0, 1)) . '</span></div>'
            . '<div class="page-actions" style="margin:18px 0"><a class="btn btn-default" href="../liste.html">Sommaire</a>'
            . '<a class="btn btn-default" href="../arbre.html">Arbre</a></div>'
            . ($lineage !== '' ? '<section class="lineage-card"><h3 class="lineage-title">Sa lignée</h3>'
                . $lineage . '</section>' : '')
            . '<div class="panel"><h2>Descendance</h2>' . $this->descendanceSummary($person, $descendance) . '</div>'
            . '<div class="details-grid">'
            . '<section class="panel"><h2>Parents</h2>' . Html::table($parent ? [$parent] : []) . '</section>'
            . '<section class="panel"><h2>Frères et sœurs</h2>' . Html::table($siblings) . '</section>'
            . '<section class="panel"><h2>Enfants</h2>' . Html::table($children) . '</section>'
            . '</div></div>';
    }

    private function descendanceSummary(object $person, ?array $descendance = null): string
    {
        $statut = $descendance['statut'] ?? Descendance::normalize($person->enfantsStatut ?? null);
        $manquants = (int) ($descendance['manquants'] ?? $person->enfantsManquants ?? 0);
        $note = $descendance['note'] ?? (trim((string) ($person->enfantsNote ?? '')) ?: null);
        $enfants = (int) ($person->enfantsCount ?? 0);

        return '<div class="descendance-summary">' . descendance_badge($statut, $manquants)
            . '<p>' . e($enfants) . ' enfant' . ($enfants > 1 ? 's' : '') . ' ajouté' . ($enfants > 1 ? 's' : '')
            . '. ' . e(descendance_description($statut))
            . ($statut === Descendance::INCOMPLET && $manquants > 0
                ? ' <strong>' . e($manquants) . ' enfant' . ($manquants > 1 ? 's' : '') . ' connu'
                    . ($manquants > 1 ? 's' : '') . ' non ajouté' . ($manquants > 1 ? 's' : '') . '.</strong>'
                : '')
            . '</p></div>'
            . ($note !== null ? '<p class="descendance-note">' . e($note) . '</p>' : '');
    }

    private function sexLabel(string $sexe): string
    {
        return match ($sexe) {
            'M' => 'Homme',
            'F' => 'Femme',
            default => 'Genre inconnu',
        };
    }

    private function layout(string $title, string $content, string $base, string $active): string
    {
        $nav = [
            'index.html' => 'Accueil',
            'liste.html' => 'Sommaire',
            'arbre.html' => 'Arbre',
            'livre.html' => 'Livre',
            'descendances.html' => 'Descendances',
        ];

        $links = '';
        foreach ($nav as $file => $label) {
            $class = $active === $file ? ' is-active' : '';
            $links .= '<a class="nav-link' . $class . '" href="' . e($base . $file) . '">' . e($label) . '</a>';
        }

        return '<!DOCTYPE html>' . "\n"
            . '<html lang="fr">' . "\n"
            . '<head>' . "\n"
            . '<meta charset="UTF-8">' . "\n"
            . '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n"
            . '<meta name="generator" content="Famille static exporter">' . "\n"
            . '<title>' . e($title) . ' — ' . e($this->appName) . '</title>' . "\n"
            . '<link rel="stylesheet" href="' . e($base . 'assets/css/modern.css') . '">' . "\n"
            . '<script src="' . e($base . 'assets/js/script.js') . '" defer></script>' . "\n"
            . '<script src="' . e($base . 'assets/js/arbre.js') . '" defer></script>' . "\n"
            . '<script src="' . e($base . 'assets/js/static-search.js') . '" defer></script>' . "\n"
            . '</head>' . "\n"
            . '<body>' . "\n"
            . '<header class="app-header"><div class="header-inner">'
            . '<a class="brand" href="' . e($base . 'index.html') . '">'
            . '<span class="brand-mark">FHT</span><span class="brand-name">' . e($this->appName)
            . '<span>Site statique</span></span></a>'
            . '<nav class="primary-nav" id="link" aria-label="Navigation principale">' . $links . '</nav>'
            . '<div class="header-actions"><span class="badge badge-muted">HTML statique</span>'
            . '<button type="button" class="mobile-toggle" id="list-link" aria-label="Ouvrir la navigation" '
            . 'aria-expanded="false" aria-controls="link"><span aria-hidden="true">☰</span></button></div>'
            . '</div></header>'
            . '<main class="app-main">' . $content . '</main>'
            . '<footer class="app-footer">' . e($this->appName) . ' <span>·</span> site statique généré le '
            . e(date('d/m/Y H:i')) . '</footer>'
            . '</body></html>';
    }

    private function write(string $relative, string $content): string
    {
        $path = $this->outputDir . '/' . ltrim($relative, '/');

        if (file_put_contents($path, $content) === false) {
            throw new RuntimeException('Impossible d’écrire le fichier : ' . $path);
        }

        return $path;
    }

    private function copyAssets(): void
    {
        $files = [
            'assets/css/modern.css',
            'assets/js/script.js',
            'assets/js/arbre.js',
        ];

        foreach ($files as $file) {
            $source = $this->publicDir . '/' . $file;
            $target = $this->outputDir . '/' . $file;

            if (!is_file($source) || !copy($source, $target)) {
                throw new RuntimeException('Impossible de copier l’asset : ' . $file);
            }
        }

        $searchScript = $this->resourcesDir . '/static-search.js';
        if (is_file($searchScript) && !copy($searchScript, $this->outputDir . '/assets/js/static-search.js')) {
            throw new RuntimeException('Impossible de copier le script de recherche statique.');
        }
    }
}
