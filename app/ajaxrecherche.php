<?php
http_response_code(404);
exit("Page introuvable.");
?>

<?php require "../autoloader/autoload.php" ;?>



    
    <?php
    
    $param=$_GET["param"]?? null;
    $ordrd=$_GET["ordrd"]??null;
    $ord=$_GET["ord"]?? "asc";
    if(!in_array($ord,["desc","asc"])) $ord="asc";


    $ordre= !empty($ordrd) ? " order by $ordrd $ord" : "";
    $condition= !empty($param) ? "where p.nomPersonne like '%$param%'or d.nomPersonne like '%$param%'" : "";
    
    $conx=new Connexion();
    $prop="p.idPersonne as id,p.nomPersonne as nom ,nomGeneration,p.sexe as sexe,d.nomPersonne as parent";
    $res=$conx->pdo->query("SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
    left join personne d on(e.idParent=d.idPersonne) $condition $ordre");
    $res->setFetchMode(PDO::FETCH_CLASS,"Personne");
    $tab=$res->fetchAll();
    $edite=$_REQUEST["edite"]??null;
    ?>
   
   
       
            <?php foreach($tab as $value):?>
                <tr>
                    <td><?=$value->id ?></td>
                    <td><a href="personne.php?id=<?=$value->id?>"> <?=ucwords($value->nom) ?></a></td>
                    <td><?=$value->nomGeneration ?></td>
                    <td><?=$value->sexe ?></td>
                    <td><a href="personne.php?id=<?=$value->id?>"><?=$value->parent ?></a></td>

                     <?php if($edite):?> 
                    <td  id="<?=$value->id ?>" title="Editer" class="edite" onclick="editer(this)"><img src="../public/icons/pencil.svg" width="15px" ></td>
                    <td id="<?=$value->id ?>" title="Supprimer" class="delete" onclick="supprimer(this)"><img src="../public/icons/basket.svg" width="15px"  ></td>
                    <?php endif?>
                </tr>
            <?php endforeach?>
        

