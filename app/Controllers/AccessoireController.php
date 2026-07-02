<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AccessoireModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class AccessoireController
{
    private AccessoireModel $model;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        AuthService::requireAuth();
        $this->model             = new AccessoireModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

    public function liste(): void
    {
        Response::success('Liste des accessoires.', $this->model->liste($this->etablissementCode));
    }

    public function ajouter(): void
    {
        $libelle = Validator::post('libelle_accessoire');
        $v = new Validator();
        $v->required('libelle_accessoire', $libelle, 'Libellé');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if ($this->model->libelleExists($libelle, $this->etablissementCode)) {
            Response::error('Un accessoire avec ce libellé existe déjà.', 409);
        }

        $code = $this->model->create([
            'libelle_accessoire' => $libelle,
            'etablissement_code' => $this->etablissementCode,
            'user_code'          => $this->userCode,
        ]);
        Response::success('Accessoire ajouté.', ['code_accessoire' => $code], 201);
    }

    public function modifier(): void
    {
        $code    = Validator::post('code_accessoire');
        $libelle = Validator::post('libelle_accessoire');

        $v = new Validator();
        $v->required('code_accessoire',    $code,    'Code')
          ->required('libelle_accessoire', $libelle, 'Libellé');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Accessoire introuvable.');
        }
        if ($this->model->libelleExists($libelle, $this->etablissementCode, $code)) {
            Response::error('Un autre accessoire avec ce libellé existe déjà.', 409);
        }

        $this->model->update($code, ['libelle_accessoire' => $libelle, 'etablissement_code' => $this->etablissementCode]);
        Response::success('Accessoire modifié.');
    }

    public function statut(): void
    {
        $code   = Validator::post('code_accessoire');
        $statut = Validator::post('statut_accessoire');

        $v = new Validator();
        $v->required('code_accessoire',   $code,   'Code')
          ->required('statut_accessoire', $statut, 'Statut')
          ->in('statut_accessoire', $statut, ['actif', 'inactif'], 'Statut');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Accessoire introuvable.');
        }
        $this->model->updateStatut($code, $statut, $this->etablissementCode);
        Response::success('Statut mis à jour.');
    }
}
