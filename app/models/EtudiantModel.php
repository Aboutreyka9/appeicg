<?php

declare(strict_types=1);

namespace App\Models;

class EtudiantModel extends BaseModel
{
    private string $table = 'etudiants';

    public function liste(string $etablissementCode, array $filters = []): array
    {
        $sql    = "SELECT * FROM {$this->table} WHERE etablissement_code = ?";
        $params = [$etablissementCode];

        if (!empty($filters['statut'])) {
            $sql    .= ' AND statut_etudiant = ?';
            $params[] = $filters['statut'];
        }
        if (!empty($filters['search'])) {
            $sql    .= ' AND (nom_etudiant LIKE ? OR prenom_etudiant LIKE ? OR matricule_etudiant LIKE ?)';
            $s        = '%' . $filters['search'] . '%';
            $params[] = $s; $params[] = $s; $params[] = $s;
        }

        $sql .= ' ORDER BY nom_etudiant ASC, prenom_etudiant ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE code_etudiant = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function matriculeExists(string $matricule, string $etablissementCode, ?string $excludeCode = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table} WHERE matricule_etudiant = ? AND etablissement_code = ?";
        $params = [$matricule, $etablissementCode];
        if ($excludeCode) {
            $sql    .= ' AND code_etudiant != ?';
            $params[] = $excludeCode;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function generateMatricule(string $etablissementCode): string
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE etablissement_code = ?"
        );
        $stmt->execute([$etablissementCode]);
        $count = (int) $stmt->fetchColumn() + 1;
        return 'ETU-' . date('Y') . '-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('ETU');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_etudiant, matricule_etudiant, nom_etudiant, prenom_etudiant,
              date_naissance_etudiant, lieu_naissance_etudiant, sexe_etudiant,
              nationalite_etudiant, lieu_residence_etudiant, telephone_etudiant,
              email_etudiant, numero_cni, statut_etudiant,
              etablissement_code, user_code, created_at_etudiant)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', ?, ?, NOW())"
        );
        $stmt->execute([
            $code,
            $data['matricule_etudiant'],
            $data['nom_etudiant'],
            $data['prenom_etudiant'],
            $data['date_naissance_etudiant']  ?: null,
            $data['lieu_naissance_etudiant']  ?: null,
            $data['sexe_etudiant']            ?: null,
            $data['nationalite_etudiant']     ?: null,
            $data['lieu_residence_etudiant']  ?: null,
            $data['telephone_etudiant']       ?: null,
            $data['email_etudiant']           ?: null,
            $data['numero_cni']               ?: null,
            $data['etablissement_code'],
            $data['user_code'],
        ]);
        return $code;
    }

    public function update(string $code, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET nom_etudiant              = ?,
                 prenom_etudiant           = ?,
                 date_naissance_etudiant   = ?,
                 lieu_naissance_etudiant   = ?,
                 sexe_etudiant             = ?,
                 nationalite_etudiant      = ?,
                 lieu_residence_etudiant   = ?,
                 telephone_etudiant        = ?,
                 email_etudiant            = ?,
                 numero_cni                = ?,
                 updated_at_etudiant       = NOW()
             WHERE code_etudiant = ? AND etablissement_code = ?"
        );
        $stmt->execute([
            $data['nom_etudiant'],
            $data['prenom_etudiant'],
            $data['date_naissance_etudiant']  ?: null,
            $data['lieu_naissance_etudiant']  ?: null,
            $data['sexe_etudiant']            ?: null,
            $data['nationalite_etudiant']     ?: null,
            $data['lieu_residence_etudiant']  ?: null,
            $data['telephone_etudiant']       ?: null,
            $data['email_etudiant']           ?: null,
            $data['numero_cni']               ?: null,
            $code,
            $data['etablissement_code'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatut(string $code, string $statut, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_etudiant = ?, updated_at_etudiant = NOW()
             WHERE code_etudiant = ? AND etablissement_code = ?"
        );
        $stmt->execute([$statut, $code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    public function hasInscriptions(string $code, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM inscriptions WHERE etudiant_code = ? AND etablissement_code = ?"
        );
        $stmt->execute([$code, $etablissementCode]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
