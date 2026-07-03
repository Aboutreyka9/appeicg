<?php
// if (session_status() === PHP_SESSION_NONE) {
    session_name("APP178674846_SESSION");
    session_start();

// }
// Charger le fichier de configuration une fois en ligne

// declare(strict_types=1);
// include 'config-production.php';
// include 'config-production-user.php';

// Activer le rapport d'erreurs (en développement uniquement)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';

// ─── Variables d'environnement ───────────────────────────────────────────────
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

use App\Controllers\AccessoireController;
use App\Controllers\AnneeController;
use App\Controllers\AuthController;
use App\Controllers\ClasseController;
use App\Controllers\DashboardController;
use App\Controllers\ControllerException;
use App\Controllers\ControllerMailer;
use App\Controllers\ControllerPrinter;
use App\controllers\ControllerUser;
use App\Controllers\CycleController;
use App\Controllers\DocumentController;
use App\Controllers\EmploiTempsController;
use App\Controllers\EnseignantController;
use App\Controllers\EtablissementController;
use App\Controllers\EtudiantController;
use App\Controllers\EvenementController;
use App\Controllers\FiliereController;
use App\Controllers\InscriptionController;
use App\Controllers\MatiereController;
use App\Controllers\MessageController;
use App\Controllers\NiveauController;
use App\Controllers\NoteController;
use App\Controllers\PaiementController;
use App\Controllers\SalleController;
use App\Controllers\ScolariteController;
use App\Controllers\SemestreController;
use App\Core\Router;
use App\Middlewares\RouteMiddleWare;
use Phroute\Phroute\Dispatcher;




$path = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);

$title = "";

$router = new Router();

/**
 * ************************************************
 * SEXION FILTER ROUTES 
 * ************************************************
 */

/* filter  for all routes*/
$router->filter('auth', [RouteMiddleWare::class, 'requireAuth']);
/* filter  for all routes*/
// $router->filter('auth', [RouteMiddleWare::class, 'requireAuth']);

$router->filter('guest', [RouteMiddleWare::class, 'isLogged']);
// $router->filter('setting', [RouteMiddleWare::class, 'requireSetting']);
// $router->filter('ghotel', [RouteMiddleWare::class, 'requireGesHotel']);
// $router->filter('comptable', [RouteMiddleWare::class, 'requireComptable']);
// $router->filter('reception', [RouteMiddleWare::class, 'requireReception']);
// $router->filter('admin', [RouteMiddleWare::class, 'requireAdmin']);


/**
 * ************************************************
 * FIN SEXION FILTER ROUTES 
 * ************************************************
 */


// MAIN GROUP A NE PAS TOUCHE
$router->group(['before' => '','prefix' => 'appeicg'], function($router){


// ─── Auth ───────────────────────────────────────────────────────────────────
$router->post('auth/login',   [AuthController::class, 'login']);
$router->post('auth/logout',  [AuthController::class, 'logout']);
$router->get('auth/check',    [AuthController::class, 'check']);

// ─── Établissements ──────────────────────────────────────────────────────────
$router->get('etablissements/liste',     [EtablissementController::class, 'liste']);
$router->post('etablissements/ajouter',  [EtablissementController::class, 'ajouter']);
$router->post('etablissements/modifier', [EtablissementController::class, 'modifier']);
$router->post('etablissements/statut',   [EtablissementController::class, 'statut']);

// ─── Années scolaires ────────────────────────────────────────────────────────
$router->get('annees/liste',     [AnneeController::class, 'liste']);
$router->post('annees/ajouter',  [AnneeController::class, 'ajouter']);
$router->post('annees/modifier', [AnneeController::class, 'modifier']);
$router->post('annees/statut',   [AnneeController::class, 'statut']);

// ─── Semestres ───────────────────────────────────────────────────────────────
$router->get('semestres/liste',      [SemestreController::class, 'liste']);
$router->post('semestres/ajouter',   [SemestreController::class, 'ajouter']);
$router->post('semestres/modifier',  [SemestreController::class, 'modifier']);
$router->post('semestres/supprimer', [SemestreController::class, 'supprimer']);

// ─── Cycles ──────────────────────────────────────────────────────────────────
$router->get('cycles/liste',     [CycleController::class, 'liste']);
$router->post('cycles/ajouter',  [CycleController::class, 'ajouter']);
$router->post('cycles/modifier', [CycleController::class, 'modifier']);
$router->post('cycles/statut',   [CycleController::class, 'statut']);

// ─── Filières ────────────────────────────────────────────────────────────────
$router->get('filieres/liste',     [FiliereController::class, 'liste']);
$router->post('filieres/ajouter',  [FiliereController::class, 'ajouter']);
$router->post('filieres/modifier', [FiliereController::class, 'modifier']);
$router->post('filieres/statut',   [FiliereController::class, 'statut']);

// ─── Niveaux ─────────────────────────────────────────────────────────────────
$router->get('niveaux/liste',     [NiveauController::class, 'liste']);
$router->post('niveaux/ajouter',  [NiveauController::class, 'ajouter']);
$router->post('niveaux/modifier', [NiveauController::class, 'modifier']);
$router->post('niveaux/statut',   [NiveauController::class, 'statut']);

// ─── Classes ─────────────────────────────────────────────────────────────────
$router->get('classes/liste',     [ClasseController::class, 'liste']);
$router->post('classes/ajouter',  [ClasseController::class, 'ajouter']);
$router->post('classes/modifier', [ClasseController::class, 'modifier']);
$router->post('classes/statut',   [ClasseController::class, 'statut']);

// ─── Salles ──────────────────────────────────────────────────────────────────
$router->get('salles/liste',     [SalleController::class, 'liste']);
$router->post('salles/ajouter',  [SalleController::class, 'ajouter']);
$router->post('salles/modifier', [SalleController::class, 'modifier']);
$router->post('salles/statut',   [SalleController::class, 'statut']);

// ─── Matières ────────────────────────────────────────────────────────────────
$router->get('matieres/liste',     [MatiereController::class, 'liste']);
$router->post('matieres/ajouter',  [MatiereController::class, 'ajouter']);
$router->post('matieres/modifier', [MatiereController::class, 'modifier']);
$router->post('matieres/statut',   [MatiereController::class, 'statut']);

// ─── Enseignants ─────────────────────────────────────────────────────────────
$router->get('enseignants/liste',     [EnseignantController::class, 'liste']);
$router->post('enseignants/ajouter',  [EnseignantController::class, 'ajouter']);
$router->post('enseignants/modifier', [EnseignantController::class, 'modifier']);
$router->post('enseignants/statut',   [EnseignantController::class, 'statut']);
$router->get('enseignants/matieres',  [EnseignantController::class, 'matieres']);
$router->post('enseignants/affecter', [EnseignantController::class, 'affecter']);
$router->post('enseignants/retirer',  [EnseignantController::class, 'retirer']);

// ─── Inscriptions ────────────────────────────────────────────────────────────
$router->get('inscriptions/liste',               [InscriptionController::class, 'liste']);
$router->post('inscriptions/ajouter',            [InscriptionController::class, 'ajouter']);
$router->post('inscriptions/modifier-classe',    [InscriptionController::class, 'modifierClasse']);
$router->post('inscriptions/modifier-montant',   [InscriptionController::class, 'modifierMontant']);
$router->post('inscriptions/statut',             [InscriptionController::class, 'statut']);
$router->get('inscriptions/accessoires',         [InscriptionController::class, 'accessoires']);
$router->post('inscriptions/accessoires/ajouter',[InscriptionController::class, 'ajouterAccessoire']);
$router->post('inscriptions/accessoires/retirer',[InscriptionController::class, 'retirerAccessoire']);

// ─── Documents ───────────────────────────────────────────────────────────────
$router->get('documents/liste',      [DocumentController::class, 'liste']);
$router->post('documents/ajouter',   [DocumentController::class, 'ajouter']);
$router->post('documents/modifier',  [DocumentController::class, 'modifier']);
$router->post('documents/supprimer', [DocumentController::class, 'supprimer']);

// ─── Messages ────────────────────────────────────────────────────────────────
$router->get('messages/liste',         [MessageController::class, 'liste']);
$router->post('messages/creer',        [MessageController::class, 'creer']);
$router->post('messages/statut',       [MessageController::class, 'updateStatut']);
$router->post('messages/supprimer',    [MessageController::class, 'supprimer']);

// ─── Événements ──────────────────────────────────────────────────────────────
$router->get('evenements/liste',      [EvenementController::class, 'liste']);
$router->post('evenements/ajouter',   [EvenementController::class, 'ajouter']);
$router->post('evenements/modifier',  [EvenementController::class, 'modifier']);
$router->post('evenements/statut',    [EvenementController::class, 'statut']);
$router->post('evenements/supprimer', [EvenementController::class, 'supprimer']);
$router->get('notes/liste',       [NoteController::class, 'liste']);
$router->post('notes/ajouter',    [NoteController::class, 'ajouter']);
$router->post('notes/modifier',   [NoteController::class, 'modifier']);
$router->post('notes/supprimer',  [NoteController::class, 'supprimer']);
$router->get('notes/moyennes',    [NoteController::class, 'moyennes']);
$router->get('notes/bulletin',    [NoteController::class, 'bulletin']);
$router->get('notes/classement',  [NoteController::class, 'classement']);
$router->get('emplois-temps/liste',      [EmploiTempsController::class, 'liste']);
$router->post('emplois-temps/ajouter',   [EmploiTempsController::class, 'ajouter']);
$router->post('emplois-temps/modifier',  [EmploiTempsController::class, 'modifier']);
$router->post('emplois-temps/supprimer', [EmploiTempsController::class, 'supprimer']);
$router->get('scolarites/liste',      [ScolariteController::class, 'liste']);
$router->post('scolarites/ajouter',   [ScolariteController::class, 'ajouter']);
$router->post('scolarites/modifier',  [ScolariteController::class, 'modifier']);
$router->post('scolarites/supprimer', [ScolariteController::class, 'supprimer']);

// ─── Paiements ───────────────────────────────────────────────────────────────
$router->get('paiements/liste',           [PaiementController::class, 'liste']);
$router->post('paiements/enregistrer',    [PaiementController::class, 'enregistrer']);
$router->post('paiements/annuler',        [PaiementController::class, 'annuler']);
$router->get('paiements/stats',           [PaiementController::class, 'stats']);
$router->get('paiements/by-inscription',  [PaiementController::class, 'byInscription']);
$router->get('accessoires/liste',     [AccessoireController::class, 'liste']);
$router->post('accessoires/ajouter',  [AccessoireController::class, 'ajouter']);
$router->post('accessoires/modifier', [AccessoireController::class, 'modifier']);
$router->post('accessoires/statut',   [AccessoireController::class, 'statut']);
$router->get('etudiants/liste',            [EtudiantController::class, 'liste']);
$router->post('etudiants/ajouter',         [EtudiantController::class, 'ajouter']);
$router->post('etudiants/modifier',        [EtudiantController::class, 'modifier']);
$router->post('etudiants/statut',          [EtudiantController::class, 'statut']);
$router->get('etudiants/parent',           [EtudiantController::class, 'getParent']);
$router->post('etudiants/parent/sauver',   [EtudiantController::class, 'saveParent']);
$router->get('etudiants/dossiers',         [EtudiantController::class, 'getDossiers']);
$router->post('etudiants/dossiers/ajouter',    [EtudiantController::class, 'ajouterDossier']);
$router->post('etudiants/dossiers/supprimer',  [EtudiantController::class, 'supprimerDossier']);

// ─── Pages (vues) ────────────────────────────────────────────────────────────
$router->get('/',          [AuthController::class, 'authentication'],['before' => 'guest']);
$router->get('/login',     [AuthController::class, 'authentication'],['before' => 'guest']);
$router->get('/dashboard',  [DashboardController::class, 'index']);
$router->get('/etablissements',[EtablissementController::class, 'index'],['before' => 'auth']);
$router->get('/annees',    [AnneeController::class, 'index'],['before' => 'auth']);
$router->get('/cycles',       [CycleController::class, 'index'],['before' => 'auth']);
$router->get('/classes',      [ClasseController::class, 'index'],['before' => 'auth']);
$router->get('/matieres',     [MatiereController::class, 'index'],['before' => 'auth']);
$router->get('/enseignants',  [EnseignantController::class, 'index'],['before' => 'auth']);
$router->get('/etudiants',    [EtudiantController::class, 'index'],['before' => 'auth']);
$router->get('/inscriptions', [InscriptionController::class, 'index'],['before' => 'auth']);
$router->get('/paiements',         [PaiementController::class, 'index'],['before' => 'auth']);
$router->get('/emplois-du-temps', [EmploiTempsController::class, 'index'],['before' => 'auth']);
$router->get('/notes',            [NoteController::class, 'index'],['before' => 'auth']);
$router->get('/communication',    function () { require __DIR__ . '/../templates/communication.php'; });



});


$dispatcher = new Dispatcher($router->getData());


try {
    $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'],$path);

    echo $response;
}
//  catch(Exception $e){
//     // Fallback pour les routes non trouvées
//     // Rediriger vers la page 404
//     http_response_code(404);
//     var_dump($e->getMessage());
//     // require __DIR__ . '/templates/404.php';
// }
catch (Phroute\Phroute\Exception\HttpRouteNotFoundException $e) {
    // Route inconnue
    // $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    // if (str_contains($accept, 'application/json') || str_starts_with($uri, '/appeicg/')) {
    //     header('Content-Type: application/json');
    //     http_response_code(404);
    //     echo json_encode(['success' => false, 'code' => 404, 'message' => 'Route introuvable.']);
    // } else {
    //     http_response_code(404);
    //     require __DIR__ . '/templates/404.php';
    // }
    var_dump($e->getMessage());

} catch (Phroute\Phroute\Exception\HttpMethodNotAllowedException $e) {
//     header('Content-Type: application/json');
//     http_response_code(405);
//     echo json_encode(['success' => false, 'code' => 405, 'message' => 'Méthode non autorisée.']);
// } catch (Throwable $e) {
//     $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
//     $msg   = $debug ? $e->getMessage() : 'Erreur interne du serveur.';
//     header('Content-Type: application/json');
//     http_response_code(500);
//     echo json_encode(['success' => false, 'code' => 500, 'message' => $msg]);
    var_dump($e->getMessage());

}