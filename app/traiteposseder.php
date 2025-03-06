<?php require "../autoloader/autoload.php"; ?>

<?php
$conx = new Connexion();
extract($_REQUEST);
$result=$conx->pdo->exec("insert into posseder values(null,'$idPersonne','$idSupplement','$valeur')");


if($result ) echo  "Enregistré";
else echo "echec";
