<?php

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT id_utilisateur, nom_utilisateur, email,
                       mot_de_passe_hash, role, actif
                FROM Utilisateur
                WHERE email = :email
                LIMIT 1';

        return $this->row($sql, ['email' => $email]);
    }

    public function create(
        string $username,
        string $email,
        string $password,
        string $role
    ): int {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = 'INSERT INTO Utilisateur
                (nom_utilisateur, email, mot_de_passe_hash, role, actif)
                VALUES (:username, :email, :password, :role, 1)';

        $query = $this->db->prepare($sql);

        $query->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hash,
            'role' => $role
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function changePassword(
        int $userId,
        string $currentPassword,
        string $newPassword
    ): string {
        $sql = 'SELECT mot_de_passe_hash
                FROM Utilisateur
                WHERE id_utilisateur = :id
                LIMIT 1';

        $user = $this->row($sql, ['id' => $userId]);

        if (!$user) {
            return 'not_found';
        }

        if (!password_verify(
            $currentPassword,
            $user['mot_de_passe_hash']
        )) {
            return 'invalid_current';
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $sql = 'UPDATE Utilisateur
                SET mot_de_passe_hash = :password
                WHERE id_utilisateur = :id';

        $query = $this->db->prepare($sql);

        return $query->execute([
            'password' => $newHash,
            'id' => $userId
        ]) ? 'success' : 'error';
    }
}
