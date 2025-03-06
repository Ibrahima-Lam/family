<?php require "../autoloader/autoload.php"; ?>

<?php
$conx = new Connexion();
$table=$_REQUEST["table"]??"personne";
$attr=$_REQUEST["attr"]??"idPersonne";
$id=$_REQUEST["id"]??null;
$res = $conx->pdo->exec("delete from $table where $attr='$id' ");
if($res)echo "supprimé";
?>