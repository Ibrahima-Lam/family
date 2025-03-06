<?php
class Personnage
{  
     private $pdo;
    private $id;
    public $parent;
    public $freres;
    public $enfants;
    public $possession;


    public function __construct(Connexion $pdo,$id) {
        $this->pdo = $pdo->pdo;
        $this->id = $id;
        $this->element=$this->getData($id,true);
        
        $idParent=$this->element->idParent;
        $this->parent=$this->getParent($idParent,true);
        $this->freres=$this->getFreres($idParent);
        $this->enfants=$this->getEnfants($id);
        $this->possession=$this->getPossession($id);


    }
    private function getReq($prop,$val): string
    {
        $req="SELECT * FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) WHERE  $prop='$val'";
        return $req;
    }
    private function getData($id)
    {
        $req=$this->getReq("idPersonne",$id);
        // var_dump($req);
        $res=$this->pdo->query($req);
        $res->setFetchMode(PDO::FETCH_CLASS,"Personne");
        $tab=$res->fetch();
        return $tab?? null;
    }
    private function getParent($idParent)
    {
        $req=$this->getReq("idPersonne",$idParent);
        $res=$this->pdo->query($req);
        $res->setFetchMode(PDO::FETCH_CLASS,"Personne");
        $tab=$res->fetchAll();
        return $tab?? null;
    }
    private function getFreres($idParent)
    {
        $req=$this->getReq("idParent",$idParent);
        $res=$this->pdo->query($req);
        $res->setFetchMode(PDO::FETCH_CLASS,"Personne");
        $tab=$res->fetchAll();
        return $tab?? null;
    }
    private function getEnfants($id)
    {
        $req=$this->getReq("idParent",$id);
        $res=$this->pdo->query($req);
        $res->setFetchMode(PDO::FETCH_CLASS,"Personne");
        $tab=$res->fetchAll();
                return $tab?? null;
    }
    private function getPossession($id)
    {
        $req="select * from posseder join personne using(idPersonne) join supplement using(idSupplement) where idPersonne='$id' ";
        $res=$this->pdo->query($req);
        $res->setFetchMode(PDO::FETCH_CLASS,"Personne");
        $tab=$res->fetchAll();
                return $tab?? null;
    }
}
