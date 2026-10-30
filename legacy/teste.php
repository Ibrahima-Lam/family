<?php
$user = 'root';
$password = '';
$dsn = 'mysql:dbname=testdb;host=localhost';
$pdo = new PDO($dns, $user, $password);


// envoi d'une requette de select
$req = $pdo->query("select.......");
// recuperation plusieurs lignes des donnees
$data = $req->fetchAll();


// recuperation d'une seul ligne de donnees
$data = $req->fetch();


// envoi de requette de suppression
$res = $pdo->exec('delete.....');

// envoi de requette de modification
$res = $pdo->exec('update.....');

// envoi de requette d'insertion
$res = $pdo->exec('insert.....');
