<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\MainController;
use App\Models\EtudiantModel;
use App\Models\ParentModel;
use App\Models\DossierEtudiantModel;
use App\Models\AnneeModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class EtudiantController extends MainController
{
    private EtudiantModel       $model;
    private ParentModel         $parentModel;
    private DossierEtudiantModel $dossierModel;
    private AnneeModel          $anneeModel;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        // AuthService::requireAuth();
        $this->model             = new EtudiantModel();
        $this->parentModel       = new ParentModel();
        $this->dossierModel      = new DossierEtudiantModel();
        $this->anneeModel        = new AnneeModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

        public function index()
    {
        return $this->view('etudiants/liste', ["title" => "Etudiants"]);
    }

    // ─── Étudiants ────────────────────────────────────────────

    public function liste(): void
    {
        $filters = [
            'statut' => $_GET['statut'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];
        $data = $this->model->liste($this->etablissementCode, $filters);
        Response::success('Liste des étudiants.', $data);
    }

    public function ajouter(): void
    {
        $nom      = Validator::post('nom_etudiant');
        $prenom   = Validator::post('prenom_etudiant');
        $sexe     = Validator::post('sexe_etudiant');
        $dateNais  = Validator::post('date_naissance_etudiant');
        $lieuNais  = Validator::post('lieu_naissance_etudiant');
        $nationalite = Validator::post('nationalite_etudiant');
        $residence = Validator::post('lieu_residence_etudiant');
        $telephone = Validator::post('telephone_etudiant');
        $email     = Validator::post('email_etudiant');
        $cni       = Validator::post('numero_cni');

        $v = new Validator();
        $v->required('nom_etudiant',    $nom,    'Nom')
          ->required('prenom_etudiant', $prenom, 'Prénom')
          ->email('email_etudiant', $email, 'Email');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        $matricule = $this->model->generateMatricule($this->etablissementCode);

        $code = $this->model->create([
            'matricule_etudiant'         => $matricule,
            'nom_etudiant'               => $nom,
            'prenom_etudiant'            => $prenom,
            'date_naissance_etudiant'    => $dateNais,
            'lieu_naissance_etudiant'    => $lieuNais,
            'sexe_etudiant'              => $sexe,
            'nationalite_etudiant'       => $nationalite,
            'lieu_residence_etudiant'    => $residence,
            'telephone_etudiant'         => $telephone,
            'email_etudiant'             => $email,
            'numero_cni'                 => $cni,
            'etablissement_code'         => $this->etablissementCode,
            'user_code'                  => $this->userCode,
        ]);

        Response::success('Étudiant ajouté avec succès.', [
            'code_etudiant'     => $code,
            'matricule_etudiant' => $matricule,
        ], 201);
    }

    public function modifier(): void
    {
        $code     = Validator::post('code_etudiant');
        $nom      = Validator::post('nom_etudiant');
        $prenom   = Validator::post('prenom_etudiant');
        $sexe     = Validator::post('sexe_etudiant');
        $dateNais  = Validator::post('date_naissance_etudiant');
        $lieuNais  = Validator::post('lieu_naissance_etudiant');
        $nationalite = Validator::post('nationalite_etudiant');
        $residence = Validator::post('lieu_residence_etudiant');
        $telephone = Validator::post('telephone_etudiant');
        $email     = Validator::post('email_etudiant');
        $cni       = Validator::post('numero_cni');

        $v = new Validator();
        $v->required('code_etudiant',   $code,   'Code étudiant')
          ->required('nom_etudiant',    $nom,    'Nom')
          ->required('prenom_etudiant', $prenom, 'Prénom')
          ->email('email_etudiant', $email, 'Email');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Étudiant introuvable.');
        }

        $this->model->update($code, [
            'nom_etudiant'               => $nom,
            'prenom_etudiant'            => $prenom,
            'date_naissance_etudiant'    => $dateNais,
            'lieu_naissance_etudiant'    => $lieuNais,
            'sexe_etudiant'              => $sexe,
            'nationalite_etudiant'       => $nationalite,
            'lieu_residence_etudiant'    => $residence,
            'telephone_etudiant'         => $telephone,
            'email_etudiant'             => $email,
            'numero_cni'                 => $cni,
            'etablissement_code'         => $this->etablissementCode,
        ]);
        Response::success('Étudiant modifié avec succès.');
    }

    public function statut(): void
    {
        $code   = Validator::post('code_etudiant');
        $statut = Validator::post('statut_etudiant');

        $v = new Validator();
        $v->required('code_etudiant',   $code,   'Code')
          ->required('statut_etudiant', $statut, 'Statut')
          ->in('statut_etudiant', $statut, ['actif', 'inactif'], 'Statut');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Étudiant introuvable.');
        }
        $this->model->updateStatut($code, $statut, $this->etablissementCode);
        Response::success('Statut mis à jour.');
    }

    // ─── Parents ──────────────────────────────────────────────

    public function getParent(): void
    {
        $etudiantCode = $_GET['etudiant_code'] ?? '';
        if (!$etudiantCode) Response::error('Code étudiant requis.', 422);

        if (!$this->model->findByCode($etudiantCode, $this->etablissementCode)) {
            Response::notFound('Étudiant introuvable.');
        }

        $parent = $this->parentModel->findByEtudiant($etudiantCode, $this->etablissementCode);
        Response::success('Fiche parent.', $parent ?: []);
    }

    public function saveParent(): void
    {
        $etudiantCode = Validator::post('etudiant_code');

        if (!$etudiantCode) Response::error('Code étudiant requis.', 422);
        if (!$this->model->findByCode($etudiantCode, $this->etablissementCode)) {
            Response::notFound('Étudiant introuvable.');
        }

        $code = $this->parentModel->save($etudiantCode, [
            'nom_pere'           => Validator::post('nom_pere'),
            'telephone_pere'     => Validator::post('telephone_pere'),
            'profession_pere'    => Validator::post('profession_pere'),
            'nom_mere'           => Validator::post('nom_mere'),
            'telephone_mere'     => Validator::post('telephone_mere'),
            'profession_mere'    => Validator::post('profession_mere'),
            'nom_tuteur'         => Validator::post('nom_tuteur'),
            'telephone_tuteur'   => Validator::post('telephone_tuteur'),
            'user_code'          => $this->userCode,
            'etablissement_code' => $this->etablissementCode,
        ]);

        Response::success('Fiche parent enregistrée.', ['code_parent' => $code]);
    }

    // ─── Dossiers ─────────────────────────────────────────────

    public function getDossiers(): void
    {
        $etudiantCode = $_GET['etudiant_code'] ?? '';
        if (!$etudiantCode) Response::error('Code étudiant requis.', 422);

        if (!$this->model->findByCode($etudiantCode, $this->etablissementCode)) {
            Response::notFound('Étudiant introuvable.');
        }

        $dossiers = $this->dossierModel->listByEtudiant($etudiantCode, $this->etablissementCode);
        Response::success('Dossiers.', $dossiers);
    }

    public function ajouterDossier(): void
    {
        $etudiantCode = Validator::post('etudiant_code');
        $libelle      = Validator::post('libelle_dossier');
        $anneeId      = Validator::post('annee_id');

        $v = new Validator();
        $v->required('etudiant_code',   $etudiantCode, 'Étudiant')
          ->required('libelle_dossier', $libelle,      'Libellé du document');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($etudiantCode, $this->etablissementCode)) {
            Response::notFound('Étudiant introuvable.');
        }

        // Résoudre l'annee_code (libelle_annee)
        $anneeCode = '';
        if ($anneeId) {
            $annee = $this->anneeModel->findById((int) $anneeId, $this->etablissementCode);
            $anneeCode = $annee ? $annee['libelle_annee'] : '';
        }

        $code = $this->dossierModel->create([
            'etudiant_code'      => $etudiantCode,
            'libelle_dossier'    => $libelle,
            'annee_code'         => $anneeCode,
            'user_code'          => $this->userCode,
            'etablissement_code' => $this->etablissementCode,
        ]);

        Response::success('Document ajouté au dossier.', ['code_dossier_etudiant' => $code], 201);
    }

    public function supprimerDossier(): void
    {
        $code = Validator::post('code_dossier_etudiant');
        if (!$code) Response::error('Code dossier requis.', 422);

        if (!$this->dossierModel->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Document introuvable.');
        }

        $this->dossierModel->delete($code, $this->etablissementCode);
        Response::success('Document supprimé.');
    }
}
