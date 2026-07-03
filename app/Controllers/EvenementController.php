<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EvenementModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class EvenementController
{
    private EvenementModel $model;

    public function __construct()
    {
        AuthService::requireAuth();
        $this->model = new EvenementModel();
    }

    public function liste(): void
    {
        $activeOnly = ($_GET['actif'] ?? '') === '1';
        Response::success('Événements.', $this->model->liste($activeOnly));
    }

    public function ajouter(): void
    {
        $titre       = Validator::post('titre_evenement');
        $description = Validator::post('description_evenement');
        $image       = Validator::post('image_evenement');
        $principal   = Validator::post('is_principal_evenement');

        $v = new Validator();
        $v->required('titre_evenement', $titre, 'Titre');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        $id = $this->model->create([
            'titre_evenement'        => $titre,
            'description_evenement'  => $description,
            'image_evenement'        => $image,
            'is_principal_evenement' => (bool) $principal,
        ]);
        Response::success('Événement ajouté.', ['id_evenement' => $id], 201);
    }

    public function modifier(): void
    {
        $id          = (int) (Validator::post('id_evenement') ?? 0);
        $titre       = Validator::post('titre_evenement');
        $description = Validator::post('description_evenement');
        $image       = Validator::post('image_evenement');
        $principal   = Validator::post('is_principal_evenement');

        $v = new Validator();
        $v->required('id_evenement',    (string) $id, 'ID')
          ->required('titre_evenement', $titre,        'Titre');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findById($id)) Response::notFound('Événement introuvable.');

        $this->model->update($id, [
            'titre_evenement'        => $titre,
            'description_evenement'  => $description,
            'image_evenement'        => $image,
            'is_principal_evenement' => (bool) $principal,
        ]);
        Response::success('Événement modifié.');
    }

    public function statut(): void
    {
        $id     = (int) (Validator::post('id_evenement') ?? 0);
        $statut = Validator::post('statut_evenement');

        $v = new Validator();
        $v->required('id_evenement',    (string) $id, 'ID')
          ->required('statut_evenement', $statut,      'Statut')
          ->in('statut_evenement', $statut, ['actif', 'inactif'], 'Statut');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findById($id)) Response::notFound('Événement introuvable.');
        $this->model->updateStatut($id, $statut);
        Response::success('Statut mis à jour.');
    }

    public function supprimer(): void
    {
        $id = (int) (Validator::post('id_evenement') ?? 0);
        if (!$id) Response::error('ID requis.', 422);
        if (!$this->model->findById($id)) Response::notFound('Événement introuvable.');
        $this->model->supprimer($id);
        Response::success('Événement supprimé.');
    }
}
