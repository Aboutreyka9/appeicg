<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\NoteModel;
use App\Models\InscriptionModel;
use App\Models\MatiereModel;
use App\Models\SemestreModel;
use App\Services\AuthService;
use App\Helpers\Response;
use App\Helpers\Validator;

class NoteController
{
    private NoteModel        $model;
    private InscriptionModel $inscriptionModel;
    private MatiereModel     $matiereModel;
    private SemestreModel    $semestreModel;
    private string $etablissementCode;
    private string $userCode;

    public function __construct()
    {
        AuthService::requireAuth();
        $this->model             = new NoteModel();
        $this->inscriptionModel  = new InscriptionModel();
        $this->matiereModel      = new MatiereModel();
        $this->semestreModel     = new SemestreModel();
        $this->etablissementCode = AuthService::getEtablissementCode();
        $this->userCode          = AuthService::getUserCode();
    }

    // ─── CRUD Notes ───────────────────────────────────────────

    public function liste(): void
    {
        $filters = [
            'inscription_code' => $_GET['inscription_code'] ?? '',
            'semestre_code'    => $_GET['semestre_code']    ?? '',
            'matiere_code'     => $_GET['matiere_code']     ?? '',
            'classe_code'      => $_GET['classe_code']      ?? '',
        ];
        Response::success('Liste des notes.', $this->model->liste($this->etablissementCode, $filters));
    }

    public function ajouter(): void
    {
        $valeur          = Validator::post('valeur_note');
        $typeEval        = Validator::post('type_evaluation_code');
        $inscriptionCode = Validator::post('inscription_code');
        $matiereCode     = Validator::post('matiere_code');
        $semestreCode    = Validator::post('semestre_code');
        $observations    = Validator::post('observations');

        $v = new Validator();
        $v->required('valeur_note',         $valeur,          'Valeur de la note')
          ->required('type_evaluation_code', $typeEval,        'Type d\'évaluation')
          ->required('inscription_code',     $inscriptionCode, 'Inscription')
          ->required('matiere_code',         $matiereCode,     'Matière')
          ->required('semestre_code',        $semestreCode,    'Semestre');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        $note = (float) $valeur;
        if ($note < 0 || $note > 20) {
            Response::error('La note doit être comprise entre 0 et 20.', 422);
        }

        // Intégrité référentielle
        if (!$this->inscriptionModel->findByCode($inscriptionCode, $this->etablissementCode)) {
            Response::notFound('Inscription introuvable.');
        }
        if (!$this->matiereModel->findByCode($matiereCode, $this->etablissementCode)) {
            Response::notFound('Matière introuvable.');
        }
        if (!$this->semestreModel->findByCode($semestreCode)) {
            Response::notFound('Semestre introuvable.');
        }

        // Vérifier doublon (même inscription + matière + semestre + type)
        if ($this->model->exists($inscriptionCode, $matiereCode, $semestreCode, $typeEval, $this->etablissementCode)) {
            Response::error('Une note de ce type existe déjà pour cet étudiant dans cette matière et ce semestre.', 409);
        }

        $code = $this->model->create([
            'valeur_note'          => $note,
            'type_evaluation_code' => $typeEval,
            'observations'         => $observations,
            'inscription_code'     => $inscriptionCode,
            'matiere_code'         => $matiereCode,
            'semestre_code'        => $semestreCode,
            'user_code'            => $this->userCode,
            'etablissement_code'   => $this->etablissementCode,
        ]);

        Response::success('Note ajoutée avec succès.', ['code_note' => $code], 201);
    }

    public function modifier(): void
    {
        $code         = Validator::post('code_note');
        $valeur       = Validator::post('valeur_note');
        $typeEval     = Validator::post('type_evaluation_code');
        $observations = Validator::post('observations');

        $v = new Validator();
        $v->required('code_note',           $code,    'Code note')
          ->required('valeur_note',         $valeur,  'Valeur')
          ->required('type_evaluation_code', $typeEval,'Type d\'évaluation');
        if ($v->fails()) Response::error('Données invalides.', 422, $v->errors());

        $note = (float) $valeur;
        if ($note < 0 || $note > 20) {
            Response::error('La note doit être comprise entre 0 et 20.', 422);
        }

        $existing = $this->model->findByCode($code, $this->etablissementCode);
        if (!$existing) Response::notFound('Note introuvable.');

        // Vérifier doublon (en excluant la note courante)
        if ($this->model->exists(
            $existing['inscription_code'], $existing['matiere_code'],
            $existing['semestre_code'], $typeEval,
            $this->etablissementCode, $code
        )) {
            Response::error('Une note de ce type existe déjà pour cet étudiant.', 409);
        }

        $this->model->update($code, [
            'valeur_note'          => $note,
            'type_evaluation_code' => $typeEval,
            'observations'         => $observations,
            'etablissement_code'   => $this->etablissementCode,
        ]);
        Response::success('Note modifiée avec succès.');
    }

    public function supprimer(): void
    {
        $code = Validator::post('code_note');
        if (!$code) Response::error('Code note requis.', 422);
        if (!$this->model->findByCode($code, $this->etablissementCode)) {
            Response::notFound('Note introuvable.');
        }
        $this->model->supprimer($code, $this->etablissementCode);
        Response::success('Note supprimée.');
    }

    // ─── Moyennes & Bulletins ─────────────────────────────────

    public function moyennes(): void
    {
        $inscriptionCode = $_GET['inscription_code'] ?? '';
        $semestreCode    = $_GET['semestre_code']    ?? '';

        if (!$inscriptionCode || !$semestreCode) {
            Response::error('inscription_code et semestre_code sont requis.', 422);
        }

        $moyennes = $this->model->moyenneParSemestre($inscriptionCode, $semestreCode, $this->etablissementCode);

        // Calculer la moyenne générale
        $moyenneGen = 0;
        if (count($moyennes) > 0) {
            $moyenneGen = round(array_sum(array_column($moyennes, 'moyenne')) / count($moyennes), 2);
        }

        Response::success('Moyennes.', [
            'par_matiere'      => $moyennes,
            'moyenne_generale' => $moyenneGen,
        ]);
    }

    public function bulletin(): void
    {
        $inscriptionCode = $_GET['inscription_code'] ?? '';
        $semestreCode    = $_GET['semestre_code']    ?? '';

        if (!$inscriptionCode || !$semestreCode) {
            Response::error('inscription_code et semestre_code sont requis.', 422);
        }

        if (!$this->inscriptionModel->findByCode($inscriptionCode, $this->etablissementCode)) {
            Response::notFound('Inscription introuvable.');
        }

        $notes    = $this->model->bulletin($inscriptionCode, $semestreCode, $this->etablissementCode);
        $moyennes = $this->model->moyenneParSemestre($inscriptionCode, $semestreCode, $this->etablissementCode);

        // Construire le bulletin par matière
        $bulletin = [];
        foreach ($moyennes as $moy) {
            $notesMatiere = array_filter($notes, fn($n) => $n['matiere_code'] === $moy['matiere_code']);
            $bulletin[] = [
                'matiere_code'   => $moy['matiere_code'],
                'libelle_matiere' => $moy['libelle_matiere'],
                'moyenne'        => round((float) $moy['moyenne'], 2),
                'nb_notes'       => $moy['nb_notes'],
                'min_note'       => $moy['min_note'],
                'max_note'       => $moy['max_note'],
                'notes'          => array_values($notesMatiere),
            ];
        }

        // Info étudiant depuis la première note
        $etudiantInfo = [];
        if (!empty($notes)) {
            $first = $notes[0];
            $etudiantInfo = [
                'nom_etudiant'        => $first['nom_etudiant'],
                'prenom_etudiant'     => $first['prenom_etudiant'],
                'matricule_etudiant'  => $first['matricule_etudiant'],
                'libelle_classe'      => $first['libelle_classe'],
                'libelle_semestre'    => $first['libelle_semestre'],
                'annee_code'          => $first['annee_code'],
            ];
        }

        $moyenneGen = count($bulletin) > 0
            ? round(array_sum(array_column($bulletin, 'moyenne')) / count($bulletin), 2)
            : 0;

        Response::success('Bulletin.', [
            'etudiant'         => $etudiantInfo,
            'bulletin'         => $bulletin,
            'moyenne_generale' => $moyenneGen,
            'nb_matieres'      => count($bulletin),
        ]);
    }

    public function classement(): void
    {
        $classeCode   = $_GET['classe_code']   ?? '';
        $semestreCode = $_GET['semestre_code'] ?? '';

        if (!$classeCode || !$semestreCode) {
            Response::error('classe_code et semestre_code sont requis.', 422);
        }

        $classement = $this->model->classement($classeCode, $semestreCode, $this->etablissementCode);

        // Ajouter le rang
        $rang = 1;
        foreach ($classement as &$row) {
            $row['rang'] = $row['moyenne_generale'] !== null ? $rang++ : '—';
        }

        Response::success('Classement.', $classement);
    }
}
