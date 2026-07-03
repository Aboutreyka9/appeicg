<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\MainController;
use App\Models\EnseignantModel;
use App\Models\MatiereModel;
use App\Models\EnseignantMatiereModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class EnseignantController extends MainController
{
    private EnseignantModel        $model;
    private MatiereModel           $matiereModel;
    private EnseignantMatiereModel $affModel;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        // AuthService::requireAuth();
        $this->model             = new EnseignantModel();
        $this->matiereModel      = new MatiereModel();
        $this->affModel          = new EnseignantMatiereModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

    public function index()
    {
        return $this->viewGuest('enseignants/liste', ["title" => "Enseignants"]);
    }

    public function liste(): void
    {
        Response::success('Liste des enseignants.', $this->model->liste($this->etablissementCode));
    }

    public function ajouter(): void
    {
        $nom           = Validator::post('nom_enseignant');
        $telephone     = Validator::post('telephone');
        $email         = Validator::post('email');
        $sexe          = Validator::post('sexe');
        $dateNaissance = Validator::post('date_naissance');
        $lieuNaissance = Validator::post('lieu_naissance');

        $v = new Validator();
        $v->required('nom_enseignant', $nom,       'Nom complet')
          ->required('telephone',      $telephone, 'Téléphone')
          ->email('email', $email, 'Email');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        // Générer matricule unique
        $matricule = $this->model->generateMatricule($this->etablissementCode);

        $code = $this->model->create([
            'matricule'          => $matricule,
            'nom_enseignant'     => $nom,
            'telephone'          => $telephone,
            'email'              => $email,
            'sexe'               => $sexe,
            'date_naissance'     => $dateNaissance,
            'lieu_naissance'     => $lieuNaissance,
            'etablissement_code' => $this->etablissementCode,
            'user_code'          => $this->userCode,
        ]);

        Response::success('Enseignant ajouté avec succès.', [
            'code_enseignant' => $code,
            'matricule'       => $matricule,
        ], 201);
    }

    public function modifier(): void
    {
        $code          = Validator::post('code_enseignant');
        $nom           = Validator::post('nom_enseignant');
        $telephone     = Validator::post('telephone');
        $email         = Validator::post('email');
        $sexe          = Validator::post('sexe');
        $dateNaissance = Validator::post('date_naissance');
        $lieuNaissance = Validator::post('lieu_naissance');

        $v = new Validator();
        $v->required('code_enseignant', $code,      'Code enseignant')
          ->required('nom_enseignant',  $nom,       'Nom complet')
          ->required('telephone',       $telephone, 'Téléphone')
          ->email('email', $email, 'Email');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Enseignant introuvable.');
        }

        $this->model->update($code, [
            'nom_enseignant'     => $nom,
            'telephone'          => $telephone,
            'email'              => $email,
            'sexe'               => $sexe,
            'date_naissance'     => $dateNaissance,
            'lieu_naissance'     => $lieuNaissance,
            'etablissement_code' => $this->etablissementCode,
        ]);
        Response::success('Enseignant modifié avec succès.');
    }

    public function statut(): void
    {
        $code   = Validator::post('code_enseignant');
        $statut = Validator::post('statut_enseignant');

        $v = new Validator();
        $v->required('code_enseignant',   $code,   'Code')
          ->required('statut_enseignant', $statut, 'Statut')
          ->in('statut_enseignant', $statut, ['actif', 'inactif'], 'Statut');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Enseignant introuvable.');
        }
        $this->model->updateStatut($code, $statut, $this->etablissementCode);
        Response::success('Statut mis à jour.');
    }

    // ─── Affectation matières ─────────────────────────────────

    public function matieres(): void
    {
        $code = $_GET['enseignant_code'] ?? '';
        if (!$code) Response::error('Code enseignant requis.', 422);

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Enseignant introuvable.');
        }

        $affectations = $this->affModel->listByEnseignant($code, $this->etablissementCode);
        Response::success('Matières de l\'enseignant.', $affectations);
    }

    public function affecter(): void
    {
        $ensCode  = Validator::post('enseignant_code');
        $matCode  = Validator::post('matiere_code');

        $v = new Validator();
        $v->required('enseignant_code', $ensCode, 'Enseignant')
          ->required('matiere_code',    $matCode, 'Matière');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($ensCode, $this->etablissementCode)) {
            Response::notFound('Enseignant introuvable.');
        }
        if (!$this->matiereModel->findByCode($matCode, $this->etablissementCode)) {
            Response::notFound('Matière introuvable.');
        }

        $ok = $this->affModel->affecter($ensCode, $matCode, $this->etablissementCode);
        if (!$ok) {
            Response::error('Cette matière est déjà affectée à cet enseignant.', 409);
        }
        Response::success('Matière affectée avec succès.');
    }

    public function retirer(): void
    {
        $ensCode = Validator::post('enseignant_code');
        $matCode = Validator::post('matiere_code');

        $v = new Validator();
        $v->required('enseignant_code', $ensCode, 'Enseignant')
          ->required('matiere_code',    $matCode, 'Matière');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        $this->affModel->retirer($ensCode, $matCode, $this->etablissementCode);
        Response::success('Matière retirée avec succès.');
    }
}
