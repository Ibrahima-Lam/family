
<?php require "../autoloader/autoload.php"; ?>

  <?php
  $id=$_GET["id"]??1;

        $conx=new connexion();
        $tree=new arbre($conx,$id) ;
        $detail="<dd>"."<p>bonjour les gens</p>"."</dd>";
        
        echo $tree->makeChildren($id);

        ?>
   
