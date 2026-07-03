<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\MainController;
use App\Models\AnneeModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class AnneeController extends MainController
{
    private AnneeModel $model;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        // AuthService::requireAuth();
        $this->model             = new AnneeModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

    public function index()
    {
        return $this->viewGuest('configurations/annees', ["title" => "annee"]);
    }

    /**
     * GET /api/annees/liste
     */
    public function liste(): void
    {
        $data = $this->model->liste($this->etablissementCode);
        Response::success('Liste des années scolaires.', $data);
    }

    /**
     * POST /api/annees/ajouter
     */
    public function ajouter(): void
    {
        $libelle    = Validator::post('libelle_annee');
        $dateDebut  = Validator::post('date_debut_annee');
        $dateFin    = Validator::post('date_fin_annee');

        $v = new Validator();
        $v->required('libelle_annee',    $libelle,   'Libellé de l\'année')
          ->required('date_debut_annee', $dateDebut, 'Date de début')
          ->required('date_fin_annee',   $dateFin,   'Date de fin');

        if ($v->fails()) {
            Response::error('Données invalides.', 422, $v->errors());
        }

        // Vérifier cohérence des dates
        if ($dateDebut >= $dateFin) {
            Response::error('La date de fin doit être postérieure à la date de début.', 422);
        }

        // Vérifier l'unicité du libellé pour cet établissement
        if ($this->model->findByLibelle($libelle, $this->etablissementCode)) {
            Response::error('Une année scolaire avec ce libellé existe déjà.', 409);
        }

        $this->model->create([
            'libelle_annee'    => $libelle,
            'date_debut_annee' => $dateDebut,
            'date_fin_annee'   => $dateFin,
            'etablissement_code' => $this->etablissementCode,
            'user_code'          => $this->userCode,
        ]);

        $annee = $this->model->getLastInsertedLibelle($this->etablissementCode);
        Response::success('Année scolaire ajoutée avec succès.', $annee ?: [], 201);
    }

    /**
     * POST /api/annees/modifier
     */
    public function modifier(): void
    {
        $id        = (int) (Validator::post('id_annee') ?? 0);
        $libelle   = Validator::post('libelle_annee');
        $dateDebut = Validator::post('date_debut_annee');
        $dateFin   = Validator::post('date_fin_annee');

        $v = new Validator();
        $v->required('id_annee',        (string) $id, 'Identifiant')
          ->required('libelle_annee',    $libelle,     'Libellé')
          ->required('date_debut_annee', $dateDebut,   'Date de début')
          ->required('date_fin_annee',   $dateFin,     'Date de fin');

        if ($v->fails()) {
            Response::error('Données invalides.', 422, $v->errors());
        }

        if ($dateDebut >= $dateFin) {
            Response::error('La date de fin doit être postérieure à la date de début.', 422);
        }

        $annee = $this->model->findById($id, $this->etablissementCode);
        if (!$annee) {
            Response::notFound('Année scolaire introuvable.');
        }

        // Vérifier l'unicité du libellé (en excluant l'année courante)
        if ($this->model->findByLibelle($libelle, $this->etablissementCode, $annee['libelle_annee'])) {
            Response::error('Une autre année scolaire avec ce libellé existe déjà.', 409);
        }

        $this->model->update($id, [
            'libelle_annee'      => $libelle,
            'date_debut_annee'   => $dateDebut,
            'date_fin_annee'     => $dateFin,
            'etablissement_code' => $this->etablissementCode,
        ]);

        Response::success('Année scolaire modifiée avec succès.');
    }

    /**
     * POST /api/annees/statut
     */
    public function statut(): void
    {
        $id     = (int) (Validator::post('id_annee') ?? 0);
        $statut = Validator::post('statut_annee');

        $v = new Validator();
        $v->required('id_annee',    (string) $id, 'Identifiant')
          ->required('statut_annee', $statut,      'Statut')
          ->in('statut_annee', $statut, ['actif', 'inactif'], 'Statut');

        if ($v->fails()) {
            Response::error('Données invalides.', 422, $v->errors());
        }

        if (!$this->model->findById($id, $this->etablissementCode)) {
            Response::notFound('Année scolaire introuvable.');
        }

        $this->model->updateStatut($id, $statut, $this->etablissementCode);
        Response::success('Statut mis à jour avec succès.');
    }
}
