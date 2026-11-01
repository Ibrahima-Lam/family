<?php
$title = 'Accueil';
$total = $total ?? 0;
$generations = $generations ?? 0;
$aCompleter = $aCompleter ?? 0;
?>

<section class="hero">
    <span class="eyebrow">Mémoire familiale</span>
    <h1>La famille Hamet Tenguella, transmise autrement.</h1>
    <p>Parcourez les générations, retrouvez les liens de parenté et faites vivre l’histoire familiale dans un espace clair, chaleureux et partagé.</p>

    <div class="hero-actions">
        <a class="btn btn-success" href="?p=personne">
            Explorer le sommaire
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
        <a class="btn btn-ghost" href="?p=personne/arbre/1">Voir l’arbre généalogique</a>
    </div>

    <div class="hero-stats">
        <div class="stat-card">
            <strong><?= e($total) ?></strong>
            <span>Personnes</span>
        </div>
        <div class="stat-card">
            <strong><?= e($generations) ?></strong>
            <span>Générations</span>
        </div>
        <a class="stat-card" href="?p=personne/incomplets">
            <strong><?= e($aCompleter) ?></strong>
            <span>Descendances à vérifier</span>
        </a>
    </div>
</section>

<section class="feature-grid">
    <a class="feature-card" href="?p=personne">
        <span class="feature-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
        </span>
        <h3>Sommaire</h3>
        <p>Retrouvez chaque membre de la famille dans une liste claire et consultable.</p>
        <span>Ouvrir le sommaire</span>
    </a>

    <a class="feature-card" href="?p=personne/arbre/1">
        <span class="feature-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="5" r="2.5"/><circle cx="6" cy="19" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="M12 7.5v4M6 16.5v-2h12v2"/></svg>
        </span>
        <h3>Arbre</h3>
        <p>Explorez les branches et descendez de génération en génération.</p>
        <span>Voir l’arbre</span>
    </a>

    <a class="feature-card" href="?p=personne/lien">
        <span class="feature-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.07 0l2-2A5 5 0 0 0 12 4l-1 1"/><path d="M14 11a5 5 0 0 0-7.07 0l-2 2A5 5 0 0 0 12 20l1-1"/></svg>
        </span>
        <h3>Liens</h3>
        <p>Découvrez le lien exact qui unit deux personnes de la famille.</p>
        <span>Calculer un lien</span>
    </a>

    <a class="feature-card" href="?p=personne/livre">
        <span class="feature-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v18H6.5A2.5 2.5 0 0 0 4 22V4.5Z"/><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/></svg>
        </span>
        <h3>Livre</h3>
        <p>Parcourez la mémoire familiale dans une version pensée pour la lecture et l’impression.</p>
        <span>Ouvrir le livre</span>
    </a>
</section>
