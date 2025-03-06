<?php

namespace App\Controllers;

use Core\Controllers\Controller;
use src\Arbre;
use src\Html;

class ApiController extends Controller
{
    const STATUS_OK = 1;
    const STATUS_ERROR = 0;
    const STATUS_NO_DATA = 2;
    const MESSAGE_SAVE_OK = "Enregistrement effectué avec succès";
    const MESSAGE_SAVE_ERROR = "Erreur lors de l'enregistrement";
    const MESSAGE_DELETE_OK = 'Supprimé avec succès';
    const MESSAGE_DELETE_ERROR = 'Erreur lors de la suppression';
    const MESSAGE_UPDATE_OK = 'Modification effectuée avec succès';
    const MESSAGE_UPDATE_ERROR = 'Erreur lors de la Modification';

    public function arbre($id = null): void
    {
        $arbre = new Arbre($id);
        $tree = $arbre->makeChildren($id);
        $this->response($tree);
    }
    public function options(String $idg = null): void
    {
        $selected = $_GET["selected"] ?? null;
        $data = $idg ? $this->loadModel('personne')->findAllIdandNomByGeneration($idg, true) : $this->loadModel('personne')->findAllIdandNom();
        $options = Html::option($data, 'idPersonne', 'nomPersonne', $selected);
        $this->response($options);
    }

    public function generations()
    {
        $data = $this->loadModel('generation')->findAll();
        $this->response($data);
    }


    public function personnes()
    {
        $data = $this->loadModel('personne')->findAll();
        $this->response($data);
    }

    public function personne($id)
    {
        $personne = $this->loadModel('personne')->findOne($id);
        $this->response($personne);
    }
    public function names()
    {
        $data = $this->loadModel('personne')->findAllIdandNom();
        $data = array_column($data, 'nomPersonne');
        $names = [];
        foreach ($data as $value) {
            $n = explode(" ", $value);
            foreach ($n as $v) {
                $v = trim($v);
                if (empty($v)) continue;
                $names[] = ucfirst($v);
                $names = array_unique($names);
                $names = array_values($names);
                sort($names);
            }
        }
        return $this->response($names);
    }


    public function add()
    {

        extract($_REQUEST);

        $generation =
            $this->loadModel('personne')->findElementGeneration($parent);
        $check = $this->loadModel('generation')->checkGeneration($generation);
        $generation++;
        if (!$check) {
            $this->loadModel('generation')->addGeneration($generation);
        }
        $res = $this->loadModel('personne')->addPersonne($nom, $generation, $sexe, $parent);
        if ($res) {
            return    $this->response([
                "message" => self::MESSAGE_SAVE_OK,
                "status" => self::STATUS_OK,
                'data' => $this->loadModel('personne')->findLastOne()
            ]);
        } else
            return $this->response([
                "message" => self::MESSAGE_SAVE_ERROR,
                "status" => self::STATUS_ERROR,
            ]);
    }
    public function edit()
    {

        extract($_REQUEST);

        $generation =
            $this->loadModel('personne')->findElementGeneration($parent);
        $check = $this->loadModel('generation')->checkGeneration($generation);
        $generation++;
        if (!$check) {
            $this->loadModel('generation')->addGeneration($generation);
        }
        $res = $this->loadModel('personne')->editPersonne($id, $nom, $generation, $sexe, $parent);
        if ($res) {
            return    $this->response([
                "message" => self::MESSAGE_UPDATE_OK,
                "status" => self::STATUS_OK,
                'data' => $this->loadModel('personne')->findLastOne()
            ]);
        } else
            return $this->response([
                "message" => self::MESSAGE_UPDATE_ERROR,
                "status" => self::STATUS_ERROR,
            ]);
    }
    public function delete($id = null)
    {
        $res = $id ? $this->loadModel('personne')->delete($id) : false;
        if ($res) return $this->response([
            "message" => self::MESSAGE_DELETE_OK,
            "status" => self::STATUS_OK,
        ]);
        else
            return $this->response([
                "message" => self::MESSAGE_DELETE_ERROR,
                "status" => self::STATUS_ERROR,
            ]);
    }
}
