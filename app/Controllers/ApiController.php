<?php

namespace App\Controllers;

use App\Support\Descendance;
use Core\Controllers\Controller;
use Core\Http\Request;
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
        $id = ($id === null || $id === '') ? '1' : (string) $id;
        $arbre = new Arbre($id);
        $rootId = $arbre->getRootId() ?? $id;
        $tree = $arbre->makeChildren($rootId);
        $this->response($tree);
    }

    public function options(?string $idg = null): void
    {
        $request = new Request();
        $selected = $request->string('selected');
        $data = $idg
            ? $this->loadModel('personne')->findAllIdandNomByGeneration($idg, true)
            : $this->loadModel('personne')->findAllIdandNom();
        $options = Html::option($data, 'idPersonne', 'nomPersonne', $selected === '' ? null : $selected);
        $this->response($options);
    }

    public function generations(): void
    {
        $data = $this->loadModel('generation')->findAll();
        $this->response($data);
    }

    public function personnes(): void
    {
        $data = $this->loadModel('personne')->findAll();
        $this->response($data);
    }

    public function personne($id = null): void
    {
        if (!is_string($id) || !ctype_digit($id) || (int) $id <= 0) {
            $this->response([
                'message' => 'Identifiant invalide',
                'status' => self::STATUS_ERROR,
            ], 422);
        }

        $personne = $this->loadModel('personne')->findOne($id);
        if (!$personne) {
            $this->response([
                'message' => 'Personne introuvable',
                'status' => self::STATUS_NO_DATA,
            ], 404);
        }

        $this->response($personne);
    }

    public function names(): void
    {
        $data = $this->loadModel('personne')->findAllIdandNom();
        $names = [];

        foreach ($data as $value) {
            foreach ([$value['nomPersonne'] ?? '', $value['surnom'] ?? ''] as $label) {
                $parts = preg_split('~\s+~u', trim((string) $label)) ?: [];

                foreach ($parts as $part) {
                    $part = trim($part);

                    if ($part === '') {
                        continue;
                    }

                    $names[] = ucfirst($part);
                    $names = array_values(array_unique($names));
                }
            }
        }

        sort($names);
        $this->response($names);
    }

    public function descendances(): void
    {
        $request = new Request();
        $statut = Descendance::normalize($request->string('statut', Descendance::INCOMPLET));
        $model = $this->loadModel('personne');

        $this->response([
            'status' => self::STATUS_OK,
            'statut' => $statut,
            'counts' => $model->countByDescendanceStatut(),
            'data' => $model->findByDescendanceStatut($statut),
        ]);
    }

    public function descendance(): void
    {
        $request = new Request();
        $id = $request->string('id');
        $statut = Descendance::normalize($request->string('statut'));
        $manquants = max(0, min(999, (int) $request->string('manquants', '0')));
        $note = $request->string('note');
        $model = $this->loadModel('personne');

        if (!ctype_digit($id) || (int) $id <= 0 || !$model->findOne($id) || mb_strlen($note, 'UTF-8') > 1000) {
            $this->response([
                'message' => self::MESSAGE_UPDATE_ERROR,
                'status' => self::STATUS_ERROR,
            ], 422);
        }

        if (!$model->saveDescendance((int) $id, $statut, $manquants, $note)) {
            $this->response([
                'message' => self::MESSAGE_UPDATE_ERROR,
                'status' => self::STATUS_ERROR,
            ], 500);
        }

        $this->response([
            'message' => self::MESSAGE_UPDATE_OK,
            'status' => self::STATUS_OK,
            'data' => $model->getDescendance($id),
        ]);
    }

    /** @return array{0:string,1:?string,2:string,3:?string} */
    private function input(): array
    {
        $request = new Request();

        $nom = $request->string('nom');
        $surnom = $request->string('surnom');
        $sexe = $request->string('sexe');
        $parent = $request->string('parent');

        return [$nom, $surnom === '' ? null : $surnom, $sexe, $parent === '' ? null : $parent];
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
        [$nom, $surnom, $sexe, $parent] = $this->input();

        if ($nom === '' || mb_strlen($nom, 'UTF-8') > 255
            || ($surnom !== null && mb_strlen($surnom, 'UTF-8') > 255)
            || !in_array($sexe, ['M', 'F'], true)
            || !$this->validParent($parent)
        ) {
            $this->response([
                'message' => self::MESSAGE_SAVE_ERROR,
                'status' => self::STATUS_ERROR,
            ], 422);
        }

        $model = $this->loadModel('personne');
        $id = $model->addPersonne($nom, $sexe, $parent, $surnom);

        if ($id === false) {
            $this->response([
                'message' => self::MESSAGE_SAVE_ERROR,
                'status' => self::STATUS_ERROR,
            ], 500);
        }

        $this->response([
            'message' => self::MESSAGE_SAVE_OK,
            'status' => self::STATUS_OK,
            'data' => $model->findOne((string) $id),
        ], 201);
    }

    public function edit(): void
    {
        [$nom, $surnom, $sexe, $parent] = $this->input();
        $request = new Request();
        $id = $request->string('id');
        $model = $this->loadModel('personne');

        if (
            !ctype_digit($id) || (int) $id <= 0
            || !$model->findOne($id)
            || $nom === '' || mb_strlen($nom, 'UTF-8') > 255
            || ($surnom !== null && mb_strlen($surnom, 'UTF-8') > 255)
            || !in_array($sexe, ['M', 'F'], true)
            || !$this->validParent($parent)
            || $parent === $id
            || $model->wouldCreateCycle((int) $id, $parent)
        ) {
            $this->response([
                'message' => self::MESSAGE_UPDATE_ERROR,
                'status' => self::STATUS_ERROR,
            ], 422);
        }

        if (!$model->editPersonne((int) $id, $nom, $sexe, $parent, $surnom)) {
            $this->response([
                'message' => self::MESSAGE_UPDATE_ERROR,
                'status' => self::STATUS_ERROR,
            ], 500);
        }

        $this->response([
            'message' => self::MESSAGE_UPDATE_OK,
            'status' => self::STATUS_OK,
            'data' => $model->findOne($id),
        ]);
    }

    public function delete($id = null): void
    {
        $request = new Request();
        $id = $id ?? $request->string('id');
        $id = ($id === null || $id === '') ? null : (string) $id;
        $valid = $id !== null && ctype_digit($id) && (int) $id > 0;
        $res = $valid ? $this->loadModel('personne')->delete($id) : false;

        if ($res) {
            $this->response([
                'message' => self::MESSAGE_DELETE_OK,
                'status' => self::STATUS_OK,
            ]);
        }

        $this->response([
            'message' => self::MESSAGE_DELETE_ERROR,
            'status' => self::STATUS_ERROR,
        ], 422);
    }
}
