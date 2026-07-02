<?php

declare(strict_types=1);

namespace App\Models;

class ClasseModel extends BaseModel
{
    private string $table = 'classes';

    public function liste(string $etablissementCode, ?string $anneeCode = null, ?string $niveauCode = null): array
    {
        $sql    = "SELECT cl.*, n.libelle_niveau, f.libelle_filiere, f.code_filiere, a.libelle_annee
                   FROM {$this->table} cl
                   LEFT JOIN niveaux n  ON n.code_niveau   = cl.niveau_code
                   LEFT JOIN filieres f ON f.code_filiere  = n.filiere_code
                   LEFT JOIN annees a   ON a.libelle_annee = cl.annee_code
                   WHERE cl.etablissement_code = ?";
        $params = [$etablissementCode];
        if ($anneeCode) {
            $sql    .= ' AND cl.annee_code = ?';
            $params[] = $anneeCode;
        }
        if ($niveauCode) {
            $sql    .= ' AND cl.niveau_code = ?';
            $params[] = $niveauCode;
        }
        $sql .= ' ORDER BY f.libelle_filiere ASC, n.libelle_niveau ASC, cl.libelle_classe ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE code_classe = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function libelleExists(string $libelle, string $niveauCode, string $anneeCode, string $etablissementCode, ?string $excludeCode = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table}
                   WHERE libelle_classe = ? AND niveau_code = ? AND annee_code = ? AND etablissement_code = ?";
        $params = [$libelle, $niveauCode, $anneeCode, $etablissementCode];
        if ($excludeCode) {
            $sql    .= ' AND code_classe != ?';
            $params[] = $excludeCode;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('CLS');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_classe, libelle_classe, capacite_max_classe, etablissement_code,
              niveau_code, annee_code, created_at_classe, statut_classe, user_code)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), 'actif', ?)"
        );
        $stmt->execute([
            $code,
            $data['libelle_classe'],
            $data['capacite_max_classe'] ?: null,
            $data['etablissement_code'],
            $data['niveau_code'],
            $data['annee_code'],
            $data['user_code'],
        ]);
        return $code;
    }

    public function update(string $code, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET libelle_classe = ?, capacite_max_classe = ?, niveau_code = ?,
                 annee_code = ?, updated_at_classe = NOW()
             WHERE code_classe = ? AND etablissement_code = ?"
        );
        $stmt->execute([
            $data['libelle_classe'],
            $data['capacite_max_classe'] ?: null,
            $data['niveau_code'],
            $data['annee_code'],
            $code,
            $data['etablissement_code'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatut(string $code, string $statut, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_classe = ?, updated_at_classe = NOW()
             WHERE code_classe = ? AND etablissement_code = ?"
        );
        $stmt->execute([$statut, $code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    public function hasInscriptions(string $code, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM inscriptions WHERE classe_code = ? AND etablissement_code = ?"
        );
        $stmt->execute([$code, $etablissementCode]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function countEtudiants(string $code): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM inscriptions WHERE classe_code = ? AND statut_inscription != 'annule'"
        );
        $stmt->execute([$code]);
        return (int) $stmt->fetchColumn();
    }
}
