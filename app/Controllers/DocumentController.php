<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\DocumentModel;
use App\Models\FiliereModel;
use App\Models\NiveauModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class DocumentController
{
    private DocumentModel $model;
    private FiliereModel  $filiereModel;
    private NiveauModel   $niveauModel;
    private string $etablissementCode;

    public function __construct()
    {
        AuthService::requireAuth();
        $this->model             = new DocumentModel();
        $this->filiereModel      = new FiliereModel();
        $this->niveauModel       = new NiveauModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
    }

    public function liste(): void
    {
        $filters = [
            'filiere_code' => $_GET['filiere_code'] ?? '',
            'niveaux_code' => $_GET['niveaux_code'] ?? '',
            'annee_code'   => $_GET['annee_code']   ?? '',
        ];
        Response::success('Documents.', $this->model->liste($this->etablissementCode, $filters));
    }

    public function ajouter(): void
    {
        $libelle     = Validator::post('libelle_document');
        $lien        = Validator::post('lien_document');
        $filiereCode = Validator::post('filiere_code');
        $niveauxCode = Validator::post('niveaux_code');
        $anneeCode   = Validator::post('annee_code');

        $v = new Validator();
        $v->required('libelle_document', $libelle,     'Libellé du document')
          ->required('lien_document',    $lien,        'Lien du document')
          ->required('filiere_code',     $filiereCode, 'Filière')
          ->required('niveaux_code',     $niveauxCode, 'Niveau')
          ->required('annee_code',       $anneeCode,   'Année');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->filiereModel->findByCode($filiereCode, $this->etablissementCode)) {
            Response::notFound('Filière introuvable.');
        }
        if (!$this->niveauModel->findByCodeEtab($niveauxCode, $this->etablissementCode)) {
            Response::notFound('Niveau introuvable.');
        }

        $id = $this->model->create([
            'libelle_document'   => $libelle,
            'lien_document'      => $lien,
            'filiere_code'       => $filiereCode,
            'niveaux_code'       => $niveauxCode,
            'annee_code'         => $anneeCode,
            'etablissement_code' => $this->etablissementCode,
        ]);
        Response::success('Document ajouté.', ['id_document' => $id], 201);
    }

    public function modifier(): void
    {
        $id          = (int) (Validator::post('id_document') ?? 0);
        $libelle     = Validator::post('libelle_document');
        $lien        = Validator::post('lien_document');
        $filiereCode = Validator::post('filiere_code');
        $niveauxCode = Validator::post('niveaux_code');
        $anneeCode   = Validator::post('annee_code');

        $v = new Validator();
        $v->required('libelle_document', $libelle, 'Libellé')
          ->required('lien_document',    $lien,    'Lien');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findById($id, $this->etablissementCode)) {
            Response::notFound('Document introuvable.');
        }

        $this->model->update($id, [
            'libelle_document'   => $libelle,
            'lien_document'      => $lien,
            'filiere_code'       => $filiereCode,
            'niveaux_code'       => $niveauxCode,
            'annee_code'         => $anneeCode,
            'etablissement_code' => $this->etablissementCode,
        ]);
        Response::success('Document modifié.');
    }

    public function supprimer(): void
    {
        $id = (int) (Validator::post('id_document') ?? 0);
        if (!$id) Response::error('ID requis.', 422);
        if (!$this->model->findById($id, $this->etablissementCode)) {
            Response::notFound('Document introuvable.');
        }
        $this->model->supprimer($id, $this->etablissementCode);
        Response::success('Document supprimé.');
    }
}
