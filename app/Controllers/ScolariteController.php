<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ScolariteModel;
use App\Models\NiveauModel;
use App\Models\FiliereModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class ScolariteController
{
    private ScolariteModel $model;
    private NiveauModel    $niveauModel;
    private FiliereModel   $filiereModel;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        AuthService::requireAuth();
        $this->model             = new ScolariteModel();
        $this->niveauModel       = new NiveauModel();
        $this->filiereModel      = new FiliereModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

    public function liste(): void
    {
        $anneeCode = $_GET['annee_code'] ?? null;
        Response::success('Grille tarifaire.', $this->model->liste($this->etablissementCode, $anneeCode));
    }

    public function ajouter(): void
    {
        $montant     = Validator::post('montant_scolarite');
        $niveauCode  = Validator::post('niveau_code');
        $filiereCode = Validator::post('filiere_code');
        $anneeCode   = Validator::post('annee_code');

        $v = new Validator();
        $v->required('montant_scolarite', $montant,     'Montant')
          ->required('niveau_code',       $niveauCode,  'Niveau')
          ->required('filiere_code',      $filiereCode, 'Filière')
          ->required('annee_code',        $anneeCode,   'Année');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if ((float) $montant <= 0) {
            Response::error('Le montant doit être supérieur à 0.', 422);
        }

        // Vérifier intégrité référentielle
        if (!$this->niveauModel->findByCodeEtab($niveauCode, $this->etablissementCode)) {
            Response::notFound('Niveau introuvable.');
        }
        if (!$this->filiereModel->findByCode($filiereCode, $this->etablissementCode)) {
            Response::notFound('Filière introuvable.');
        }

        // Vérifier unicité (niveau + filière + année)
        if ($this->model->findByNiveauAnnee($niveauCode, $filiereCode, $anneeCode)) {
            Response::error('Un tarif existe déjà pour ce niveau, cette filière et cette année.', 409);
        }

        $code = $this->model->create([
            'montant_scolarite' => (float) $montant,
            'niveau_code'       => $niveauCode,
            'filiere_code'      => $filiereCode,
            'annee_code'        => $anneeCode,
            'user_code'         => $this->userCode,
        ]);
        Response::success('Tarif ajouté avec succès.', ['code_scolarite' => $code], 201);
    }

    public function modifier(): void
    {
        $code        = Validator::post('code_scolarite');
        $montant     = Validator::post('montant_scolarite');
        $niveauCode  = Validator::post('niveau_code');
        $filiereCode = Validator::post('filiere_code');
        $anneeCode   = Validator::post('annee_code');

        $v = new Validator();
        $v->required('code_scolarite',    $code,        'Code')
          ->required('montant_scolarite', $montant,     'Montant')
          ->required('niveau_code',       $niveauCode,  'Niveau')
          ->required('filiere_code',      $filiereCode, 'Filière')
          ->required('annee_code',        $anneeCode,   'Année');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code)) {
            Response::notFound('Tarif introuvable.');
        }

        $this->model->update($code, [
            'montant_scolarite' => (float) $montant,
            'niveau_code'       => $niveauCode,
            'filiere_code'      => $filiereCode,
            'annee_code'        => $anneeCode,
        ]);
        Response::success('Tarif modifié avec succès.');
    }

    public function supprimer(): void
    {
        $code = Validator::post('code_scolarite');
        if (!$code) Response::error('Code requis.', 422);
        if (!$this->model->findByCode($code)) Response::notFound('Tarif introuvable.');
        $this->model->delete($code);
        Response::success('Tarif supprimé.');
    }
}
