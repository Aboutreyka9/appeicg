<?php

declare(strict_types=1);

namespace App\Models;

class AccessoireModel extends BaseModel
{
    private string $table  = 'accessoires';
    private string $tableI = 'accessoire_inscription';

    // ─── Accessoires (référentiel) ─────────────────────────────

    public function liste(string $etablissementCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE etablissement_code = ?
             ORDER BY libelle_accessoire ASC"
        );
        $stmt->execute([$etablissementCode]);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE code_accessoire = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function libelleExists(string $libelle, string $etablissementCode, ?string $excludeCode = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table} WHERE libelle_accessoire = ? AND etablissement_code = ?";
        $params = [$libelle, $etablissementCode];
        if ($excludeCode) { $sql .= ' AND code_accessoire != ?'; $params[] = $excludeCode; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('ACC');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_accessoire, libelle_accessoire, etablissement_code, user_code,
              statut_accessoire, created_at_accessoire)
             VALUES (?, ?, ?, ?, 'actif', NOW())"
        );
        $stmt->execute([$code, $data['libelle_accessoire'], $data['etablissement_code'], $data['user_code']]);
        return $code;
    }

    public function update(string $code, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET libelle_accessoire = ?
             WHERE code_accessoire = ? AND etablissement_code = ?"
        );
        $stmt->execute([$data['libelle_accessoire'], $code, $data['etablissement_code']]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatut(string $code, string $statut, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET statut_accessoire = ? WHERE code_accessoire = ? AND etablissement_code = ?"
        );
        $stmt->execute([$statut, $code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    // ─── Accessoires d'une inscription ────────────────────────

    public function listByInscription(string $inscriptionCode, string $etablissementCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT ai.*, a.libelle_accessoire
             FROM {$this->tableI} ai
             JOIN {$this->table} a ON a.code_accessoire = ai.accessoire_code
             WHERE ai.inscription_code = ? AND ai.etablissement_code = ?
               AND ai.statut_accessoire_inscription = 'actif'
             ORDER BY a.libelle_accessoire ASC"
        );
        $stmt->execute([$inscriptionCode, $etablissementCode]);
        return $stmt->fetchAll();
    }

    public function ajouterAInscription(array $data): string
    {
        // Vérifier si déjà présent
        $stmt = $this->db->prepare(
            "SELECT code_accessoire_inscription FROM {$this->tableI}
             WHERE inscription_code = ? AND accessoire_code = ? AND etablissement_code = ?
               AND statut_accessoire_inscription = 'actif' LIMIT 1"
        );
        $stmt->execute([$data['inscription_code'], $data['accessoire_code'], $data['etablissement_code']]);
        if ($stmt->fetch()) return ''; // déjà présent

        $code = $this->generateCode('ACI');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->tableI}
             (code_accessoire_inscription, inscription_code, accessoire_code, annee_code,
              etablissement_code, user_code, statut_accessoire_inscription, created_at_accessoire_inscription)
             VALUES (?, ?, ?, ?, ?, ?, 'actif', NOW())"
        );
        $stmt->execute([
            $code,
            $data['inscription_code'],
            $data['accessoire_code'],
            $data['annee_code'],
            $data['etablissement_code'],
            $data['user_code'],
        ]);
        return $code;
    }

    public function retirerDeInscription(string $code, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->tableI}
             SET statut_accessoire_inscription = 'inactif'
             WHERE code_accessoire_inscription = ? AND etablissement_code = ?"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }
}
