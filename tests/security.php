<?php

declare(strict_types=1);

use App\Models\Personne;
use App\Security\Auth;
use App\Support\Descendance;
use Core\Http\Request;
use Core\Security\Csrf;
use Core\Security\RateLimiter;
use Core\Service\Database;
use Core\Service\Migrator;
use Src\Arbre;

require dirname(__DIR__) . '/vendor/autoload.php';

$tempDb = sys_get_temp_dir() . '/famille-test-' . bin2hex(random_bytes(4)) . '.db';
copy(dirname(__DIR__) . '/database/famille.db', $tempDb);
putenv('DB_PATH=' . $tempDb);

(new Migrator(Database::connection(), dirname(__DIR__) . '/database/migrations'))->run();

$failures = 0;

function check(bool $condition, string $message): void
{
    global $failures;

    if (!$condition) {
        fwrite(STDERR, "ÉCHEC: $message\n");
        $failures++;
        return;
    }

    echo "OK: $message\n";
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

check(class_exists(Auth::class), 'Auth est disponible');
check(class_exists(Csrf::class), 'CSRF est disponible');
check(class_exists(RateLimiter::class), 'RateLimiter est disponible');
check(class_exists(Request::class), 'Request est disponible');

$_GET['p'] = 'personne/details/45';
$request = new Request();
check($request->path() === 'personne/details/45', 'Le chemin de requête est normalisé');

Csrf::bootstrap();
$token = Csrf::token();
check(strlen($token) === 64, 'Le jeton CSRF fait 64 caractères');
check(str_contains(Csrf::field(), $token), 'Le champ CSRF contient le jeton');
Csrf::validate($token);
check(true, 'Un jeton CSRF valide est accepté');

$before = Csrf::token();
Csrf::rotate();
check($before !== Csrf::token(), 'Le jeton CSRF est renouvelé après rotation');

check(Auth::normalizeRole('admin') === 'admin', 'Le rôle admin est normalisé');
check(Auth::normalizeRole('user') === 'editeur', 'L’ancien rôle user devient editeur');
check(Auth::normalizeRole('inconnu') === 'lecteur', 'Un rôle inconnu devient lecteur');

$rateKey = 'test:' . bin2hex(random_bytes(6));
check(!RateLimiter::tooManyAttempts($rateKey, 3, 60), 'Aucune tentative au départ');
RateLimiter::hit($rateKey, 60);
RateLimiter::hit($rateKey, 60);
RateLimiter::hit($rateKey, 60);
check(RateLimiter::tooManyAttempts($rateKey, 3, 60), 'Le rate limiter bloque après 3 tentatives');
RateLimiter::clear($rateKey);
check(!RateLimiter::tooManyAttempts($rateKey, 3, 60), 'Le rate limiter est réinitialisé');

$pdo = Database::connection();
$foreignKeys = (int) $pdo->query('PRAGMA foreign_keys')->fetchColumn();
check($foreignKeys === 1, 'Les clés étrangères SQLite sont activées');

$info = password_get_info('mot-de-passe-en-clair');
check(($info['algoName'] ?? 'unknown') === 'unknown', 'Les anciens mots de passe en clair sont détectés');

$arbre = new Arbre('45');
$tree = $arbre->getResult();
check($arbre->getRootId() === '45', 'La racine de l’arbre est résolue');
check(str_contains($tree, '<dl class="G2">') && str_contains($tree, 'data-generation="10"'), 'L’arbre est imbriqué par génération');
check(substr_count($tree, 'class="tree-node"') === 93, 'Toutes les personnes sont présentes dans l’arbre');

$graph = $arbre->toGraph();
check(($graph['root'] ?? null) === '45', 'Le graphe expose la racine de l’arbre');
check(count($graph['nodes'] ?? []) === 93, 'Le graphe contient toutes les personnes');
check(count($graph['edges'] ?? []) === 92, 'Le graphe contient tous les liens parent-enfant');
check(($graph['nodes'][0]['href'] ?? '') === '?p=personne/details/45', 'Le graphe pointe vers les fiches web');
check(isset($graph['nodes'][0]['status'], $graph['nodes'][0]['attention']), 'Le graphe expose le statut de descendance');

$model = new Personne();
$person = $model->findOne('64');
check($person !== false && $person->nomPersonne === 'Abdoulaye Yougourou' && $person->surnom === 'Abdoulaye Matal', 'Un nom avec surnom est séparé');
$person = $model->findOne('88');
check($person !== false && $person->nomPersonne === 'Dieinaba Abdoulaye' && $person->surnom === 'Bakaw', 'Un surnom séparé par un antislash est reconnu');
$defaultPersonId = $model->addPersonne('Test Descendance Defaut', 'F', null);
check(is_int($defaultPersonId) && $defaultPersonId > 0, 'Une personne peut être ajoutée pour tester le défaut de descendance');
$descendance = $model->getDescendance((string) $defaultPersonId);
check($descendance['statut'] === Descendance::INCONNU, 'La descendance est inconnue par défaut');
check($model->delete((string) $defaultPersonId), 'La personne de test est supprimée');
check($model->saveDescendance(45, Descendance::INCOMPLET, 2, 'Deux enfants non ajoutés'), 'La descendance peut être mise à jour');
$person = $model->findOne('45');
check(
    $person !== false
    && $person->enfantsStatut === Descendance::INCOMPLET
    && (int) $person->enfantsManquants === 2
    && $person->enfantsNote === 'Deux enfants non ajoutés',
    'Le statut de descendance est enregistré'
);
$incompletes = $model->findByDescendanceStatut(Descendance::INCOMPLET);
check(count($incompletes) >= 1, 'Les descendances incomplètes sont filtrables');
check(($model->countByDescendanceStatut()[Descendance::INCOMPLET] ?? 0) >= 1, 'Les statuts de descendance sont comptés');
check(!$model->saveDescendance(999999, Descendance::COMPLET, 0, null), 'La mise à jour d’une descendance inconnue échoue');
check(Descendance::normalizeMissing(Descendance::COMPLET, 4) === 0, 'Un statut complet ne conserve pas de manquants');
check($model->saveDescendance(45, Descendance::INCONNU, 0, null), 'La descendance peut être remise à inconnu');

$childId = $model->addPersonne('Test Sécurité', 'F', '45', 'Test Alias');
check(is_int($childId) && $childId > 0, 'Une personne peut être ajoutée');
$child = $model->findOne((string) $childId);
check($child !== false && $child->surnom === 'Test Alias', 'Le surnom est enregistré');
check($model->wouldCreateCycle(45, (string) $childId), 'Un cycle parent/enfant est détecté');
check(!$model->editPersonne(45, 'Hamet teguela/Diadji  teguela', 'M', (string) $childId), 'Un cycle parent/enfant est refusé');
check($model->editPersonne($childId, 'Test Sécurité 2', 'F', null, 'Alias Modifié'), 'Une personne peut être détachée de son parent');
$child = $model->findOne((string) $childId);
check($child !== false && $child->surnom === 'Alias Modifié', 'Le surnom est modifié');
check($model->delete((string) $childId), 'Une personne peut être supprimée');
check($model->findOne((string) $childId) === false, 'La personne supprimée est introuvable');
check(!$model->delete('999999'), 'La suppression d’un identifiant inconnu échoue');

@unlink($tempDb);
@unlink($tempDb . '-wal');
@unlink($tempDb . '-shm');

if ($failures > 0) {
    fwrite(STDERR, "\n$failures test(s) en échec.\n");
    exit(1);
}

echo "\nTous les tests de sécurité sont passés.\n";
