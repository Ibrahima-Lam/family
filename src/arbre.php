<?php
class arbre
{
    private $db;
    private $data = [];
    private $children = [];

    public function __construct(Connexion $db, $id)
    {
        $this->db = $db;
        $this->id = $id;
        $this->getData($id);
    }

    public function getDl(array $tab, $dt = null): string
    {
        $dl = "<dl>";
        $dl .= "<dt>" . $dt ?? null . "</dt>";
        foreach ($tab as  $value) {
            if (is_array($value)) {
                $dl .= $this->getDl($value);
            } else $dl .= "<dd>$value</dd>";
        }

        $dl .= "</dl>";
        return $dl;
    }


    public function getDetail($det, $sum = null, $id = null, $opened = true): string
    {
        if (in_array($id, array_keys($this->data))) $opened = false;
        $open = $opened ? "open" : "";
        $detail = "<details id='$id' $open ontoggle='charge(this)'>";
        $detail .= "<summary>$sum ($id)</summary>";
        $detail .= $det;

        $detail .= "</details>";
        return $detail;
    }



    private function getData($id)
    {
        $prop = "p.idPersonne as id,p.nomPersonne as nom ,nomGeneration,p.sexe as sexe,d.nomPersonne as parent, d.idPersonne as idParent ,g.idGeneration as idg";
        $req = "SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
        left join personne d on(e.idParent=d.idPersonne) where g.idGeneration='$id' ";
        $data = $this->db->pdo->query($req)->fetchAll(PDO::FETCH_ASSOC);
        $cles = array_column($data, "id");
        $this->data = array_combine($cles, array_column($data, "nom"));

        return $this->data;
    }


    private function getChildren($id)
    {
        $prop = "p.idPersonne as id,p.nomPersonne as nom ,nomGeneration,p.sexe as sexe,d.nomPersonne as parent, d.idPersonne as idParent ,g.idGeneration as idg";
        $req = "SELECT $prop FROM personne p  left natural join etre e join generation g on ( p.generation=g.idGeneration) 
        left join personne d on(e.idParent=d.idPersonne) where d.idPersonne='$id' ";
        $children = $this->db->pdo->query($req)->fetchAll(PDO::FETCH_ASSOC);
        $cles = array_column($children, "id");
        $this->children = array_combine($cles, array_column($children, "nom"));

        return $this->children;
    }


    public function makeChildren($id): string
    {
        $this->getChildren($id);
        $children = $this->children;
        return !empty($children) ? $this->makeDetail($this->children) : "<span class='empty'> pas d'enfant  </span>";
    }


    public function getResult()
    {
        return $this->makeDetail($this->data);
    }


    private function makeDetail(array $tab): string
    {
        $dets = "";
        foreach ($tab as $k => $value) {
            $opened = $this->getChildren($k) ? true : false;
            $dets .= (is_array($value)) ? $this->makeDetail($value) : $this->getDetail(null, $value, $k, $opened);
        }
        return $dets;
    }
}
