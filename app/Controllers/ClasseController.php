<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ClasseModel;
use App\Models\NiveauModel;
use App\Models\AnneeModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class ClasseController
{
    private ClasseModel $model;
    private NiveauModel $niveauModel;
    private AnneeModel  $anneeModel;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        AuthService::requireAuth();
        $this->model             = new ClasseModel();
        $this->niveauModel       = new NiveauModel();
        $this->anneeModel        = new AnneeModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

    public function liste(): void
    {
        $anneeCode  = $_GET['annee_code']  ?? null;
        $niveauCode = $_GET['niveau_code'] ?? null;
        $data = $this->model->liste($this->etablissementCode, $anneeCode, $niveauCode);

        // Ajouter le nombre d'étudiants pour chaque classe
        foreach ($data as &$classe) {
            $classe['nb_etudiants'] = $this->model->countEtudiants($classe['code_classe']);
        }
        Response::success('Liste des classes.', $data);
    }

    public function ajouter(): void
    {
        $libelle   = Validator::post('libelle_classe');
        $niveauCode = Validator::post('niveau_code');
        $anneeCode  = Validator::post('annee_code');
        $capacite   = Validator::post('capacite_max_classe');

        $v = new Validator();
        $v->required('libelle_classe', $libelle,    'Libellé de la classe')
          ->required('niveau_code',    $niveauCode, 'Niveau')
          ->required('annee_code',     $anneeCode,  'Année scolaire');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        // Vérifier niveau et année (intégrité référentielle manuelle)
        if (!$this->niveauModel->findByCodeEtab($niveauCode, $this->etablissementCode)) {
            Response::error('Niveau introuvable.', 404);
        }
        if (!$this->anneeModel->findById((int) $anneeCode, $this->etablissementCode)) {
            Response::error('Année scolaire introuvable.', 404);
        }

        // Récupérer le libelle_annee comme annee_code
        $annee     = $this->anneeModel->findById((int) $anneeCode, $this->etablissementCode);
        $anneeLibelle = $annee['libelle_annee'];

        if ($this->model->libelleExists($libelle, $niveauCode, $anneeLibelle, $this->etablissementCode)) {
            Response::error('Une classe avec ce libellé existe déjà pour ce niveau et cette année.', 409);
        }

        $code = $this->model->create([
            'libelle_classe'      => $libelle,
            'capacite_max_classe' => $capacite ? (int) $capacite : null,
            'niveau_code'         => $niveauCode,
            'annee_code'          => $anneeLibelle,
            'etablissement_code'  => $this->etablissementCode,
            'user_code'           => $this->userCode,
        ]);
        Response::success('Classe ajoutée avec succès.', ['code_classe' => $code], 201);
    }

    public function modifier(): void
    {
        $code       = Validator::post('code_classe');
        $libelle    = Validator::post('libelle_classe');
        $niveauCode = Validator::post('niveau_code');
        $anneeCode  = Validator::post('annee_code');
        $capacite   = Validator::post('capacite_max_classe');

        $v = new Validator();
        $v->required('code_classe',    $code,       'Code classe')
          ->required('libelle_classe', $libelle,    'Libellé')
          ->required('niveau_code',    $niveauCode, 'Niveau')
          ->required('annee_code',     $anneeCode,  'Année');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Classe introuvable.');
        }
        if (!$this->niveauModel->findByCodeEtab($niveauCode, $this->etablissementCode)) {
            Response::error('Niveau introuvable.', 404);
        }

        $annee = $this->anneeModel->findById((int) $anneeCode, $this->etablissementCode);
        if (!$annee) Response::error('Année scolaire introuvable.', 404);
        $anneeLibelle = $annee['libelle_annee'];

        if ($this->model->libelleExists($libelle, $niveauCode, $anneeLibelle, $this->etablissementCode, $code)) {
            Response::error('Une autre classe avec ce libellé existe déjà.', 409);
        }

        $this->model->update($code, [
            'libelle_classe'      => $libelle,
            'capacite_max_classe' => $capacite ? (int) $capacite : null,
            'niveau_code'         => $niveauCode,
            'annee_code'          => $anneeLibelle,
            'etablissement_code'  => $this->etablissementCode,
        ]);
        Response::success('Classe modifiée avec succès.');
    }

    public function statut(): void
    {
        $code   = Validator::post('code_classe');
        $statut = Validator::post('statut_classe');

        $v = new Validator();
        $v->required('code_classe',   $code,   'Code classe')
          ->required('statut_classe', $statut, 'Statut')
          ->in('statut_classe', $statut, ['actif', 'inactif'], 'Statut');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Classe introuvable.');
        }
        $this->model->updateStatut($code, $statut, $this->etablissementCode);
        Response::success('Statut mis à jour.');
    }
}
