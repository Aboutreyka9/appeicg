<?php

declare(strict_types=1);

namespace App\Models;

class SemestreModel extends BaseModel
{
    private string $table = 'semestres';

    public function liste(string $anneeCode, string $etablissementCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE annee_code = ? AND etablissement_code = ?
             ORDER BY date_debut_semestre ASC"
        );
        $stmt->execute([$anneeCode, $etablissementCode]);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE code_semestre = ? LIMIT 1"
        );
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    public function libelleExists(string $libelle, string $anneeCode, string $etablissementCode, ?string $excludeCode = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table}
                   WHERE libelle_semestre = ? AND annee_code = ? AND etablissement_code = ?";
        $params = [$libelle, $anneeCode, $etablissementCode];
        if ($excludeCode) {
            $sql    .= ' AND code_semestre != ?';
            $params[] = $excludeCode;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('SEM');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_semestre, libelle_semestre, etablissement_code, annee_code,
              date_debut_semestre, date_fin_semestre, created_at_semestre, user_code)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)"
        );
        $stmt->execute([
            $code,
            $data['libelle_semestre'],
            $data['etablissement_code'],
            $data['annee_code'],
            $data['date_debut_semestre'] ?: null,
            $data['date_fin_semestre']   ?: null,
            $data['user_code'],
        ]);
        return $code;
    }

    public function update(string $code, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET libelle_semestre    = ?,
                 date_debut_semestre = ?,
                 date_fin_semestre   = ?,
                 updated_at_semestre = NOW()
             WHERE code_semestre = ? AND etablissement_code = ?"
        );
        $stmt->execute([
            $data['libelle_semestre'],
            $data['date_debut_semestre'] ?: null,
            $data['date_fin_semestre']   ?: null,
            $code,
            $data['etablissement_code'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(string $code, string $etablissementCode): bool
    {
        // Soft delete non applicable ici (pas de statut_semestre) → suppression réelle
        // mais on vérifie l'intégrité avant (notes liées)
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table}
             WHERE code_semestre = ? AND etablissement_code = ?"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Intégrité référentielle manuelle : vérifier si des notes sont liées
     */
    public function hasNotes(string $code): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM notes WHERE semestre_code = ?"
        );
        $stmt->execute([$code]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
