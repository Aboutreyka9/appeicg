<?php

declare(strict_types=1);

namespace App\Models;

class CycleModel extends BaseModel
{
    private string $table = 'cycles';

    public function liste(string $etablissementCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE etablissement_code = ?
             ORDER BY libelle_cycle ASC"
        );
        $stmt->execute([$etablissementCode]);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE code_cycle = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function libelleExists(string $libelle, string $etablissementCode, ?string $excludeCode = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table} WHERE libelle_cycle = ? AND etablissement_code = ?";
        $params = [$libelle, $etablissementCode];
        if ($excludeCode) {
            $sql    .= ' AND code_cycle != ?';
            $params[] = $excludeCode;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('CYC');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_cycle, libelle_cycle, etablissement_code, created_at_cycle, statut_cycle, user_code)
             VALUES (?, ?, ?, NOW(), 'actif', ?)"
        );
        $stmt->execute([
            $code,
            $data['libelle_cycle'],
            $data['etablissement_code'],
            $data['user_code'],
        ]);
        return $code;
    }

    public function update(string $code, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET libelle_cycle = ?, updated_at_cycle = NOW()
             WHERE code_cycle = ? AND etablissement_code = ?"
        );
        $stmt->execute([$data['libelle_cycle'], $code, $data['etablissement_code']]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatut(string $code, string $statut, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_cycle = ?, updated_at_cycle = NOW()
             WHERE code_cycle = ? AND etablissement_code = ?"
        );
        $stmt->execute([$statut, $code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    public function hasFilieres(string $code, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM filieres WHERE cycle_code = ? AND etablissement_code = ?"
        );
        $stmt->execute([$code, $etablissementCode]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
