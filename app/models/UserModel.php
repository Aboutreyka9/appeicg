<?php

declare(strict_types=1);

namespace App\Models;

use Exception;

class UserModel extends BaseModel
{
    private string $table = 'users';

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE email_user = ? AND statut_user = 'actif' LIMIT 1"
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findByCode(string $code): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT id_user, code_user, nom_user, prenom_user, email_user,
                    telephone_user, photo_user, etablissement_code, statut_user, last_connexion
             FROM {$this->table}
             WHERE code_user = ? LIMIT 1"
        );
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    public function updateLastConnexion(string $code): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET last_connexion = NOW() WHERE code_user = ?"
        );
        $stmt->execute([$code]);
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('USR');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_user, nom_user, prenom_user, email_user, password_user,
              telephone_user, etablissement_code, created_at_user, statut_user)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'actif')"
        );
        $stmt->execute([
            $code,
            $data['nom_user'],
            $data['prenom_user'],
            $data['email_user'],
            password_hash($data['password_user'], PASSWORD_BCRYPT),
            $data['telephone_user'] ?? null,
            $data['etablissement_code'] ?? null,
        ]);
        return $code;
    }

    public function emailExists(string $email, ?string $excludeCode = null): bool
    {
        $sql  = "SELECT COUNT(*) FROM {$this->table} WHERE email_user = ?";
        $params = [$email];
        if ($excludeCode) {
            $sql    .= ' AND code_user != ?';
            $params[] = $excludeCode;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function liste(string $etablissementCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT id_user, code_user, nom_user, prenom_user, email_user,
                    telephone_user, photo_user, statut_user, last_connexion, created_at_user
             FROM {$this->table}
             WHERE etablissement_code = ?
             ORDER BY nom_user ASC"
        );
        $stmt->execute([$etablissementCode]);
        return $stmt->fetchAll();
    }

    public function updateStatut(string $code, string $statut): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_user = ?, updated_at_user = NOW()
             WHERE code_user = ?"
        );
        $stmt->execute([$statut, $code]);
        return $stmt->rowCount() > 0;
    }

        public function getUserRoles(string $userId): array
    {
        $data = [];
        try {
            $sql = "SELECT r.code_role, r.name,r.description, ur.* FROM roles AS r JOIN user_roles ur ON r.code_role = ur.role_id WHERE ur.user_id = :userId GROUP BY r.code_role";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['userId' => $userId]);
            $data = $stmt->fetchAll();
        } catch (Exception $e) {
            die($e->getMessage());
        }
        return $data;
    }

        public function getUserGroups(string $userId): array
    {
        $data = [];
        try {
            $sql = "SELECT r.groupe FROM roles AS r JOIN user_roles ur ON r.code_role = ur.role_id WHERE ur.user_id = :userId GROUP BY r.groupe";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['userId' => $userId]);
            $data = $stmt->fetchAll();
        } catch (Exception $e) {
            die($e->getMessage());
        }
        return $data;
    }
}
