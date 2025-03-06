<?php

$idg=$_GET["id"] ?? 1;

require "../autoloader/autoload.php" ;
$conx=new Connexion();
$param= !empty($idg)?"where generation='$idg'":"";
$req="select * from personne $param order by idPersonne desc ";
echo html::option($conx,$req,"idPersonne","nomPersonne");

?>