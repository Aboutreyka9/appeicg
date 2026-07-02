<?php

declare(strict_types=1);

namespace App\Models;

class SalleModel extends BaseModel
{
    private string $table = 'salles';

    public function liste(string $etablissementCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE etablissement_code = ?
             ORDER BY libelle_salle ASC"
        );
        $stmt->execute([$etablissementCode]);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE code_salle = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function libelleExists(string $libelle, string $etablissementCode, ?string $excludeCode = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table} WHERE libelle_salle = ? AND etablissement_code = ?";
        $params = [$libelle, $etablissementCode];
        if ($excludeCode) {
            $sql    .= ' AND code_salle != ?';
            $params[] = $excludeCode;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('SAL');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_salle, libelle_salle, etablissement_code, user_code, statut_salle, created_at)
             VALUES (?, ?, ?, ?, 'actif', NOW())"
        );
        $stmt->execute([
            $code,
            $data['libelle_salle'],
            $data['etablissement_code'],
            $data['user_code'],
        ]);
        return $code;
    }

    public function update(string $code, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET libelle_salle = ?, updated_at = NOW()
             WHERE code_salle = ? AND etablissement_code = ?"
        );
        $stmt->execute([$data['libelle_salle'], $code, $data['etablissement_code']]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatut(string $code, string $statut, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_salle = ?, updated_at = NOW()
             WHERE code_salle = ? AND etablissement_code = ?"
        );
        $stmt->execute([$statut, $code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    public function hasEmplois(string $code, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM emplois_temps
             WHERE salle_code = ? AND etablissement_code = ? AND statut_emploi = 'actif'"
        );
        $stmt->execute([$code, $etablissementCode]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
