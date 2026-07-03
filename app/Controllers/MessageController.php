<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\MessageModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class MessageController
{
    private MessageModel $model;

    public function __construct()
    {
        AuthService::requireAuth();
        $this->model = new MessageModel();
    }

    public function liste(): void
    {
        $filters = ['statut' => $_GET['statut'] ?? ''];
        Response::success('Messages.', $this->model->liste($filters));
    }

    public function creer(): void
    {
        $objet       = Validator::post('objet_message');
        $description = Validator::post('description_message');

        $v = new Validator();
        $v->required('objet_message',       $objet,       'Objet')
          ->required('description_message', $description, 'Description');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        $id = $this->model->create([
            'objet_message'       => $objet,
            'description_message' => $description,
        ]);
        Response::success('Message créé.', ['id_message' => $id], 201);
    }

    public function updateStatut(): void
    {
        $id     = (int) (Validator::post('id_message') ?? 0);
        $statut = Validator::post('statut_message');

        $v = new Validator();
        $v->required('id_message',     (string) $id, 'ID message')
          ->required('statut_message', $statut,       'Statut')
          ->in('statut_message', $statut, ['en_attente', 'envoye', 'vue', 'archive'], 'Statut');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if (!$this->model->findById($id)) Response::notFound('Message introuvable.');
        $this->model->updateStatut($id, $statut);
        Response::success('Statut mis à jour.');
    }

    public function supprimer(): void
    {
        $id = (int) (Validator::post('id_message') ?? 0);
        if (!$id) Response::error('ID requis.', 422);
        if (!$this->model->findById($id)) Response::notFound('Message introuvable.');
        $this->model->supprimer($id);
        Response::success('Message supprimé.');
    }
}
