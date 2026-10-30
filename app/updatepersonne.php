<?php
http_response_code(404);
exit("Page introuvable.");
?>
<?php require "../autoloader/autoload.php"; ?>

<?php
$conx = new Connexion();
$id=$_REQUEST["id"]??null;
$res= $conx->pdo->query("select * from personne join etre using(idPersonne) where idPersonne='$id'");
$res->setFetchMode(PDO::FETCH_CLASS,"personne");
$res=$res->fetch();

?>
<form action="traiteupdate.php" method="post" class="form" id="updating">
<input type="hidden" name="idp" value='<?=$res->idPersonne?>'>
            <div class="control">
                <label for="">Le nom de la personne:</label>
                <div>
                    <input type="text" id="texte" name='nom' class='field champ' value='<?=$res->nomPersonne?>' placeholder='Entrer le Nom et Le Prenom' autocomplete="off" required>
                   
                </div>
            </div>
     <div class="control">
                <label for="">le parent:</label>
                <div>
                <select name="parent" id="parent" class='champ field'step="5" value='<?=$res->generation?>'>
                    <?php
                    $conx = new Connexion();
                    $req = "select * from personne order by idPersonne desc ";
                    echo html::option($conx, $req, "idPersonne", "nomPersonne",$res->idParent);
                    ?>
                </select>
            </div>

            </div>



            <div class="control">
                <label for=" ">le sexe:</label>
                <div class="radio field">
                    <span>masculin:</span>
                    <input type="radio" name='sexe' value='M' <?=$res->sexe!=="M"?:"checked"?> class="champ">
                    <span> feminin:</span>
                    <input type="radio" name='sexe' class="champ" value='F'<?=$res->sexe!=="F"?:"checked"?>>
                </div>
            </div>
           
            <div class="action">
                <input type="reset" name="annule" value='annuler' class='btn btn-default '>
                <input type="submit" name='envoi' value='envoyer' class='btn btn-success champ'>
            </div>
        </form>

