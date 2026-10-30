<?php

namespace App\Controllers;

use Core\Controllers\Controller;
use Src\Arbre;
use Src\Html;

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
    public function options(?string $idg = null): void
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
        $this->response($names);
    }


    private function checkCsrf(): void
    {
        $token = (string) ($_REQUEST['_token'] ?? '');
        if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
            $this->response([
                'message' => 'Requête invalide (CSRF)',
                'status' => self::STATUS_ERROR,
            ]);
        }
    }

    private function input(): array
    {
        $nom = trim((string) ($_REQUEST['nom'] ?? ''));
        $sexe = (string) ($_REQUEST['sexe'] ?? '');
        $parent = $_REQUEST['parent'] ?? null;
        $parent = ($parent === '' || $parent === null) ? null : trim((string) $parent);

        return [$nom, $sexe, $parent];
    }

    private function validParent(?string $parent): bool
    {
        if ($parent === null) {
            return true;
        }
        if (!ctype_digit($parent) || (int) $parent <= 0) {
            return false;
        }
        return (bool) $this->loadModel('personne')->findOne($parent);
    }

    public function add(): void
    {

        $this->checkCsrf();
        [$nom, $sexe, $parent] = $this->input();

        if ($nom === '' || !in_array($sexe, ['M', 'F'], true) || !$this->validParent($parent)) {
            $this->response([
                "message" => self::MESSAGE_SAVE_ERROR,
                "status" => self::STATUS_ERROR,
            ]);
            return;
        }

        $generation = $parent ? $this->loadModel('personne')->findElementGeneration($parent) : 0;
        $check = $this->loadModel('generation')->checkGeneration($generation);
        $generation++;
        if (!$check) {
            $this->loadModel('generation')->addGeneration($generation);
        }
        $res = $this->loadModel('personne')->addPersonne($nom, (string) $generation, $sexe, $parent);
        if ($res) {
            $this->response([
                "message" => self::MESSAGE_SAVE_OK,
                "status" => self::STATUS_OK,
                'data' => $this->loadModel('personne')->findLastOne()
            ]);
        } else
            $this->response([
                "message" => self::MESSAGE_SAVE_ERROR,
                "status" => self::STATUS_ERROR,
            ]);
    }
    public function edit(): void
    {

        $this->checkCsrf();
        [$nom, $sexe, $parent] = $this->input();
        $id = (string) ($_REQUEST['id'] ?? '');

        if (
            !ctype_digit($id) || (int) $id <= 0
            || !$this->loadModel('personne')->findOne($id)
            || $nom === '' || !in_array($sexe, ['M', 'F'], true)
            || !$this->validParent($parent)
        ) {
            $this->response([
                "message" => self::MESSAGE_UPDATE_ERROR,
                "status" => self::STATUS_ERROR,
            ]);
            return;
        }

        $generation = $parent ? $this->loadModel('personne')->findElementGeneration($parent) : 0;
        $check = $this->loadModel('generation')->checkGeneration($generation);
        $generation++;
        if (!$check) {
            $this->loadModel('generation')->addGeneration($generation);
        }
        $res = $this->loadModel('personne')->editPersonne((int) $id, $nom, (string) $generation, $sexe, $parent);
        if ($res) {
            $this->response([
                "message" => self::MESSAGE_UPDATE_OK,
                "status" => self::STATUS_OK,
                'data' => $this->loadModel('personne')->findLastOne()
            ]);
        } else
            $this->response([
                "message" => self::MESSAGE_UPDATE_ERROR,
                "status" => self::STATUS_ERROR,
            ]);
    }
    public function delete($id = null)
    {
        $this->checkCsrf();
        $id = $id ?? ($_REQUEST['id'] ?? null);
        $id = ($id === null || $id === '') ? null : (string) $id;
        $valid = $id !== null && ctype_digit($id) && (int) $id > 0;
        $res = $valid ? $this->loadModel('personne')->delete($id) : false;
        if ($res) $this->response([
            "message" => self::MESSAGE_DELETE_OK,
            "status" => self::STATUS_OK,
        ]);
        else
            $this->response([
                "message" => self::MESSAGE_DELETE_ERROR,
                "status" => self::STATUS_ERROR,
            ]);
    }
}
