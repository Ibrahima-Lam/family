<?php

namespace App\Controllers;

use App\Support\Descendance;
use Core\Controllers\Controller;
use Core\Http\Request;
use Core\Http\Response;
use Src\Html;
use Src\Arbre;
use Src\Altoperson;
use Src\Link;
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
        if (!is_string($id) || !ctype_digit($id) || (int) $id <= 0) {
            Response::abort(404, 'Personne introuvable.');
        }

        $data = $this->loadModel('personne')->findOne($id);
        if (!$data) {
            Response::abort(404, 'Personne introuvable.');
        }

        Html::$target = $id;
        $tab = $this->loadModel('personne')->findAllIdandNom();
        $parent = $data && $data->idParent ? $this->loadModel('personne')->findParent($data->idParent) : null;
        $freres = $data && $data->idParent ? $this->loadModel('personne')->findSiblings($data->idParent) : [];
        $enfants = $data  ? $this->loadModel('personne')->findChildren($id) : [];
        $options = Html::option($tab, "idPersonne", "nomPersonne", $id);
        $freresHtml = Html::table($freres);
        $enfantsHtml = Html::table($enfants);
        $parentHtml = Html::table($parent ? [$parent] : []);
        $descendance = $this->loadModel('personne')->getDescendance($id);
        $childrenCount = count($enfants);
        $descendanceStatuts = Descendance::statuses();
        $this->render(
            'personne/details',
            compact(
                'data',
                'freresHtml',
                'enfantsHtml',
                'options',
                'parentHtml',
                'descendance',
                'childrenCount',
                'descendanceStatuts'
            )
        );
    }

    public function descendance(): void
    {
        $request = new Request();
        $id = $request->string('id');

        if (!ctype_digit($id) || (int) $id <= 0) {
            flash_set('error', 'Identifiant invalide.');
            Response::redirect('?p=personne');
        }

        $statut = Descendance::normalize($request->string('statut'));
        $manquants = max(0, min(999, (int) $request->string('manquants', '0')));
        $note = $request->string('note');

        if (mb_strlen($note, 'UTF-8') > 1000) {
            flash_set('error', 'La note de descendance est trop longue (1000 caractères maximum).');
            Response::redirect('?p=personne/details/' . $id);
        }

        $saved = $this->loadModel('personne')->saveDescendance($id, $statut, $manquants, $note);

        if (!$saved) {
            flash_set('error', 'Impossible de mettre à jour la descendance.');
        } else {
            flash_set('success', 'La descendance a été mise à jour.');
        }

        Response::redirect('?p=personne/details/' . $id);
    }

    public function incomplets(): void
    {
        $request = new Request();
        $statut = Descendance::normalize($request->string('statut', Descendance::INCOMPLET));
        $model = $this->loadModel('personne');
        $data = $model->findByDescendanceStatut($statut);
        $counts = $model->countByDescendanceStatut();
        $descendanceStatuts = Descendance::statuses();
        $this->render(
            'personne/incomplets',
            compact('data', 'counts', 'statut', 'descendanceStatuts')
        );
    }
    public function arbre($id = null): void
    {
        $arbre = new Arbre($id);
        $id = $arbre->getRootId() ?? ($id === null || $id === '' ? '1' : (string) $id);
        $rootName = $arbre->getRootName();
        $rootSurname = $arbre->getRootSurname();
        $tree = $arbre->getResult();
        $graph = $arbre->toGraph();
        $this->render('personne/arbre', compact('tree', 'graph', 'id', 'rootName', 'rootSurname'));
    }
    public function lien(): void
    {
        $id1 = isset($_POST['id1']) ? (string) $_POST['id1'] : null;
        $id2 = isset($_POST['id2']) ? (string) $_POST['id2'] : null;

        $element1 = ($id1 && ctype_digit($id1))
            ? $this->loadModel('personne')->findOne($id1)
            : null;
        $element2 = ($id2 && ctype_digit($id2))
            ? $this->loadModel('personne')->findOne($id2)
            : null;

        if ($id1 && $id2 && $id1 !== $id2 && $element1 && $element2) {
            $person = new Altoperson($id1);

            $links = $person->getLink($id1, $id2);
            $parents1 = array_reverse($links->getFirstParents());
            $parents2 = array_reverse($links->getSecondParents());
        } else {

            $links = new Link(new stdClass(), [], [], false);
            $parents1 = [];
            $parents2 = [];
            $element1 = new stdClass();
            $element2 = new stdClass();
        }

        $tab = $this->loadModel('personne')->findAllIdandNom();
        $options1 = Html::option($tab, "idPersonne", "nomPersonne", $id1);
        $options2 = Html::option($tab, "idPersonne", "nomPersonne", $id2);
        $this->render('personne/lien', compact('options1', 'options2', 'parents1', 'parents2', 'element1', 'element2', 'links', 'id1', 'id2'));
    }
    public function livre(): void
    {
        $tab = $this->loadModel('personne')->findAll();
        $arbre = new Arbre();
        $lineages = [];

        foreach ($tab as $value) {
            $personId = (string) $value->idPersonne;
            $lineages[$personId] = $arbre->getLineage($personId, '#');
        }

        $this->render('personne/livre', compact('tab', 'lineages'));
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
        $data = $this->loadModel('personne')->findAll();
        $this->render('personne/editer', compact('data'));
    }
}
