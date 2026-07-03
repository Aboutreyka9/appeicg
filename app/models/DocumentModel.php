<?php

declare(strict_types=1);

namespace App\Models;

class DocumentModel extends BaseModel
{
    // ⚠️ Incohérence connue : colonne "etablisement_code" (sans 2e 's') dans cette table
    private string $table = 'documents';

    public function liste(string $etablissementCode, array $filters = []): array
    {
        $sql    = "SELECT * FROM {$this->table} WHERE etablisement_code = ?";
        $params = [$etablissementCode];

        if (!empty($filters['filiere_code'])) {
            $sql    .= ' AND filiere_code = ?';
            $params[] = $filters['filiere_code'];
        }
        if (!empty($filters['niveaux_code'])) {
            $sql    .= ' AND niveaux_code = ?';
            $params[] = $filters['niveaux_code'];
        }
        if (!empty($filters['annee_code'])) {
            $sql    .= ' AND annee_code = ?';
            $params[] = $filters['annee_code'];
        }

        // ⚠️ statut_document a des valeurs invalides ('innactif', '')
        // On filtre uniquement sur 'actif' pour être sûr
        $sql .= " AND statut_document = 'actif'";
        $sql .= ' ORDER BY id_document DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE id_document = ? AND etablisement_code = ? LIMIT 1"
        );
        $stmt->execute([$id, $etablissementCode]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (libelle_document, lien_document, filiere_code, annee_code,
              niveaux_code, etablisement_code, statut_document)
             VALUES (?, ?, ?, ?, ?, ?, 'actif')"
        );
        $stmt->execute([
            $data['libelle_document'],
            $data['lien_document'],
            $data['filiere_code'],
            $data['annee_code'],
            $data['niveaux_code'],
            $data['etablissement_code'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET libelle_document = ?, lien_document = ?,
                 filiere_code = ?, annee_code = ?, niveaux_code = ?
             WHERE id_document = ? AND etablisement_code = ?"
        );
        $stmt->execute([
            $data['libelle_document'],
            $data['lien_document'],
            $data['filiere_code'],
            $data['annee_code'],
            $data['niveaux_code'],
            $id,
            $data['etablissement_code'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public function supprimer(int $id, string $etablissementCode): bool
    {
        // ⚠️ Pas de 'inactif' valide dans l'enum — on utilise une suppression physique ici
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE id_document = ? AND etablisement_code = ?"
        );
        $stmt->execute([$id, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }
}
