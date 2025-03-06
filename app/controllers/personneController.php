<?php

namespace App\Controllers;

use Core\Controllers\Controller;
use src\Html;
use src\Arbre;
use src\Altoperson;
use src\Link;
use stdClass;

class PersonneController extends Controller
{

    public function index(): void
    {

        $data = $this->loadModel('personne')->findAll();
        $this->render('personne/liste', compact('data'));
    }

    public function details($id = null): void
    {
        Html::$target = $id;
        $data = $this->loadModel('personne')->findOne($id);
        $tab = $this->loadModel('personne')->findAllIdandNom();
        $parent = $data && $data->idParent ? $this->loadModel('personne')->findParent($data->idParent) : null;
        $freres = $data && $data->idParent ? $this->loadModel('personne')->findSiblings($data->idParent) : [];
        $enfants = $data  ? $this->loadModel('personne')->findChildren($id) : [];
        $options = Html::option($tab, "idPersonne", "nomPersonne", $id);
        $freresHtml = Html::table($freres);
        $enfantsHtml = Html::table($enfants);
        $parentHtml = Html::table($parent ? [$parent] : []);
        $this->render('personne/details', compact('data', 'freresHtml', 'enfantsHtml', 'options', 'parentHtml'));
    }
    public function arbre($id = null): void
    {
        $arbre = new Arbre($id);
        $tree = $arbre->getResult();
        $this->render('personne/arbre', compact('tree', 'id'));
    }
    public function lien(): void
    {
        $id1 = $_POST["id1"] ?? null;
        $id2 = $_POST["id2"] ?? null;
        if ($id1 && $id2 && $id1 != $id2) {
            $person = new Altoperson(45);

            $links = $person->getLink($id1, $id2);
            $parents1 = array_reverse($links->getFirstParents());
            $parents2 = array_reverse($links->getSecondParents());

            $element1 = $this->loadModel('personne')->findOne($id1);
            $element2 = $this->loadModel('personne')->findOne($id2);
        } else {

            $links = new Link(new stdClass(), [], [], false,);
            $parents1 = [];
            $parents2 = [];
            $element1 = [];
            $element2 = [];
        }

        $tab = $this->loadModel('personne')->findAllIdandNom();
        $options1 = Html::option($tab, "idPersonne", "nomPersonne", $id1);
        $options2 = Html::option($tab, "idPersonne", "nomPersonne", $id2);
        $this->render('personne/lien', compact('options1', 'options2', 'parents1', 'parents2', 'element1', 'element2', 'links', 'id1', 'id2'));
    }
    public function livre(): void
    {
        $tab = $this->loadModel('personne')->findAll();
        $this->render('personne/livre', compact('tab'));
    }

    public function ajout(): void
    {
        $data = $this->loadModel('personne')->findAllIdandNom(true);
        $generation = $this->loadModel('generation')->findAll();
        $html = Html::option($data, 'idPersonne', 'nomPersonne');
        $gen = Html::option($generation, 'idGeneration', 'nomGeneration');
        $this->render('personne/ajout', compact('html', 'gen'));
    }
    public function edit(): void
    {
        $data = $this->loadModel('personne')->findALl();
        $this->render('personne/editer', compact('data'));
    }
}
