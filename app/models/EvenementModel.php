<?php

declare(strict_types=1);

namespace App\Models;

class EvenementModel extends BaseModel
{
    // ⚠️ Incohérence : statut utilise 'innactif' (et non 'inactif') dans cette table
    private string $table = 'evenements';

    public function liste(bool $activeOnly = false): array
    {
        $sql    = "SELECT * FROM {$this->table}";
        $params = [];
        if ($activeOnly) {
            // ⚠️ 'actif' est la valeur correcte pour les actifs dans evenements
            $sql    .= " WHERE statut_evenement = 'actif'";
        }
        $sql .= ' ORDER BY date_creation_evenement DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE id_evenement = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (titre_evenement, description_evenement, image_evenement,
              is_principal_evenement, statut_evenement, date_creation_evenement)
             VALUES (?, ?, ?, ?, 'actif', NOW())"
        );
        $stmt->execute([
            $data['titre_evenement'],
            $data['description_evenement'] ?: null,
            $data['image_evenement']       ?: null,
            $data['is_principal_evenement'] ? 1 : 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET titre_evenement        = ?,
                 description_evenement  = ?,
                 image_evenement        = ?,
                 is_principal_evenement = ?,
                 date_modification_evenement = NOW()
             WHERE id_evenement = ?"
        );
        $stmt->execute([
            $data['titre_evenement'],
            $data['description_evenement'] ?: null,
            $data['image_evenement']       ?: null,
            $data['is_principal_evenement'] ? 1 : 0,
            $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatut(int $id, string $statut): bool
    {
        // ⚠️ 'innactif' (double n) est la valeur enum pour inactif dans cette table
        $statutDb = $statut === 'inactif' ? 'innactif' : 'actif';
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_evenement = ?, date_modification_evenement = NOW()
             WHERE id_evenement = ?"
        );
        $stmt->execute([$statutDb, $id]);
        return $stmt->rowCount() > 0;
    }

    public function supprimer(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id_evenement = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
