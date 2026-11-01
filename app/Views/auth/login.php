<?php
$title = 'Connexion';
?>

<div class="auth-shell">
    <div class="auth-visual">
        <span class="eyebrow">Espace privé</span>
        <h2>Gardons le fil des générations.</h2>
        <p>Connectez-vous pour enrichir les fiches, corriger les liens et transmettre la mémoire familiale.</p>
    </div>

    <div class="auth-form-panel">
        <span class="eyebrow">Connexion</span>
        <h1>Bon retour</h1>
        <p>Utilisez votre nom d’utilisateur et votre mot de passe.</p>

        <?php if (!empty($error)): ?>
            <p class="alert alert-danger" role="alert"><?= e($error) ?></p>
        <?php endif ?>

        <form action="?p=login" method="post" class="form">
            <?= csrf_field() ?>

            <div class="control">
                <label for="nom">Nom d’utilisateur</label>
                <input type="text" id="nom" name="nom" value="<?= e($nom ?? '') ?>" required autocomplete="username" autofocus>
            </div>

            <div class="control">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <div class="action">
                <button type="submit" class="btn btn-success">Se connecter</button>
            </div>
        </form>

        <p class="auth-back"><a href="?p=app/index">Retour à l’accueil</a></p>
    </div>
</div>
