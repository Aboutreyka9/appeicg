<?php

declare(strict_types=1);

namespace App\Models;

class MessageModel extends BaseModel
{
    private string $table = 'messages';

    public function liste(array $filters = []): array
    {
        $sql    = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['statut'])) {
            $sql    .= ' AND statut_message = ?';
            $params[] = $filters['statut'];
        }

        $sql .= ' ORDER BY created_at_message DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE id_message = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (objet_message, description_message, statut_message, created_at_message)
             VALUES (?, ?, 'en_attente', NOW())"
        );
        $stmt->execute([
            $data['objet_message'],
            $data['description_message'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateStatut(int $id, string $statut): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_message = ?, update_at_message = NOW()
             WHERE id_message = ?"
        );
        $stmt->execute([$statut, $id]);
        return $stmt->rowCount() > 0;
    }

    public function supprimer(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id_message = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
