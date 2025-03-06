<?php require "../autoloader/autoload.php"; ?>

<?php
$conx = new Connexion();
$res = $conx->pdo->query("Select distinct nomPersonne from personne order by nomPersonne asc ");
$res->setFetchMode(PDO::FETCH_CLASS, "Personne");
$result = $res->fetchAll();
$tab = [];
foreach ($result as $item) {
    foreach ($item->singleName() as  $value) {
        $tab[] = $value;
    }
}
$tab=array_map(function ($val)
{
   return ucwords($val);
},$tab);

$tab = array_unique($tab);
sort($tab);
$json = json_encode($tab);
file_put_contents("file.json", $json);
?>