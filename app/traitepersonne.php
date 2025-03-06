<?php
 require "../autoloader/autoload.php" ;
  
 var_dump($_REQUEST);
 if (isset($_REQUEST["envoi"])) {
     $post=array_map(function ($val)
     {
         return ucwords($val);
        },$_REQUEST);
        
        extract($post);
    $conx=new Connexion();
    $generation=$conx->pdo->query("select generation from personne where idPersonne='$parent'")->fetch(PDO::FETCH_NUM)[0];
    $check=$conx->pdo->query("select idGeneration from generation where idGeneration>'$generation'")->fetch(PDO::FETCH_NUM)[0];
    $generation++;
    if (!$check) {
        $conx->pdo->exec("insert into generation values(null,'G$generation')");
    }
    
    $ex=$conx->pdo->exec("INSERT INTO personne VALUES(NULL,'$nom','$generation','$sexe')");
    if ($ex) {
        $id=$conx->pdo->query("select idPersonne from personne order by idPersonne desc limit 1 ")->fetch(PDO::FETCH_NUM)[0];
        $ex=$conx->pdo->exec("insert into etre values(null,'$id','$parent')");
    }
   $token=$_REQUEST["token"] ?? false;
    if ($ex && !$token) {
          header("location:sommaire.php");
    }
}
?>