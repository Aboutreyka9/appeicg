<?php

declare(strict_types=1);

namespace App\Models;

class DossierEtudiantModel extends BaseModel
{
    private string $table = 'dossier_etudiant';

    public function listByEtudiant(string $etudiantCode, string $etablissementCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE etudiant_code = ? AND etablissement_code = ?
             ORDER BY created_at_dossier_etudiant DESC"
        );
        $stmt->execute([$etudiantCode, $etablissementCode]);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE code_dossier_etudiant = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('DOS');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_dossier_etudiant, etudiant_code, libelle_dossier, annee_code,
              user_code, etablissement_code, created_at_dossier_etudiant)
             VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $code,
            $data['etudiant_code'],
            $data['libelle_dossier'],
            $data['annee_code'],
            $data['user_code'],
            $data['etablissement_code'],
        ]);
        return $code;
    }

    public function delete(string $code, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table}
             WHERE code_dossier_etudiant = ? AND etablissement_code = ?"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }
}
