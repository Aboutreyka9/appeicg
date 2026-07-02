<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CycleModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class CycleController
{
    private CycleModel $model;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        AuthService::requireAuth();
        $this->model             = new CycleModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

    public function liste(): void
    {
        Response::success('Liste des cycles.', $this->model->liste($this->etablissementCode));
    }

    public function ajouter(): void
    {
        $libelle = Validator::post('libelle_cycle');

        $v = new Validator();
        $v->required('libelle_cycle', $libelle, 'Libellé du cycle')
          ->maxLength('libelle_cycle', $libelle, 100, 'Libellé du cycle');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if ($this->model->libelleExists($libelle, $this->etablissementCode)) {
            Response::error('Un cycle avec ce libellé existe déjà.', 409);
        }

        $code = $this->model->create([
            'libelle_cycle'      => $libelle,
            'etablissement_code' => $this->etablissementCode,
            'user_code'          => $this->userCode,
        ]);
        Response::success('Cycle ajouté avec succès.', ['code_cycle' => $code], 201);
    }

    public function modifier(): void
    {
        $code    = Validator::post('code_cycle');
        $libelle = Validator::post('libelle_cycle');

        $v = new Validator();
        $v->required('code_cycle',    $code,    'Code cycle')
          ->required('libelle_cycle', $libelle, 'Libellé du cycle');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Cycle introuvable.');
        }
        if ($this->model->libelleExists($libelle, $this->etablissementCode, $code)) {
            Response::error('Un autre cycle avec ce libellé existe déjà.', 409);
        }

        $this->model->update($code, [
            'libelle_cycle'      => $libelle,
            'etablissement_code' => $this->etablissementCode,
        ]);
        Response::success('Cycle modifié avec succès.');
    }

    public function statut(): void
    {
        $code   = Validator::post('code_cycle');
        $statut = Validator::post('statut_cycle');

        $v = new Validator();
        $v->required('code_cycle',   $code,   'Code cycle')
          ->required('statut_cycle', $statut, 'Statut')
          ->in('statut_cycle', $statut, ['actif', 'inactif'], 'Statut');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Cycle introuvable.');
        }
        $this->model->updateStatut($code, $statut, $this->etablissementCode);
        Response::success('Statut mis à jour.');
    }
}
