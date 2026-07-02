<?php

declare(strict_types=1);

namespace App\Models;

class NiveauModel extends BaseModel
{
    private string $table = 'niveaux';

    public function liste(string $etablissementCode, ?string $filiereCode = null): array
    {
        $sql    = "SELECT n.*, f.libelle_filiere
                   FROM {$this->table} n
                   LEFT JOIN filieres f ON f.code_filiere = n.filiere_code
                   WHERE n.etablissement_code = ?";
        $params = [$etablissementCode];
        if ($filiereCode) {
            $sql    .= ' AND n.filiere_code = ?';
            $params[] = $filiereCode;
        }
        $sql .= ' ORDER BY n.libelle_niveau ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $filiereCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE code_niveau = ? AND filiere_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $filiereCode]);
        return $stmt->fetch();
    }

    public function findByCodeEtab(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE code_niveau = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function libelleExists(string $libelle, string $filiereCode, string $etablissementCode, ?string $excludeCode = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table}
                   WHERE libelle_niveau = ? AND filiere_code = ? AND etablissement_code = ?";
        $params = [$libelle, $filiereCode, $etablissementCode];
        if ($excludeCode) {
            $sql    .= ' AND code_niveau != ?';
            $params[] = $excludeCode;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('NIV');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_niveau, libelle_niveau, etablissement_code, filiere_code, created_at_niveau, statut_niveau)
             VALUES (?, ?, ?, ?, NOW(), 'actif')"
        );
        $stmt->execute([
            $code,
            $data['libelle_niveau'],
            $data['etablissement_code'],
            $data['filiere_code'],
        ]);
        return $code;
    }

    public function update(string $code, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET libelle_niveau = ?, filiere_code = ?, updated_at_niveau = NOW()
             WHERE code_niveau = ? AND etablissement_code = ?"
        );
        $stmt->execute([
            $data['libelle_niveau'],
            $data['filiere_code'],
            $code,
            $data['etablissement_code'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatut(string $code, string $statut, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_niveau = ?, updated_at_niveau = NOW()
             WHERE code_niveau = ? AND etablissement_code = ?"
        );
        $stmt->execute([$statut, $code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    public function hasClasses(string $code, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM classes WHERE niveau_code = ? AND etablissement_code = ?"
        );
        $stmt->execute([$code, $etablissementCode]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
