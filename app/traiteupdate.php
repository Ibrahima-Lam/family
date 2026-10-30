<?php
http_response_code(404);
exit("Page introuvable.");
?>
<?php require "../autoloader/autoload.php"; ?>

<?php
$conx = new Connexion();
extract($_REQUEST);
$result=$conx->pdo->query("select * from personne   where idPersonne='$parent'");
$result=$result->fetch(5);
$gen=$result->generation+1;




$res = $conx->pdo->exec("update  personne set nomPersonne='$nom',
sexe='$sexe',generation='$gen' where idPersonne='$idp'" );


$re = $conx->pdo->exec("update  etre set idParent='$parent'
 where idPersonne='$idp'" );


if($res && $re ) header("location:editer.php");
else echo "echec";

?>
