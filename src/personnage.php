<?php

namespace src;

use App\Modele\Personne;


class Personnage
{

    private Personne $model;


    public $parent;
    public $freres = [];
    public $enfants = [];

    public $element;


    public function __construct($id)
    {

        $this->model = new Personne();

        $this->element = $this->model->findOne($id);
        $idParent = $this->element->idParent;
        if ($idParent) {
            $this->parent = $this->model->findOne($idParent);
            $this->freres = $this->model->findSiblings($idParent);
        }
        $this->enfants = $this->model->findChildren($id);
    }
}
