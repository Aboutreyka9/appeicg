<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SalleModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class SalleController
{
    private SalleModel $model;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        // AuthService::requireAuth();
        $this->model             = new SalleModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

    public function liste(): void
    {
        Response::success('Liste des salles.', $this->model->liste($this->etablissementCode));
    }

    public function ajouter(): void
    {
        $libelle = Validator::post('libelle_salle');

        $v = new Validator();
        $v->required('libelle_salle', $libelle, 'Libellé de la salle')
          ->maxLength('libelle_salle', $libelle, 100, 'Libellé de la salle');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if ($this->model->libelleExists($libelle, $this->etablissementCode)) {
            Response::error('Une salle avec ce libellé existe déjà.', 409);
        }

        $code = $this->model->create([
            'libelle_salle'      => $libelle,
            'etablissement_code' => $this->etablissementCode,
            'user_code'          => $this->userCode,
        ]);
        Response::success('Salle ajoutée avec succès.', ['code_salle' => $code], 201);
    }

    public function modifier(): void
    {
        $code    = Validator::post('code_salle');
        $libelle = Validator::post('libelle_salle');

        $v = new Validator();
        $v->required('code_salle',    $code,    'Code salle')
          ->required('libelle_salle', $libelle, 'Libellé');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Salle introuvable.');
        }
        if ($this->model->libelleExists($libelle, $this->etablissementCode, $code)) {
            Response::error('Une autre salle avec ce libellé existe déjà.', 409);
        }

        $this->model->update($code, [
            'libelle_salle'      => $libelle,
            'etablissement_code' => $this->etablissementCode,
        ]);
        Response::success('Salle modifiée avec succès.');
    }

    public function statut(): void
    {
        $code   = Validator::post('code_salle');
        $statut = Validator::post('statut_salle');

        $v = new Validator();
        $v->required('code_salle',   $code,   'Code salle')
          ->required('statut_salle', $statut, 'Statut')
          ->in('statut_salle', $statut, ['actif', 'inactif'], 'Statut');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Salle introuvable.');
        }
        $this->model->updateStatut($code, $statut, $this->etablissementCode);
        Response::success('Statut mis à jour.');
    }
}
