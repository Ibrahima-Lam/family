<?php
class Personne
{   public $nomPersonne;
    public function singleName()
    {
        $tab=explode(" ",$this->nomPersonne);
       return $tab;
       
    }
}
