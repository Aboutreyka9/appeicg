<?php

declare(strict_types=1);

namespace App\Models;

class EnseignantMatiereModel extends BaseModel
{
    private string $table = 'enseignant_matiere';

    public function listByEnseignant(string $enseignantCode, string $etablissementCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT em.*, m.libelle_matiere
             FROM {$this->table} em
             JOIN matieres m ON m.code_matiere = em.matiere_code
             WHERE em.enseignant_code = ? AND em.etablissement_code = ?
             ORDER BY m.libelle_matiere ASC"
        );
        $stmt->execute([$enseignantCode, $etablissementCode]);
        return $stmt->fetchAll();
    }

    public function affecter(string $enseignantCode, string $matiereCode, string $etablissementCode): bool
    {
        // Vérifier si l'affectation existe déjà (même inactive)
        $stmt = $this->db->prepare(
            "SELECT id_enseignant_matiere, statut_enseignant_matiere FROM {$this->table}
             WHERE enseignant_code = ? AND matiere_code = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$enseignantCode, $matiereCode, $etablissementCode]);
        $existing = $stmt->fetch();

        if ($existing) {
            if ($existing['statut_enseignant_matiere'] === 'actif') {
                return false; // déjà active
            }
            // Réactiver
            $upd = $this->db->prepare(
                "UPDATE {$this->table}
                 SET statut_enseignant_matiere = 'actif', updated_at_enseignant_matiere = NOW()
                 WHERE id_enseignant_matiere = ?"
            );
            $upd->execute([$existing['id_enseignant_matiere']]);
            return true;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (enseignant_code, matiere_code, etablissement_code, created_at_enseignant_matiere, statut_enseignant_matiere)
             VALUES (?, ?, ?, NOW(), 'actif')"
        );
        $stmt->execute([$enseignantCode, $matiereCode, $etablissementCode]);
        return true;
    }

    public function retirer(string $enseignantCode, string $matiereCode, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_enseignant_matiere = 'inactif', updated_at_enseignant_matiere = NOW()
             WHERE enseignant_code = ? AND matiere_code = ? AND etablissement_code = ?"
        );
        $stmt->execute([$enseignantCode, $matiereCode, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    public function isAffecte(string $enseignantCode, string $matiereCode, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table}
             WHERE enseignant_code = ? AND matiere_code = ? AND etablissement_code = ?
               AND statut_enseignant_matiere = 'actif'"
        );
        $stmt->execute([$enseignantCode, $matiereCode, $etablissementCode]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
