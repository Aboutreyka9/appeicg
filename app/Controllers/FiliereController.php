<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\FiliereModel;
use App\Models\CycleModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class FiliereController
{
    private FiliereModel $model;
    private CycleModel   $cycleModel;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        AuthService::requireAuth();
        $this->model             = new FiliereModel();
        $this->cycleModel        = new CycleModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

    public function liste(): void
    {
        $cycleCode = $_GET['cycle_code'] ?? null;
        Response::success('Liste des filières.', $this->model->liste($this->etablissementCode, $cycleCode));
    }

    public function ajouter(): void
    {
        $libelle     = Validator::post('libelle_filiere');
        $cycleCode   = Validator::post('cycle_code');
        $description = Validator::post('description_filiere');

        $v = new Validator();
        $v->required('libelle_filiere', $libelle,   'Libellé de la filière')
          ->required('cycle_code',      $cycleCode, 'Cycle');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->cycleModel->findByCode($cycleCode, $this->etablissementCode)) {
            Response::error('Cycle introuvable.', 404);
        }
        if ($this->model->libelleExists($libelle, $cycleCode, $this->etablissementCode)) {
            Response::error('Une filière avec ce libellé existe déjà dans ce cycle.', 409);
        }

        $code = $this->model->create([
            'libelle_filiere'     => $libelle,
            'description_filiere' => $description,
            'cycle_code'          => $cycleCode,
            'etablissement_code'  => $this->etablissementCode,
            'user_code'           => $this->userCode,
        ]);
        Response::success('Filière ajoutée avec succès.', ['code_filiere' => $code], 201);
    }

    public function modifier(): void
    {
        $code        = Validator::post('code_filiere');
        $libelle     = Validator::post('libelle_filiere');
        $cycleCode   = Validator::post('cycle_code');
        $description = Validator::post('description_filiere');

        $v = new Validator();
        $v->required('code_filiere',    $code,      'Code filière')
          ->required('libelle_filiere', $libelle,   'Libellé')
          ->required('cycle_code',      $cycleCode, 'Cycle');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Filière introuvable.');
        }
        if (!$this->cycleModel->findByCode($cycleCode, $this->etablissementCode)) {
            Response::error('Cycle introuvable.', 404);
        }
        if ($this->model->libelleExists($libelle, $cycleCode, $this->etablissementCode, $code)) {
            Response::error('Une autre filière avec ce libellé existe déjà dans ce cycle.', 409);
        }

        $this->model->update($code, [
            'libelle_filiere'     => $libelle,
            'description_filiere' => $description,
            'cycle_code'          => $cycleCode,
            'etablissement_code'  => $this->etablissementCode,
        ]);
        Response::success('Filière modifiée avec succès.');
    }

    public function statut(): void
    {
        $code   = Validator::post('code_filiere');
        $statut = Validator::post('statut_filiere');

        $v = new Validator();
        $v->required('code_filiere',    $code,   'Code filière')
          ->required('statut_filiere',  $statut, 'Statut')
          ->in('statut_filiere', $statut, ['actif', 'inactif'], 'Statut');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Filière introuvable.');
        }
        $this->model->updateStatut($code, $statut, $this->etablissementCode);
        Response::success('Statut mis à jour.');
    }
}
