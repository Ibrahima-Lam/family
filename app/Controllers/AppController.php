<?php

namespace App\Controllers;

use Core\Controllers\Controller;

class AppController extends Controller
{
    public function index(): void
    {
        $people = $this->loadModel('personne')->findAll();
        $people = is_array($people) ? $people : [];
        $generations = 0;

        foreach ($people as $person) {
            $generations = max($generations, (int) ($person->generation ?? 0));
        }

        $descendance = $this->loadModel('personne')->countByDescendanceStatut();
        $aCompleter = ($descendance['incomplet'] ?? 0) + ($descendance['inconnu'] ?? 0);

        $this->render('home', [
            'total' => count($people),
            'generations' => $generations,
            'aCompleter' => $aCompleter,
            'descendance' => $descendance,
        ]);
    }
}
