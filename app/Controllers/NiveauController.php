<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\NiveauModel;
use App\Models\FiliereModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class NiveauController
{
    private NiveauModel   $model;
    private FiliereModel  $filiereModel;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        AuthService::requireAuth();
        $this->model             = new NiveauModel();
        $this->filiereModel      = new FiliereModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

    public function liste(): void
    {
        $filiereCode = $_GET['filiere_code'] ?? null;
        Response::success('Liste des niveaux.', $this->model->liste($this->etablissementCode, $filiereCode));
    }

    public function ajouter(): void
    {
        $libelle     = Validator::post('libelle_niveau');
        $filiereCode = Validator::post('filiere_code');

        $v = new Validator();
        $v->required('libelle_niveau', $libelle,     'Libellé du niveau')
          ->required('filiere_code',   $filiereCode, 'Filière');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->filiereModel->findByCode($filiereCode, $this->etablissementCode)) {
            Response::error('Filière introuvable.', 404);
        }
        if ($this->model->libelleExists($libelle, $filiereCode, $this->etablissementCode)) {
            Response::error('Un niveau avec ce libellé existe déjà dans cette filière.', 409);
        }

        $code = $this->model->create([
            'libelle_niveau'     => $libelle,
            'filiere_code'       => $filiereCode,
            'etablissement_code' => $this->etablissementCode,
        ]);
        Response::success('Niveau ajouté avec succès.', ['code_niveau' => $code], 201);
    }

    public function modifier(): void
    {
        $code        = Validator::post('code_niveau');
        $libelle     = Validator::post('libelle_niveau');
        $filiereCode = Validator::post('filiere_code');

        $v = new Validator();
        $v->required('code_niveau',    $code,        'Code niveau')
          ->required('libelle_niveau', $libelle,     'Libellé')
          ->required('filiere_code',   $filiereCode, 'Filière');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCodeEtab($code, $this->etablissementCode)) {
            Response::notFound('Niveau introuvable.');
        }
        if (!$this->filiereModel->findByCode($filiereCode, $this->etablissementCode)) {
            Response::error('Filière introuvable.', 404);
        }
        if ($this->model->libelleExists($libelle, $filiereCode, $this->etablissementCode, $code)) {
            Response::error('Un autre niveau avec ce libellé existe déjà dans cette filière.', 409);
        }

        $this->model->update($code, [
            'libelle_niveau'     => $libelle,
            'filiere_code'       => $filiereCode,
            'etablissement_code' => $this->etablissementCode,
        ]);
        Response::success('Niveau modifié avec succès.');
    }

    public function statut(): void
    {
        $code   = Validator::post('code_niveau');
        $statut = Validator::post('statut_niveau');

        $v = new Validator();
        $v->required('code_niveau',   $code,   'Code niveau')
          ->required('statut_niveau', $statut, 'Statut')
          ->in('statut_niveau', $statut, ['actif', 'inactif'], 'Statut');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCodeEtab($code, $this->etablissementCode)) {
            Response::notFound('Niveau introuvable.');
        }
        $this->model->updateStatut($code, $statut, $this->etablissementCode);
        Response::success('Statut mis à jour.');
    }
}
