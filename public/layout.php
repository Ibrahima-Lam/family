<?php

use App\Security\Auth;
use Core\Security\Csrf;

$currentUser = Auth::check() ? Auth::user() : null;
$flashError = flash_get('error');
$flashSuccess = flash_get('success');
$p = is_string($_GET['p'] ?? null) ? $_GET['p'] : 'app/index';
$srch = preg_match('/edit/', $p) ? 'search' : 'srch';
$isActive = static fn (string $prefix): string => str_starts_with($p, $prefix) ? ' is-active' : '';
$isHome = ($p === '' || str_starts_with($p, 'app/index')) ? ' is-active' : '';
$isDirectory = ($p === 'personne' || str_starts_with($p, 'personne/details') || str_starts_with($p, 'personne/edit') || str_starts_with($p, 'personne/ajout')) ? ' is-active' : '';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <title><?= e($title ?? 'Famille Hamet Tenguella') ?> — Famille Hamet Tenguella</title>
    <script>
        window.CSRF_TOKEN = <?= json_encode(Csrf::token(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>
    <link rel="stylesheet" href="assets/css/modern.css">
    <script src="assets/js/script.js" defer></script>
    <script src="assets/js/arbre.js" defer></script>
    <script src="assets/js/search.js" defer></script>
</head>

<body>
    <header class="app-header">
        <div class="header-inner">
            <a class="brand" href="?p=app/index" aria-label="Famille Hamet Tenguella, accueil">
                <span class="brand-mark">FHT</span>
                <span class="brand-name">Famille<span>Hamet Tenguella</span></span>
            </a>

            <nav class="primary-nav" id="link" aria-label="Navigation principale">
                <a class="nav-link<?= $isHome ?>" href="?p=app/index">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h5v-6h4v6h5V9.5"/></svg>
                    Accueil
                </a>
                <a class="nav-link<?= $isDirectory ?>" href="?p=personne">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                    Sommaire
                </a>
                <a class="nav-link<?= $isActive('personne/arbre') ?>" href="?p=personne/arbre/1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="5" r="2.5"/><circle cx="6" cy="19" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="M12 7.5v4M6 16.5v-2h12v2"/></svg>
                    Arbre
                </a>
                <a class="nav-link<?= $isActive('personne/lien') ?>" href="?p=personne/lien">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.07 0l2-2A5 5 0 0 0 12 4l-1 1"/><path d="M14 11a5 5 0 0 0-7.07 0l-2 2A5 5 0 0 0 12 20l1-1"/></svg>
                    Liens
                </a>
                <a class="nav-link<?= $isActive('personne/livre') ?>" href="?p=personne/livre">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v18H6.5A2.5 2.5 0 0 0 4 22V4.5Z"/><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/></svg>
                    Livre
                </a>
            </nav>

            <div class="header-actions">
                <?php if ($currentUser !== null): ?>
                    <label class="search-field" for="<?= e($srch) ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
                        <input type="search" id="<?= e($srch) ?>" placeholder="Rechercher une personne…" aria-label="Rechercher une personne">
                    </label>
                <?php endif ?>

                <div class="account">
                    <?php if ($currentUser !== null): ?>
                        <span class="user-chip" title="<?= e($currentUser['nom'] ?? '') ?>">
                            <span class="avatar"><?= e(mb_substr((string) ($currentUser['nom'] ?? '?'), 0, 1)) ?></span>
                            <span class="user-meta">
                                <strong><?= e($currentUser['nom'] ?? '') ?></strong>
                                <small><?= e(Auth::role()) ?></small>
                            </span>
                        </span>
                        <form action="?p=logout" method="post" class="logout-form">
                            <?= Csrf::field() ?>
                            <button type="submit" class="icon-button" title="Déconnexion" aria-label="Déconnexion">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 4H5v16h5"/><path d="M14 8l4 4-4 4M18 12H9"/></svg>
                            </button>
                        </form>
                    <?php else: ?>
                        <a class="btn btn-ghost btn-sm" href="?p=login">Connexion</a>
                    <?php endif ?>
                </div>

                <div class="menu-wrap">
                    <button type="button" class="menu-trigger" id="menu-bar" aria-haspopup="true" aria-expanded="false" aria-controls="menu" title="Actions">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </button>
                    <div id="menu" class="actions-menu">
                        <?php if (Auth::canEdit()): ?>
                            <a href="?p=personne/ajout">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                                Ajouter une personne
                            </a>
                            <a href="?p=personne/edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 20h4l10.5-10.5a2.1 2.1 0 0 0-3-3L5 17v3Z"/><path d="m13.5 6.5 3 3"/></svg>
                                Gérer les personnes
                            </a>
                        <?php endif ?>
                        <a href="?p=personne/incomplets">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                            Descendances à vérifier
                        </a>
                        <div class="menu-divider"></div>
                        <a href="?p=personne/livre&imprim=true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>
                            Imprimer le livre
                        </a>
                    </div>
                </div>

                <button type="button" class="mobile-toggle" id="list-link" aria-label="Ouvrir la navigation" aria-expanded="false" aria-controls="link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
            </div>
        </div>
    </header>

    <main class="app-main">
        <?php if (!empty($flashError) || !empty($flashSuccess)): ?>
            <div class="flash-stack">
                <?php if (!empty($flashError)): ?>
                    <p class="alert alert-danger" role="alert"><?= e($flashError) ?></p>
                <?php endif ?>
                <?php if (!empty($flashSuccess)): ?>
                    <p class="alert alert-success" role="status"><?= e($flashSuccess) ?></p>
                <?php endif ?>
            </div>
        <?php endif ?>

        <div id="search-container" class="search-container"></div>

        <?= $content ?>
    </main>

    <footer class="app-footer">
        Famille Hamet Tenguella <span>·</span> mémoire familiale partagée
    </footer>

    <template id="template">
        <img src="assets/icons/triangle-fill.svg" class="sort-icon" alt="">
    </template>
</body>

</html>
