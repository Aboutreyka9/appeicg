<?php

declare(strict_types=1);

namespace App\Models;

class EtablissementModel extends BaseModel
{
    private string $table = 'etablissements';

    public function liste(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} ORDER BY libelle_etablissement ASC"
        );
        return $stmt->fetchAll();
    }

    public function findByCode(string $code): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE code_etablissement = ? LIMIT 1"
        );
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('ETB');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_etablissement, libelle_etablissement, adresse_etablissement,
              telephone_etablissement, telephone_etablissement2, email_etablissement,
              slogan_etablissement, created_at_etablissement, statut_etablissement)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'actif')"
        );
        $stmt->execute([
            $code,
            $data['libelle_etablissement'],
            $data['adresse_etablissement']    ?? null,
            $data['telephone_etablissement']  ?? null,
            $data['telephone_etablissement2'] ?? null,
            $data['email_etablissement']      ?? null,
            $data['slogan_etablissement']     ?? null,
        ]);
        return $code;
    }

    public function update(string $code, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET libelle_etablissement    = ?,
                 adresse_etablissement    = ?,
                 telephone_etablissement  = ?,
                 telephone_etablissement2 = ?,
                 email_etablissement      = ?,
                 slogan_etablissement     = ?,
                 updated_at_etablissement = NOW()
             WHERE code_etablissement = ?"
        );
        $stmt->execute([
            $data['libelle_etablissement'],
            $data['adresse_etablissement']    ?? null,
            $data['telephone_etablissement']  ?? null,
            $data['telephone_etablissement2'] ?? null,
            $data['email_etablissement']      ?? null,
            $data['slogan_etablissement']     ?? null,
            $code,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatut(string $code, string $statut): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_etablissement = ?, updated_at_etablissement = NOW()
             WHERE code_etablissement = ?"
        );
        $stmt->execute([$statut, $code]);
        return $stmt->rowCount() > 0;
    }

    public function updateLogo(string $code, string $logoPath): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET logo_etablissement = ?, updated_at_etablissement = NOW()
             WHERE code_etablissement = ?"
        );
        $stmt->execute([$logoPath, $code]);
        return $stmt->rowCount() > 0;
    }
}
