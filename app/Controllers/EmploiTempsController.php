<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\MainController;
use App\Models\EmploiTempsModel;
use App\Models\ClasseModel;
use App\Models\MatiereModel;
use App\Models\EnseignantModel;
use App\Models\SalleModel;
use App\Models\AnneeModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class EmploiTempsController extends MainController
{
    private EmploiTempsModel $model;
    private ClasseModel      $classeModel;
    private MatiereModel     $matiereModel;
    private EnseignantModel  $enseignantModel;
    private SalleModel       $salleModel;
    private AnneeModel       $anneeModel;
    private string $etablissementCode;
    private string $userCode;

    private const JOURS = ['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'];

    public function __construct()
    {
        $this->model           = new EmploiTempsModel();
        $this->classeModel     = new ClasseModel();
        $this->matiereModel    = new MatiereModel();
        $this->enseignantModel = new EnseignantModel();
        $this->salleModel      = new SalleModel();
        $this->anneeModel      = new AnneeModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

            public function index()
    {
        return $this->viewGuest('academiques/emplois_temps', ["title" => "Emplois du temps"]);
    }

    public function liste(): void
    {
        $filters = [
            'classe_code'     => $_GET['classe_code']     ?? '',
            'enseignant_code' => $_GET['enseignant_code'] ?? '',
            'annee_code'      => $_GET['annee_code']      ?? '',
            'jour'            => $_GET['jour']            ?? '',
        ];
        Response::success('Emplois du temps.', $this->model->liste($this->etablissementCode, $filters));
    }

    public function ajouter(): void
    {
        $classeCode     = Validator::post('classe_code');
        $matiereCode    = Validator::post('matiere_code');
        $enseignantCode = Validator::post('enseignant_code');
        $salleCode      = Validator::post('salle_code');
        $anneeId        = Validator::post('annee_id');
        $jour           = Validator::post('jour');
        $heureDebut     = Validator::post('heure_debut');
        $heureFin       = Validator::post('heure_fin');

        $v = new Validator();
        $v->required('classe_code',     $classeCode,     'Classe')
          ->required('matiere_code',    $matiereCode,    'Matière')
          ->required('enseignant_code', $enseignantCode, 'Enseignant')
          ->required('salle_code',      $salleCode,      'Salle')
          ->required('annee_id',        $anneeId,        'Année')
          ->required('jour',            $jour,           'Jour')
          ->required('heure_debut',     $heureDebut,     'Heure de début')
          ->required('heure_fin',       $heureFin,       'Heure de fin')
          ->in('jour', $jour, self::JOURS, 'Jour');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if ($heureDebut >= $heureFin) {
            Response::error('L\'heure de fin doit être après l\'heure de début.', 422);
        }

        // Intégrité référentielle
        if (!$this->classeModel->findByCode($classeCode, $this->etablissementCode))
            Response::notFound('Classe introuvable.');
        if (!$this->matiereModel->findByCode($matiereCode, $this->etablissementCode))
            Response::notFound('Matière introuvable.');
        if (!$this->enseignantModel->findByCode($enseignantCode, $this->etablissementCode))
            Response::notFound('Enseignant introuvable.');
        if (!$this->salleModel->findByCode($salleCode, $this->etablissementCode))
            Response::notFound('Salle introuvable.');

        $annee = $this->anneeModel->findById((int) $anneeId, $this->etablissementCode);
        if (!$annee) Response::notFound('Année introuvable.');
        $anneeCode = $annee['libelle_annee'];

        // Vérification des conflits
        $conflitSalle = $this->model->conflitSalle($salleCode, $jour, $heureDebut, $heureFin, $this->etablissementCode);
        if ($conflitSalle) {
            Response::error("Conflit de salle : {$conflitSalle['libelle_classe']} occupe déjà cette salle ce créneau.", 409);
        }

        $conflitEns = $this->model->conflitEnseignant($enseignantCode, $jour, $heureDebut, $heureFin, $this->etablissementCode);
        if ($conflitEns) {
            Response::error("Conflit enseignant : il est déjà affecté à {$conflitEns['libelle_classe']} sur ce créneau.", 409);
        }

        $conflitCls = $this->model->conflitClasse($classeCode, $jour, $heureDebut, $heureFin, $this->etablissementCode);
        if ($conflitCls) {
            Response::error("Conflit classe : {$conflitCls['libelle_matiere']} est déjà planifiée sur ce créneau.", 409);
        }

        $code = $this->model->create([
            'classe_code'        => $classeCode,
            'matiere_code'       => $matiereCode,
            'enseignant_code'    => $enseignantCode,
            'salle_code'         => $salleCode,
            'annee_code'         => $anneeCode,
            'jour'               => $jour,
            'heure_debut'        => $heureDebut,
            'heure_fin'          => $heureFin,
            'etablissement_code' => $this->etablissementCode,
            'user_code'          => $this->userCode,
        ]);

        Response::success('Créneau ajouté avec succès.', ['code_emploi' => $code], 201);
    }

    public function modifier(): void
    {
        $code           = Validator::post('code_emploi');
        $classeCode     = Validator::post('classe_code');
        $matiereCode    = Validator::post('matiere_code');
        $enseignantCode = Validator::post('enseignant_code');
        $salleCode      = Validator::post('salle_code');
        $anneeId        = Validator::post('annee_id');
        $jour           = Validator::post('jour');
        $heureDebut     = Validator::post('heure_debut');
        $heureFin       = Validator::post('heure_fin');

        $v = new Validator();
        $v->required('code_emploi',     $code,           'Code')
          ->required('classe_code',     $classeCode,     'Classe')
          ->required('matiere_code',    $matiereCode,    'Matière')
          ->required('enseignant_code', $enseignantCode, 'Enseignant')
          ->required('salle_code',      $salleCode,      'Salle')
          ->required('annee_id',        $anneeId,        'Année')
          ->required('jour',            $jour,           'Jour')
          ->required('heure_debut',     $heureDebut,     'Heure de début')
          ->required('heure_fin',       $heureFin,       'Heure de fin')
          ->in('jour', $jour, self::JOURS, 'Jour');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        if ($heureDebut >= $heureFin) {
            Response::error('L\'heure de fin doit être après l\'heure de début.', 422);
        }

        if (!$this->model->findByCode($code, $this->etablissementCode))
            Response::notFound('Créneau introuvable.');

        $annee = $this->anneeModel->findById((int) $anneeId, $this->etablissementCode);
        if (!$annee) Response::notFound('Année introuvable.');
        $anneeCode = $annee['libelle_annee'];

        // Conflits (en excluant le créneau courant)
        $conflitSalle = $this->model->conflitSalle($salleCode, $jour, $heureDebut, $heureFin, $this->etablissementCode, $code);
        if ($conflitSalle) Response::error("Conflit de salle : {$conflitSalle['libelle_classe']}.", 409);

        $conflitEns = $this->model->conflitEnseignant($enseignantCode, $jour, $heureDebut, $heureFin, $this->etablissementCode, $code);
        if ($conflitEns) Response::error("Conflit enseignant : {$conflitEns['libelle_classe']}.", 409);

        $conflitCls = $this->model->conflitClasse($classeCode, $jour, $heureDebut, $heureFin, $this->etablissementCode, $code);
        if ($conflitCls) Response::error("Conflit classe : {$conflitCls['libelle_matiere']}.", 409);

        $this->model->update($code, [
            'classe_code'        => $classeCode,
            'matiere_code'       => $matiereCode,
            'enseignant_code'    => $enseignantCode,
            'salle_code'         => $salleCode,
            'annee_code'         => $anneeCode,
            'jour'               => $jour,
            'heure_debut'        => $heureDebut,
            'heure_fin'          => $heureFin,
            'etablissement_code' => $this->etablissementCode,
        ]);

        Response::success('Créneau modifié avec succès.');
    }

    public function supprimer(): void
    {
        $code = Validator::post('code_emploi');
        if (!$code) Response::error('Code requis.', 422);
        if (!$this->model->findByCode($code, $this->etablissementCode))
            Response::notFound('Créneau introuvable.');
        $this->model->supprimer($code, $this->etablissementCode);
        Response::success('Créneau supprimé.');
    }
}
