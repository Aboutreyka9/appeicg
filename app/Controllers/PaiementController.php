<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\MainController;
use App\Models\PaiementModel;
use App\Models\InscriptionModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class PaiementController extends MainController
{
    private PaiementModel    $model;
    private InscriptionModel $inscriptionModel;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        // AuthService::requireAuth();
        $this->model             = new PaiementModel();
        $this->inscriptionModel  = new InscriptionModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

        public function index()
    {
        return $this->view('finances/paiements', ["title" => "Paiements"]);
    }
    
    public function liste(): void
    {
        $filters = [
            'annee_code'    => $_GET['annee_code']    ?? '',
            'statut'        => $_GET['statut']        ?? '',
            'type_paiement' => $_GET['type_paiement'] ?? '',
            'search'        => $_GET['search']        ?? '',
        ];
        Response::success('Liste des paiements.', $this->model->liste($this->etablissementCode, $filters));
    }

    public function enregistrer(): void
    {
        $montant          = Validator::post('montant_paiement');
        $typePaiement     = Validator::post('type_paiement');
        $modePaiement     = Validator::post('mode_paiement');
        $inscriptionCode  = Validator::post('inscription_code');
        $anneeCode        = Validator::post('annee_code');
        $observations     = Validator::post('observations');

        $v = new Validator();
        $v->required('montant_paiement', $montant,      'Montant')
          ->required('type_paiement',    $typePaiement, 'Type de paiement')
          ->required('mode_paiement',    $modePaiement, 'Mode de paiement');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if ((float) $montant <= 0) {
            Response::error('Le montant doit être supérieur à 0.', 422);
        }

        // Vérifier l'inscription si fournie
        if ($inscriptionCode) {
            $inscription = $this->inscriptionModel->findByCode($inscriptionCode, $this->etablissementCode);
            if (!$inscription) Response::notFound('Inscription introuvable.');
            if ($inscription['statut_inscription'] === 'annule') {
                Response::error('Impossible d\'enregistrer un paiement pour une inscription annulée.', 409);
            }
            $anneeCode = $anneeCode ?: $inscription['annee_code'];
        }

        $code = $this->model->create([
            'montant_paiement'   => (float) $montant,
            'type_paiement'      => $typePaiement,
            'mode_paiement'      => $modePaiement,
            'reference_paiement' => $inscriptionCode ?: null,
            'annee_code'         => $anneeCode,
            'observations'       => $observations,
            'user_code'          => $this->userCode,
            'etablissement_code' => $this->etablissementCode,
        ]);

        // Vérifier si l'inscription est soldée
        if ($inscriptionCode) {
            $totalPaye  = $this->model->totalByInscription($inscriptionCode, $this->etablissementCode);
            $inscription = $this->inscriptionModel->findByCode($inscriptionCode, $this->etablissementCode);
            if ($inscription && $totalPaye >= (float) $inscription['montant_scolarite_inscription']
                && (float) $inscription['montant_scolarite_inscription'] > 0) {
                $this->inscriptionModel->updateStatut($inscriptionCode, 'solde', $this->etablissementCode);
            }
        }

        Response::success('Paiement enregistré avec succès.', ['code_paiement' => $code], 201);
    }

    public function annuler(): void
    {
        $code = Validator::post('code_paiement');
        if (!$code) Response::error('Code paiement requis.', 422);
        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Paiement introuvable.');
        }
        $this->model->annuler($code, $this->etablissementCode);
        Response::success('Paiement annulé.');
    }

    public function stats(): void
    {
        $anneeCode = $_GET['annee_code'] ?? '';
        $total     = $this->model->totalConfirme($this->etablissementCode, $anneeCode ?: null);
        $parMois   = $anneeCode ? $this->model->statsParMois($this->etablissementCode, $anneeCode) : [];
        Response::success('Statistiques.', [
            'total_confirme' => $total,
            'par_mois'       => $parMois,
        ]);
    }

    public function byInscription(): void
    {
        $inscriptionCode = $_GET['inscription_code'] ?? '';
        if (!$inscriptionCode) Response::error('Code inscription requis.', 422);
        $paiements   = $this->model->liste($this->etablissementCode, ['search' => '']);
        // Filtrer par référence
        $result = array_filter(
            $this->model->liste($this->etablissementCode, []),
            fn($p) => $p['reference_paiement'] === $inscriptionCode
        );
        $total = $this->model->totalByInscription($inscriptionCode, $this->etablissementCode);
        Response::success('Paiements de l\'inscription.', [
            'paiements' => array_values($result),
            'total'     => $total,
        ]);
    }
}
