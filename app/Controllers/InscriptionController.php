<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\MainController;
use App\Models\InscriptionModel;
use App\Models\EtudiantModel;
use App\Models\ClasseModel;
use App\Models\AnneeModel;
use App\Models\AccessoireModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class InscriptionController extends MainController
{
    private InscriptionModel $model;
    private EtudiantModel    $etudiantModel;
    private ClasseModel      $classeModel;
    private AnneeModel       $anneeModel;
    private AccessoireModel  $accModel;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        // AuthService::requireAuth();
        $this->model             = new InscriptionModel();
        $this->etudiantModel     = new EtudiantModel();
        $this->classeModel       = new ClasseModel();
        $this->anneeModel        = new AnneeModel();
        $this->accModel          = new AccessoireModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

    public function index()
    {
        return $this->view('etudiants/inscriptions', ["title" => "Inscription"]);
    }

    // ─── Inscriptions ─────────────────────────────────────────

    public function liste(): void
    {
        $filters = [
            'annee_code'  => $_GET['annee_code']  ?? '',
            'classe_code' => $_GET['classe_code'] ?? '',
            'statut'      => $_GET['statut']      ?? '',
            'search'      => $_GET['search']      ?? '',
        ];
        Response::success('Liste des inscriptions.', $this->model->liste($this->etablissementCode, $filters));
    }

    public function ajouter(): void
    {
        $etudiantCode = Validator::post('etudiant_code');
        $classeCode   = Validator::post('classe_code');
        $anneeId      = Validator::post('annee_id');
        $montant      = Validator::post('montant_scolarite_inscription');

        $v = new Validator();
        $v->required('etudiant_code', $etudiantCode, 'Étudiant')
          ->required('classe_code',   $classeCode,   'Classe')
          ->required('annee_id',      $anneeId,      'Année scolaire');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        // Vérifier existence étudiant (intégrité manuelle)
        if (!$this->etudiantModel->findByCode($etudiantCode, $this->etablissementCode)) {
            Response::notFound('Étudiant introuvable.');
        }
        // Vérifier existence classe
        if (!$this->classeModel->findByCode($classeCode, $this->etablissementCode)) {
            Response::notFound('Classe introuvable.');
        }
        // Récupérer l'année
        $annee = $this->anneeModel->findById((int) $anneeId, $this->etablissementCode);
        if (!$annee) Response::notFound('Année scolaire introuvable.');
        $anneeCode = $annee['libelle_annee'];

        // Règle métier : un étudiant ne peut avoir qu'une seule inscription par année
        if ($this->model->findByEtudiantAnnee($etudiantCode, $anneeCode, $this->etablissementCode)) {
            Response::error('Cet étudiant est déjà inscrit pour cette année scolaire.', 409);
        }

        $code = $this->model->create([
            'etudiant_code'               => $etudiantCode,
            'classe_code'                 => $classeCode,
            'annee_code'                  => $anneeCode,
            'montant_scolarite_inscription' => $montant ? (float) $montant : 0,
            'etablissement_code'          => $this->etablissementCode,
            'user_code'                   => $this->userCode,
        ]);

        Response::success('Inscription effectuée avec succès.', ['code_inscription' => $code], 201);
    }

    public function modifierClasse(): void
    {
        $code       = Validator::post('code_inscription');
        $classeCode = Validator::post('classe_code');

        $v = new Validator();
        $v->required('code_inscription', $code,       'Code inscription')
          ->required('classe_code',      $classeCode, 'Classe');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Inscription introuvable.');
        }
        if (!$this->classeModel->findByCode($classeCode, $this->etablissementCode)) {
            Response::notFound('Classe introuvable.');
        }

        $this->model->updateClasse($code, $classeCode, $this->etablissementCode);
        Response::success('Classe modifiée avec succès.');
    }

    public function modifierMontant(): void
    {
        $code    = Validator::post('code_inscription');
        $montant = Validator::post('montant_scolarite_inscription');

        $v = new Validator();
        $v->required('code_inscription',             $code,    'Code inscription')
          ->required('montant_scolarite_inscription', $montant, 'Montant');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Inscription introuvable.');
        }

        $this->model->updateMontant($code, (float) $montant, $this->etablissementCode);
        Response::success('Montant mis à jour.');
    }

    public function statut(): void
    {
        $code   = Validator::post('code_inscription');
        $statut = Validator::post('statut_inscription');

        $v = new Validator();
        $v->required('code_inscription',  $code,   'Code inscription')
          ->required('statut_inscription', $statut, 'Statut')
          ->in('statut_inscription', $statut, ['valide', 'solde', 'annule'], 'Statut');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Inscription introuvable.');
        }
        $this->model->updateStatut($code, $statut, $this->etablissementCode);
        Response::success('Statut mis à jour.');
    }

    // ─── Accessoires d'une inscription ────────────────────────

    public function accessoires(): void
    {
        $inscriptionCode = $_GET['inscription_code'] ?? '';
        if (!$inscriptionCode) Response::error('Code inscription requis.', 422);
        $data = $this->accModel->listByInscription($inscriptionCode, $this->etablissementCode);
        Response::success('Accessoires.', $data);
    }

    public function ajouterAccessoire(): void
    {
        $inscriptionCode = Validator::post('inscription_code');
        $accessoireCode  = Validator::post('accessoire_code');

        $v = new Validator();
        $v->required('inscription_code', $inscriptionCode, 'Inscription')
          ->required('accessoire_code',  $accessoireCode,  'Accessoire');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        $inscription = $this->model->findByCode($inscriptionCode, $this->etablissementCode);
        if (!$inscription) Response::notFound('Inscription introuvable.');

        if (!$this->accModel->findByCode($accessoireCode, $this->etablissementCode)) {
            Response::notFound('Accessoire introuvable.');
        }

        $code = $this->accModel->ajouterAInscription([
            'inscription_code'   => $inscriptionCode,
            'accessoire_code'    => $accessoireCode,
            'annee_code'         => $inscription['annee_code'],
            'etablissement_code' => $this->etablissementCode,
            'user_code'          => $this->userCode,
        ]);

        if (!$code) Response::error('Cet accessoire est déjà associé à cette inscription.', 409);
        Response::success('Accessoire ajouté.', ['code_accessoire_inscription' => $code], 201);
    }

    public function retirerAccessoire(): void
    {
        $code = Validator::post('code_accessoire_inscription');
        if (!$code) Response::error('Code requis.', 422);
        $this->accModel->retirerDeInscription($code, $this->etablissementCode);
        Response::success('Accessoire retiré.');
    }
}
