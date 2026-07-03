<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\MainController;
use App\Models\EtablissementModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class EtablissementController extends MainController
{
    private EtablissementModel $model;

    public function __construct()
    {
        Auth::requireAuth();
        $this->model = new EtablissementModel();
    }


         public function index()
    {
        return $this->viewGuest('configurations/etablissements', ["title" => "Etablissement"]);
    }

    /**
     * GET /api/etablissements/liste
     */
    public function liste(): void
    {
        $data = $this->model->liste();
        Response::success('Liste des établissements.', $data);
    }

    /**
     * POST /api/etablissements/ajouter
     */
    public function ajouter(): void
    {
        $libelle   = Validator::post('libelle_etablissement');
        $adresse   = Validator::post('adresse_etablissement');
        $tel1      = Validator::post('telephone_etablissement');
        $tel2      = Validator::post('telephone_etablissement2');
        $email     = Validator::post('email_etablissement');
        $slogan    = Validator::post('slogan_etablissement');

        $v = new Validator();
        $v->required('libelle_etablissement', $libelle, 'Nom de l\'établissement')
          ->maxLength('libelle_etablissement', $libelle, 200, 'Nom de l\'établissement')
          ->email('email_etablissement', $email, 'Email');

        if ($v->fails()) {
            Response::error('Données invalides.', 422, $v->errors());
        }

        $code = $this->model->create([
            'libelle_etablissement'    => $libelle,
            'adresse_etablissement'    => $adresse,
            'telephone_etablissement'  => $tel1,
            'telephone_etablissement2' => $tel2,
            'email_etablissement'      => $email,
            'slogan_etablissement'     => $slogan,
        ]);

        Response::success('Établissement ajouté avec succès.', ['code_etablissement' => $code], 201);
    }

    /**
     * POST /api/etablissements/modifier
     */
    public function modifier(): void
    {
        $code    = Validator::post('code_etablissement');
        $libelle = Validator::post('libelle_etablissement');
        $adresse = Validator::post('adresse_etablissement');
        $tel1    = Validator::post('telephone_etablissement');
        $tel2    = Validator::post('telephone_etablissement2');
        $email   = Validator::post('email_etablissement');
        $slogan  = Validator::post('slogan_etablissement');

        $v = new Validator();
        $v->required('code_etablissement', $code, 'Code établissement')
          ->required('libelle_etablissement', $libelle, 'Nom de l\'établissement')
          ->email('email_etablissement', $email, 'Email');

        if ($v->fails()) {
            Response::error('Données invalides.', 422, $v->errors());
        }

        // Vérifier que l'établissement existe (intégrité référentielle manuelle)
        if (!$this->model->findByCode($code)) {
            Response::notFound('Établissement introuvable.');
        }

        $updated = $this->model->update($code, [
            'libelle_etablissement'    => $libelle,
            'adresse_etablissement'    => $adresse,
            'telephone_etablissement'  => $tel1,
            'telephone_etablissement2' => $tel2,
            'email_etablissement'      => $email,
            'slogan_etablissement'     => $slogan,
        ]);

        if (!$updated) {
            Response::error('Aucune modification effectuée.', 200);
        }

        Response::success('Établissement modifié avec succès.');
    }

    /**
     * POST /api/etablissements/statut
     */
    public function statut(): void
    {
        $code   = Validator::post('code_etablissement');
        $statut = Validator::post('statut_etablissement');

        $v = new Validator();
        $v->required('code_etablissement', $code, 'Code établissement')
          ->required('statut_etablissement', $statut, 'Statut')
          ->in('statut_etablissement', $statut, ['actif', 'inactif'], 'Statut');

        if ($v->fails()) {
            Response::error('Données invalides.', 422, $v->errors());
        }

        if (!$this->model->findByCode($code)) {
            Response::notFound('Établissement introuvable.');
        }

        $this->model->updateStatut($code, $statut);
        Response::success('Statut mis à jour avec succès.');
    }
}
