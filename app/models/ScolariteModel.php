<?php

declare(strict_types=1);

namespace App\Models;

class ScolariteModel extends BaseModel
{
    private string $table = 'scolarites';

    public function liste(string $etablissementCode, ?string $anneeCode = null): array
    {
        $sql    = "SELECT s.*, n.libelle_niveau, f.libelle_filiere
                   FROM {$this->table} s
                   LEFT JOIN niveaux n  ON n.code_niveau  = s.niveau_code
                   LEFT JOIN filieres f ON f.code_filiere = s.filiere_code
                   WHERE s.statut_scolarite = 'actif'";
        $params = [];

        if ($anneeCode) {
            $sql    .= ' AND s.annee_code = ?';
            $params[] = $anneeCode;
        }

        $sql .= ' ORDER BY f.libelle_filiere ASC, n.libelle_niveau ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE code_scolarite = ? LIMIT 1"
        );
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    public function findByNiveauAnnee(string $niveauCode, string $filiereCode, string $anneeCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE niveau_code = ? AND filiere_code = ? AND annee_code = ?
               AND statut_scolarite = 'actif' LIMIT 1"
        );
        $stmt->execute([$niveauCode, $filiereCode, $anneeCode]);
        return $stmt->fetch();
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('SCO');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_scolarite, montant_scolarite, niveau_code, filiere_code,
              annee_code, user_code, statut_scolarite, created_at_scolarite)
             VALUES (?, ?, ?, ?, ?, ?, 'actif', NOW())"
        );
        $stmt->execute([
            $code,
            $data['montant_scolarite'],
            $data['niveau_code']  ?: null,
            $data['filiere_code'] ?: null,
            $data['annee_code']   ?: null,
            $data['user_code'],
        ]);
        return $code;
    }

    public function update(string $code, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET montant_scolarite = ?, niveau_code = ?, filiere_code = ?,
                 annee_code = ?, updated_at_scolarite = NOW()
             WHERE code_scolarite = ?"
        );
        $stmt->execute([
            $data['montant_scolarite'],
            $data['niveau_code']  ?: null,
            $data['filiere_code'] ?: null,
            $data['annee_code']   ?: null,
            $code,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(string $code): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET statut_scolarite = 'inactif', updated_at_scolarite = NOW()
             WHERE code_scolarite = ?"
        );
        $stmt->execute([$code]);
        return $stmt->rowCount() > 0;
    }
}
