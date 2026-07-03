<?php

declare(strict_types=1);

namespace App\Models;

class NoteModel extends BaseModel
{
    private string $table = 'notes';

    public function liste(string $etablissementCode, array $filters = []): array
    {
        $sql    = "SELECT n.*,
                          m.libelle_matiere,
                          i.etudiant_code, i.classe_code,
                          e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant,
                          s.libelle_semestre
                   FROM {$this->table} n
                   JOIN inscriptions i  ON i.code_inscription = n.inscription_code
                   JOIN etudiants e     ON e.code_etudiant    = i.etudiant_code
                   JOIN matieres m      ON m.code_matiere     = n.matiere_code
                   JOIN semestres s     ON s.code_semestre    = n.semestre_code
                   WHERE n.etablissement_code = ? AND n.statut_note = 'actif'";
        $params = [$etablissementCode];

        if (!empty($filters['inscription_code'])) {
            $sql    .= ' AND n.inscription_code = ?';
            $params[] = $filters['inscription_code'];
        }
        if (!empty($filters['semestre_code'])) {
            $sql    .= ' AND n.semestre_code = ?';
            $params[] = $filters['semestre_code'];
        }
        if (!empty($filters['matiere_code'])) {
            $sql    .= ' AND n.matiere_code = ?';
            $params[] = $filters['matiere_code'];
        }
        if (!empty($filters['classe_code'])) {
            $sql    .= ' AND i.classe_code = ?';
            $params[] = $filters['classe_code'];
        }

        $sql .= ' ORDER BY e.nom_etudiant ASC, m.libelle_matiere ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE code_note = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function exists(string $inscriptionCode, string $matiereCode, string $semestreCode,
                           string $typeEvalCode, string $etablissementCode, ?string $excludeCode = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table}
                   WHERE inscription_code = ? AND matiere_code = ? AND semestre_code = ?
                     AND type_evaluation_code = ? AND etablissement_code = ? AND statut_note = 'actif'";
        $params = [$inscriptionCode, $matiereCode, $semestreCode, $typeEvalCode, $etablissementCode];
        if ($excludeCode) { $sql .= ' AND code_note != ?'; $params[] = $excludeCode; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('NOT');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_note, valeur_note, type_evaluation_code, observations,
              inscription_code, matiere_code, semestre_code,
              user_code, etablissement_code, created_at_note, statut_note)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'actif')"
        );
        $stmt->execute([
            $code,
            $data['valeur_note'],
            $data['type_evaluation_code'],
            $data['observations'] ?: null,
            $data['inscription_code'],
            $data['matiere_code'],
            $data['semestre_code'],
            $data['user_code'],
            $data['etablissement_code'],
        ]);
        return $code;
    }

    public function update(string $code, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET valeur_note = ?, type_evaluation_code = ?, observations = ?, updated_at_note = NOW()
             WHERE code_note = ? AND etablissement_code = ?"
        );
        $stmt->execute([
            $data['valeur_note'],
            $data['type_evaluation_code'],
            $data['observations'] ?: null,
            $code,
            $data['etablissement_code'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public function supprimer(string $code, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_note = 'inactif', updated_at_note = NOW()
             WHERE code_note = ? AND etablissement_code = ?"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Calculer la moyenne d'un étudiant pour un semestre donné
     */
    public function moyenneParSemestre(string $inscriptionCode, string $semestreCode, string $etablissementCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT n.matiere_code, m.libelle_matiere,
                    AVG(n.valeur_note) as moyenne,
                    COUNT(n.id_note)   as nb_notes,
                    MIN(n.valeur_note) as min_note,
                    MAX(n.valeur_note) as max_note
             FROM {$this->table} n
             JOIN matieres m ON m.code_matiere = n.matiere_code
             WHERE n.inscription_code = ? AND n.semestre_code = ?
               AND n.etablissement_code = ? AND n.statut_note = 'actif'
             GROUP BY n.matiere_code, m.libelle_matiere
             ORDER BY m.libelle_matiere ASC"
        );
        $stmt->execute([$inscriptionCode, $semestreCode, $etablissementCode]);
        return $stmt->fetchAll();
    }

    /**
     * Bulletin complet : toutes les matières + moyennes pour un étudiant/semestre
     */
    public function bulletin(string $inscriptionCode, string $semestreCode, string $etablissementCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT n.*,
                    m.libelle_matiere,
                    e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant,
                    c.libelle_classe,
                    s.libelle_semestre,
                    i.annee_code
             FROM {$this->table} n
             JOIN matieres     m ON m.code_matiere     = n.matiere_code
             JOIN inscriptions i ON i.code_inscription = n.inscription_code
             JOIN etudiants    e ON e.code_etudiant    = i.etudiant_code
             JOIN classes      c ON c.code_classe      = i.classe_code
             JOIN semestres    s ON s.code_semestre    = n.semestre_code
             WHERE n.inscription_code = ? AND n.semestre_code = ?
               AND n.etablissement_code = ? AND n.statut_note = 'actif'
             ORDER BY m.libelle_matiere ASC"
        );
        $stmt->execute([$inscriptionCode, $semestreCode, $etablissementCode]);
        return $stmt->fetchAll();
    }

    /**
     * Classement d'une classe pour un semestre
     */
    public function classement(string $classeCode, string $semestreCode, string $etablissementCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT i.code_inscription, i.etudiant_code,
                    e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant,
                    ROUND(AVG(n.valeur_note), 2) as moyenne_generale,
                    COUNT(DISTINCT n.matiere_code) as nb_matieres
             FROM inscriptions i
             JOIN etudiants e ON e.code_etudiant = i.etudiant_code
             LEFT JOIN {$this->table} n ON n.inscription_code = i.code_inscription
                 AND n.semestre_code = ? AND n.statut_note = 'actif'
             WHERE i.classe_code = ? AND i.etablissement_code = ?
               AND i.statut_inscription != 'annule'
             GROUP BY i.code_inscription, i.etudiant_code, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant
             ORDER BY moyenne_generale DESC"
        );
        $stmt->execute([$semestreCode, $classeCode, $etablissementCode]);
        return $stmt->fetchAll();
    }
}
