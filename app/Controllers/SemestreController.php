<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SemestreModel;
use App\Models\AnneeModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class SemestreController
{
    private SemestreModel $model;
    private AnneeModel    $anneeModel;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        AuthService::requireAuth();
        $this->model             = new SemestreModel();
        $this->anneeModel        = new AnneeModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

    /**
     * GET /api/semestres/liste?annee_code=XXX
     */
    public function liste(): void
    {
        $anneeCode = $_GET['annee_code'] ?? '';
        if (empty($anneeCode)) {
            Response::error('Le paramètre annee_code est requis.', 422);
        }

        $data = $this->model->liste($anneeCode, $this->etablissementCode);
        Response::success('Liste des semestres.', $data);
    }

    /**
     * POST /api/semestres/ajouter
     */
    public function ajouter(): void
    {
        $libelle    = Validator::post('libelle_semestre');
        $anneeCode  = Validator::post('annee_code');
        $dateDebut  = Validator::post('date_debut_semestre');
        $dateFin    = Validator::post('date_fin_semestre');

        $v = new Validator();
        $v->required('libelle_semestre', $libelle,   'Libellé du semestre')
          ->required('annee_code',       $anneeCode, 'Année scolaire');

        if ($v->fails()) {
            Response::error('Données invalides.', 422, $v->errors());
        }

        // Vérifier que l'année existe (intégrité référentielle manuelle)
        if (!$this->anneeModel->findById((int) $anneeCode, $this->etablissementCode)) {
            // annee_code peut être le libelle ou l'id — on gère les deux
            // Dans la DB, annee_code dans semestres stocke le libelle_annee
            // Mais ici on reçoit l'id_annee et on récupère le libelle
        }

        // Récupérer le libelle de l'année pour le stocker comme annee_code
        $annee = $this->anneeModel->findById((int) $anneeCode, $this->etablissementCode);
        if (!$annee) {
            Response::notFound('Année scolaire introuvable.');
        }
        $anneeLibelle = $annee['libelle_annee'];

        // Vérifier l'unicité du libellé dans cette année
        if ($this->model->libelleExists($libelle, $anneeLibelle, $this->etablissementCode)) {
            Response::error('Un semestre avec ce libellé existe déjà pour cette année.', 409);
        }

        // Vérifier cohérence des dates si fournies
        if ($dateDebut && $dateFin && $dateDebut >= $dateFin) {
            Response::error('La date de fin doit être postérieure à la date de début.', 422);
        }

        $code = $this->model->create([
            'libelle_semestre'    => $libelle,
            'annee_code'          => $anneeLibelle,
            'etablissement_code'  => $this->etablissementCode,
            'date_debut_semestre' => $dateDebut,
            'date_fin_semestre'   => $dateFin,
            'user_code'           => $this->userCode,
        ]);

        Response::success('Semestre ajouté avec succès.', ['code_semestre' => $code], 201);
    }

    /**
     * POST /api/semestres/modifier
     */
    public function modifier(): void
    {
        $code      = Validator::post('code_semestre');
        $libelle   = Validator::post('libelle_semestre');
        $dateDebut = Validator::post('date_debut_semestre');
        $dateFin   = Validator::post('date_fin_semestre');

        $v = new Validator();
        $v->required('code_semestre',    $code,    'Code semestre')
          ->required('libelle_semestre', $libelle, 'Libellé');

        if ($v->fails()) {
            Response::error('Données invalides.', 422, $v->errors());
        }

        $semestre = $this->model->findByCode($code);
        if (!$semestre || $semestre['etablissement_code'] !== $this->etablissementCode) {
            Response::notFound('Semestre introuvable.');
        }

        if ($dateDebut && $dateFin && $dateDebut >= $dateFin) {
            Response::error('La date de fin doit être postérieure à la date de début.', 422);
        }

        // Unicité du libellé (sauf le semestre courant)
        if ($this->model->libelleExists($libelle, $semestre['annee_code'], $this->etablissementCode, $code)) {
            Response::error('Un autre semestre avec ce libellé existe déjà pour cette année.', 409);
        }

        $this->model->update($code, [
            'libelle_semestre'    => $libelle,
            'date_debut_semestre' => $dateDebut,
            'date_fin_semestre'   => $dateFin,
            'etablissement_code'  => $this->etablissementCode,
        ]);

        Response::success('Semestre modifié avec succès.');
    }

    /**
     * POST /api/semestres/supprimer
     */
    public function supprimer(): void
    {
        $code = Validator::post('code_semestre');

        if (!$code) {
            Response::error('Le code du semestre est requis.', 422);
        }

        $semestre = $this->model->findByCode($code);
        if (!$semestre || $semestre['etablissement_code'] !== $this->etablissementCode) {
            Response::notFound('Semestre introuvable.');
        }

        // Intégrité référentielle manuelle : vérifier les notes liées
        if ($this->model->hasNotes($code)) {
            Response::error('Impossible de supprimer ce semestre : des notes y sont rattachées.', 409);
        }

        $this->model->delete($code, $this->etablissementCode);
        Response::success('Semestre supprimé avec succès.');
    }
}
