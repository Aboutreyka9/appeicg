<?php

declare(strict_types=1);

namespace App\Models;

class FiliereModel extends BaseModel
{
    private string $table = 'filieres';

    public function liste(string $etablissementCode, ?string $cycleCode = null): array
    {
        $sql    = "SELECT f.*, c.libelle_cycle
                   FROM {$this->table} f
                   LEFT JOIN cycles c ON c.code_cycle = f.cycle_code
                   WHERE f.etablissement_code = ?";
        $params = [$etablissementCode];
        if ($cycleCode) {
            $sql    .= ' AND f.cycle_code = ?';
            $params[] = $cycleCode;
        }
        $sql .= ' ORDER BY f.libelle_filiere ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE code_filiere = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function libelleExists(string $libelle, string $cycleCode, string $etablissementCode, ?string $excludeCode = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table}
                   WHERE libelle_filiere = ? AND cycle_code = ? AND etablissement_code = ?";
        $params = [$libelle, $cycleCode, $etablissementCode];
        if ($excludeCode) {
            $sql    .= ' AND code_filiere != ?';
            $params[] = $excludeCode;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('FIL');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_filiere, libelle_filiere, description_filiere, etablissement_code,
              cycle_code, created_at_filiere, statut_filiere, user_code)
             VALUES (?, ?, ?, ?, ?, NOW(), 'actif', ?)"
        );
        $stmt->execute([
            $code,
            $data['libelle_filiere'],
            $data['description_filiere'] ?? null,
            $data['etablissement_code'],
            $data['cycle_code'],
            $data['user_code'],
        ]);
        return $code;
    }

    public function update(string $code, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET libelle_filiere = ?, description_filiere = ?, cycle_code = ?, updated_at_filiere = NOW()
             WHERE code_filiere = ? AND etablissement_code = ?"
        );
        $stmt->execute([
            $data['libelle_filiere'],
            $data['description_filiere'] ?? null,
            $data['cycle_code'],
            $code,
            $data['etablissement_code'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatut(string $code, string $statut, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_filiere = ?, updated_at_filiere = NOW()
             WHERE code_filiere = ? AND etablissement_code = ?"
        );
        $stmt->execute([$statut, $code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    public function hasNiveaux(string $code, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM niveaux WHERE filiere_code = ? AND etablissement_code = ?"
        );
        $stmt->execute([$code, $etablissementCode]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
