<?php

declare(strict_types=1);

namespace App\Models;

class EmploiTempsModel extends BaseModel
{
    private string $table = 'emplois_temps';

    public function liste(string $etablissementCode, array $filters = []): array
    {
        $sql    = "SELECT et.*,
                          c.libelle_classe,
                          m.libelle_matiere,
                          e.nom_enseignant,
                          s.libelle_salle
                   FROM {$this->table} et
                   JOIN classes    c ON c.code_classe    = et.classe_code
                   JOIN matieres   m ON m.code_matiere   = et.matiere_code
                   JOIN enseignants e ON e.code_enseignant = et.enseignant_code
                   JOIN salles     s ON s.code_salle     = et.salle_code
                   WHERE et.etablissement_code = ? AND et.statut_emploi = 'actif'";
        $params = [$etablissementCode];

        if (!empty($filters['classe_code'])) {
            $sql    .= ' AND et.classe_code = ?';
            $params[] = $filters['classe_code'];
        }
        if (!empty($filters['enseignant_code'])) {
            $sql    .= ' AND et.enseignant_code = ?';
            $params[] = $filters['enseignant_code'];
        }
        if (!empty($filters['annee_code'])) {
            $sql    .= ' AND et.annee_code = ?';
            $params[] = $filters['annee_code'];
        }
        if (!empty($filters['jour'])) {
            $sql    .= ' AND et.jour = ?';
            $params[] = $filters['jour'];
        }

        $sql .= ' ORDER BY FIELD(et.jour,"lundi","mardi","mercredi","jeudi","vendredi","samedi","dimanche"), et.heure_debut ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE code_emploi = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    /**
     * Vérifier conflit de salle : même salle, même jour, créneau qui se chevauche
     */
    public function conflitSalle(string $salleCode, string $jour, string $heureDebut, string $heureFin,
                                  string $etablissementCode, ?string $excludeCode = null): array|false
    {
        $sql    = "SELECT et.*, c.libelle_classe
                   FROM {$this->table} et
                   JOIN classes c ON c.code_classe = et.classe_code
                   WHERE et.salle_code = ? AND et.jour = ?
                     AND et.statut_emploi = 'actif'
                     AND et.etablissement_code = ?
                     AND et.heure_debut < ? AND et.heure_fin > ?";
        $params = [$salleCode, $jour, $etablissementCode, $heureFin, $heureDebut];
        if ($excludeCode) { $sql .= ' AND et.code_emploi != ?'; $params[] = $excludeCode; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Vérifier conflit enseignant : même enseignant, même jour, créneau qui se chevauche
     */
    public function conflitEnseignant(string $enseignantCode, string $jour, string $heureDebut, string $heureFin,
                                       string $etablissementCode, ?string $excludeCode = null): array|false
    {
        $sql    = "SELECT et.*, c.libelle_classe
                   FROM {$this->table} et
                   JOIN classes c ON c.code_classe = et.classe_code
                   WHERE et.enseignant_code = ? AND et.jour = ?
                     AND et.statut_emploi = 'actif'
                     AND et.etablissement_code = ?
                     AND et.heure_debut < ? AND et.heure_fin > ?";
        $params = [$enseignantCode, $jour, $etablissementCode, $heureFin, $heureDebut];
        if ($excludeCode) { $sql .= ' AND et.code_emploi != ?'; $params[] = $excludeCode; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Vérifier conflit classe : même classe, même jour, créneau qui se chevauche
     */
    public function conflitClasse(string $classeCode, string $jour, string $heureDebut, string $heureFin,
                                   string $etablissementCode, ?string $excludeCode = null): array|false
    {
        $sql    = "SELECT et.*, m.libelle_matiere
                   FROM {$this->table} et
                   JOIN matieres m ON m.code_matiere = et.matiere_code
                   WHERE et.classe_code = ? AND et.jour = ?
                     AND et.statut_emploi = 'actif'
                     AND et.etablissement_code = ?
                     AND et.heure_debut < ? AND et.heure_fin > ?";
        $params = [$classeCode, $jour, $etablissementCode, $heureFin, $heureDebut];
        if ($excludeCode) { $sql .= ' AND et.code_emploi != ?'; $params[] = $excludeCode; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('EMP');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_emploi, classe_code, matiere_code, enseignant_code, salle_code,
              etablissement_code, annee_code, user_code, jour, heure_debut, heure_fin,
              created_at_emploi, statut_emploi)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'actif')"
        );
        $stmt->execute([
            $code,
            $data['classe_code'],
            $data['matiere_code'],
            $data['enseignant_code'],
            $data['salle_code'],
            $data['etablissement_code'],
            $data['annee_code'],
            $data['user_code'],
            $data['jour'],
            $data['heure_debut'],
            $data['heure_fin'],
        ]);
        return $code;
    }

    public function update(string $code, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET classe_code = ?, matiere_code = ?, enseignant_code = ?, salle_code = ?,
                 annee_code = ?, jour = ?, heure_debut = ?, heure_fin = ?, updated_at_emploi = NOW()
             WHERE code_emploi = ? AND etablissement_code = ?"
        );
        $stmt->execute([
            $data['classe_code'],
            $data['matiere_code'],
            $data['enseignant_code'],
            $data['salle_code'],
            $data['annee_code'],
            $data['jour'],
            $data['heure_debut'],
            $data['heure_fin'],
            $code,
            $data['etablissement_code'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public function supprimer(string $code, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_emploi = 'inactif', updated_at_emploi = NOW()
             WHERE code_emploi = ? AND etablissement_code = ?"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }
}
