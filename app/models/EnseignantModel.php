<?php

declare(strict_types=1);

namespace App\Models;

class EnseignantModel extends BaseModel
{
    private string $table = 'enseignants';

    public function liste(string $etablissementCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT e.*,
                    GROUP_CONCAT(m.libelle_matiere ORDER BY m.libelle_matiere SEPARATOR ', ') AS matieres_libelles
             FROM {$this->table} e
             LEFT JOIN enseignant_matiere em ON em.enseignant_code = e.code_enseignant
                 AND em.statut_enseignant_matiere = 'actif'
             LEFT JOIN matieres m ON m.code_matiere = em.matiere_code
             WHERE e.etablissement_code = ?
             GROUP BY e.id_enseignant
             ORDER BY e.nom_enseignant ASC"
        );
        $stmt->execute([$etablissementCode]);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE code_enseignant = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function matriculeExists(string $matricule, string $etablissementCode, ?string $excludeCode = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table} WHERE matricule = ? AND etablissement_code = ?";
        $params = [$matricule, $etablissementCode];
        if ($excludeCode) {
            $sql    .= ' AND code_enseignant != ?';
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
        return 'ENS-' . date('Y') . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('ENS');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_enseignant, matricule, nom_enseignant, date_naissance, lieu_naissance,
              sexe, telephone, email, statut_enseignant, etablissement_code, user_code, created_at_enseignant)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'actif', ?, ?, NOW())"
        );
        $stmt->execute([
            $code,
            $data['matricule'],
            $data['nom_enseignant'],
            $data['date_naissance']  ?: null,
            $data['lieu_naissance']  ?: null,
            $data['sexe']            ?: null,
            $data['telephone'],
            $data['email']           ?: null,
            $data['etablissement_code'],
            $data['user_code'],
        ]);
        return $code;
    }

    public function update(string $code, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET nom_enseignant = ?, date_naissance = ?, lieu_naissance = ?,
                 sexe = ?, telephone = ?, email = ?, updated_at_enseignant = NOW()
             WHERE code_enseignant = ? AND etablissement_code = ?"
        );
        $stmt->execute([
            $data['nom_enseignant'],
            $data['date_naissance']  ?: null,
            $data['lieu_naissance']  ?: null,
            $data['sexe']            ?: null,
            $data['telephone'],
            $data['email']           ?: null,
            $code,
            $data['etablissement_code'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatut(string $code, string $statut, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_enseignant = ?, updated_at_enseignant = NOW()
             WHERE code_enseignant = ? AND etablissement_code = ?"
        );
        $stmt->execute([$statut, $code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }
}
