<?php

declare(strict_types=1);

namespace App\Models;

class ParentModel extends BaseModel
{
    private string $table = 'parents';

    public function findByEtudiant(string $etudiantCode, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE etudiant_code = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$etudiantCode, $etablissementCode]);
        return $stmt->fetch();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE code_parent = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function save(string $etudiantCode, array $data): string
    {
        // Vérifier si une fiche parent existe déjà pour cet étudiant
        $existing = $this->findByEtudiant($etudiantCode, $data['etablissement_code']);

        if ($existing) {
            // Mise à jour
            $stmt = $this->db->prepare(
                "UPDATE {$this->table}
                 SET nom_pere           = ?,
                     telephone_pere     = ?,
                     profession_pere    = ?,
                     nom_mere           = ?,
                     telephone_mere     = ?,
                     profession_mere    = ?,
                     nom_tuteur         = ?,
                     telephone_tuteur   = ?,
                     updated_at_parent  = NOW()
                 WHERE code_parent = ? AND etablissement_code = ?"
            );
            $stmt->execute([
                $data['nom_pere']         ?: null,
                $data['telephone_pere']   ?: null,
                $data['profession_pere']  ?: null,
                $data['nom_mere']         ?: null,
                $data['telephone_mere']   ?: null,
                $data['profession_mere']  ?: null,
                $data['nom_tuteur']       ?: null,
                $data['telephone_tuteur'] ?: null,
                $existing['code_parent'],
                $data['etablissement_code'],
            ]);
            return $existing['code_parent'];
        }

        // Création
        $code = $this->generateCode('PAR');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_parent, nom_pere, telephone_pere, profession_pere,
              nom_mere, telephone_mere, profession_mere,
              nom_tuteur, telephone_tuteur,
              etudiant_code, user_code, etablissement_code, created_at_parent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $code,
            $data['nom_pere']         ?: null,
            $data['telephone_pere']   ?: null,
            $data['profession_pere']  ?: null,
            $data['nom_mere']         ?: null,
            $data['telephone_mere']   ?: null,
            $data['profession_mere']  ?: null,
            $data['nom_tuteur']       ?: null,
            $data['telephone_tuteur'] ?: null,
            $etudiantCode,
            $data['user_code'],
            $data['etablissement_code'],
        ]);
        return $code;
    }
}
