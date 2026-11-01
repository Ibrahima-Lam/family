<?php
$statusCode = $status ?? 500;
$errorTitle = $title ?? 'Erreur';
$errorMessage = $message ?? $errorTitle;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($statusCode) ?> — <?= e($errorTitle) ?></title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            display: grid;
            place-items: center;
            color: #1b2b26;
            background:
                radial-gradient(700px 380px at 92% 0, rgba(201, 154, 61, .18), transparent 64%),
                radial-gradient(520px 360px at 0 100%, rgba(58, 125, 102, .18), transparent 64%),
                #f4f1e9;
            font-family: "Avenir Next", "Segoe UI", system-ui, sans-serif;
        }
        main {
            width: min(100%, 620px);
            padding: clamp(28px, 6vw, 48px);
            border: 1px solid #e1dbcf;
            border-radius: 26px;
            background: #fffdf9;
            box-shadow: 0 36px 90px -40px rgba(16, 42, 34, .45);
        }
        .status {
            display: inline-flex;
            padding: 5px 11px;
            border-radius: 999px;
            background: #f6edd9;
            color: #94452a;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .12em;
        }
        h1 {
            margin: 18px 0 10px;
            font-family: "Iowan Old Style", Palatino, Georgia, serif;
            font-size: clamp(2rem, 5vw, 3rem);
            line-height: 1.1;
            color: #173b30;
        }
        p { margin: 0 0 24px; color: #66746d; line-height: 1.6; }
        a {
            display: inline-flex;
            padding: 10px 18px;
            border-radius: 999px;
            background: #1f4d3e;
            color: #fff;
            font-size: .88rem;
            font-weight: 700;
            text-decoration: none;
        }
        a:hover { background: #102a22; }
    </style>
</head>
<body>
<main>
    <span class="status"><?= e($statusCode) ?></span>
    <h1><?= e($errorTitle) ?></h1>
    <p><?= e($errorMessage) ?></p>
    <a href="?p=app/index">Retour à l’accueil</a>
</main>
</body>
</html>
