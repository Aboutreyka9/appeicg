
<?php
    session_name("APP178674846_SESSION");


session_start();

use App\Controllers\Controller;
use App\Controllers\ControllerPrinter;

require __DIR__ . '/../../vendor/autoload.php';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
    exit;
}
// var_dump($_POST);

$action = $_POST['action'] ?? null;

switch ($action) {
    /**
     * SEXION data configuration
     */
    
    // Debut Actions pour les utilisateurs
    case 'test':
        $ajx = new Controller();
        $ajx->test();
    break;
   

    // Fin de Salaire 
    // Fin Actions pour les hotels


    case 'pdf_facture':
        $fac = new ControllerPrinter();
        $fac->factureData();
    break;
        
    case 'pdf_version_save':
        $pdfGen = new ControllerPrinter();
        $pdfGen->newVersionPdfSave();
    break;

    // Fin Actions pour les utilisateurs
    /**
     *  fIN Sexion data configuration
     */


    // Autres cas...
    default:
        echo json_encode(['status' => 'error', 'message' => 'Action inconnue']);
    break;
}

