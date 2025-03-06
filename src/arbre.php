<?php

namespace src;


use App\Modele\Personne;




class Arbre
{
    private Personne $model;
    private $data = [];
    private $result = [];



    public function __construct($id)
    {
        $this->model = new Personne();

        $this->getData($id);
    }
    private function getData($id)
    {

        $data = $this->model->getAllByGeneration($id);
        $this->result = $this->model->getAll();
        $cles = array_column($data, "id");
        $this->data = array_combine($cles, array_column($data, "nom"));
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





    private function getChildren($id): array
    {
        $children = array_filter($this->result, function ($value) use ($id) {
            return $value["idParent"] == $id;
        });
        $cles = array_column($children, "id");
        return   array_combine($cles, array_column($children, "nom"));
    }


    public function makeChildren($id): string
    {

        $children = $this->getChildren($id);
        return !empty($children) ? $this->makeDetail($children) : "<span class='empty'> pas d'enfant  </span>";
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
