<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\MainController;
use App\Models\MatiereModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class MatiereController extends MainController
{
    private MatiereModel $model;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        // AuthService::requireAuth();
        $this->model             = new MatiereModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

             public function index()
    {
        return $this->viewGuest('academiques/matieres', ["title" => "Matiere"]);
    }

    public function liste(): void
    {
        Response::success('Liste des matières.', $this->model->liste($this->etablissementCode));
    }

    public function ajouter(): void
    {
        $libelle = Validator::post('libelle_matiere');

        $v = new Validator();
        $v->required('libelle_matiere', $libelle, 'Libellé de la matière')
          ->maxLength('libelle_matiere', $libelle, 150, 'Libellé');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if ($this->model->libelleExists($libelle, $this->etablissementCode)) {
            Response::error('Une matière avec ce libellé existe déjà.', 409);
        }

        $code = $this->model->create([
            'libelle_matiere'    => $libelle,
            'etablissement_code' => $this->etablissementCode,
            'user_code'          => $this->userCode,
        ]);
        Response::success('Matière ajoutée avec succès.', ['code_matiere' => $code], 201);
    }

    public function modifier(): void
    {
        $code    = Validator::post('code_matiere');
        $libelle = Validator::post('libelle_matiere');

        $v = new Validator();
        $v->required('code_matiere',    $code,    'Code matière')
          ->required('libelle_matiere', $libelle, 'Libellé');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Matière introuvable.');
        }
        if ($this->model->libelleExists($libelle, $this->etablissementCode, $code)) {
            Response::error('Une autre matière avec ce libellé existe déjà.', 409);
        }

        $this->model->update($code, [
            'libelle_matiere'    => $libelle,
            'etablissement_code' => $this->etablissementCode,
        ]);
        Response::success('Matière modifiée avec succès.');
    }

    public function statut(): void
    {
        $code   = Validator::post('code_matiere');
        $statut = Validator::post('statut_matiere');

        $v = new Validator();
        $v->required('code_matiere',   $code,   'Code matière')
          ->required('statut_matiere', $statut, 'Statut')
          ->in('statut_matiere', $statut, ['actif', 'inactif'], 'Statut');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Matière introuvable.');
        }
        $this->model->updateStatut($code, $statut, $this->etablissementCode);
        Response::success('Statut mis à jour.');
    }
}
