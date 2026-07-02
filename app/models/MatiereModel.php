<?php

declare(strict_types=1);

namespace App\Models;

class MatiereModel extends BaseModel
{
    private string $table = 'matieres';

    public function liste(string $etablissementCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE etablissement_code = ?
             ORDER BY libelle_matiere ASC"
        );
        $stmt->execute([$etablissementCode]);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE code_matiere = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function libelleExists(string $libelle, string $etablissementCode, ?string $excludeCode = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table} WHERE libelle_matiere = ? AND etablissement_code = ?";
        $params = [$libelle, $etablissementCode];
        if ($excludeCode) {
            $sql    .= ' AND code_matiere != ?';
            $params[] = $excludeCode;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('MAT');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_matiere, libelle_matiere, etablissement_code, user_code, created_at_matiere, statut_matiere)
             VALUES (?, ?, ?, ?, NOW(), 'actif')"
        );
        $stmt->execute([
            $code,
            $data['libelle_matiere'],
            $data['etablissement_code'],
            $data['user_code'],
        ]);
        return $code;
    }

    public function update(string $code, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET libelle_matiere = ?, updated_at_matiere = NOW()
             WHERE code_matiere = ? AND etablissement_code = ?"
        );
        $stmt->execute([$data['libelle_matiere'], $code, $data['etablissement_code']]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatut(string $code, string $statut, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_matiere = ?, updated_at_matiere = NOW()
             WHERE code_matiere = ? AND etablissement_code = ?"
        );
        $stmt->execute([$statut, $code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    public function hasNotes(string $code, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM notes WHERE matiere_code = ? AND etablissement_code = ?"
        );
        $stmt->execute([$code, $etablissementCode]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
