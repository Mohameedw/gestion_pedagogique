<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use Throwable;

final class AdminAccount extends Model
{
    public function createStudent(array $data): void
    {
        $this->db->beginTransaction();

        try {
            $user = new User($this->db);

            $userId = $user->create(
                $data['nom_utilisateur'],
                $data['email'],
                $data['password'],
                'ETUDIANT'
            );

            $sql = 'INSERT INTO Etudiant
                    (id_utilisateur, CNE, nom, prenom,
                     date_naissance, telephone, adresse)
                    VALUES
                    (:id_utilisateur, :CNE, :nom, :prenom,
                     :date_naissance, :telephone, :adresse)';

            $query = $this->db->prepare($sql);

            $query->execute([
                'id_utilisateur' => $userId,
                'CNE' => $data['CNE'],
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'date_naissance' => $data['date_naissance'] ?: null,
                'telephone' => $data['telephone'] ?: null,
                'adresse' => $data['adresse'] ?: null
            ]);

            $this->db->commit();

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
    public function createTeacher(array $data): void
{
    $this->db->beginTransaction();

    try {
        $user = new User($this->db);

        $userId = $user->create(
            $data['nom_utilisateur'],
            $data['email'],
            $data['password'],
            'ENSEIGNANT'
        );

        $sql = 'INSERT INTO Enseignant
                (id_utilisateur, matricule, nom, prenom,
                 telephone, specialite)
                VALUES
                (:id_utilisateur, :matricule, :nom, :prenom,
                 :telephone, :specialite)';

        $query = $this->db->prepare($sql);

        $query->execute([
            'id_utilisateur' => $userId,
            'matricule' => $data['matricule'],
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'telephone' => $data['telephone'] ?: null,
            'specialite' => $data['specialite'] ?: null
        ]);

        $this->db->commit();

    } catch (Throwable $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }

        throw $e;
    }
}
public function updateStudent(int $id, array $data): void
{
    $sql = 'UPDATE Etudiant
            SET CNE = :CNE,
                nom = :nom,
                prenom = :prenom,
                date_naissance = :date_naissance,
                telephone = :telephone,
                adresse = :adresse
            WHERE id_etudiant = :id';

    $query = $this->db->prepare($sql);

    $query->execute([
        'CNE' => $data['CNE'],
        'nom' => $data['nom'],
        'prenom' => $data['prenom'],
        'date_naissance' => $data['date_naissance'] ?: null,
        'telephone' => $data['telephone'] ?: null,
        'adresse' => $data['adresse'] ?: null,
        'id' => $id
    ]);
}
public function findStudent(int $id): ?array
{
    $sql = 'SELECT CNE, nom, prenom, date_naissance,
                   telephone, adresse
            FROM Etudiant
            WHERE id_etudiant = :id';

    $query = $this->db->prepare($sql);
    $query->execute(['id' => $id]);

    return $query->fetch() ?: null;
}
public function deleteStudent(int $id): void
{
    $this->db->beginTransaction();

    try {
        $query = $this->db->prepare(
            'SELECT id_utilisateur
             FROM Etudiant
             WHERE id_etudiant = :id'
        );

        $query->execute(['id' => $id]);
        $student = $query->fetch();

        if (!$student) {
            throw new \RuntimeException('Étudiant introuvable.');
        }

        $query = $this->db->prepare(
            'SELECT COUNT(*)
             FROM Inscription
             WHERE id_etudiant = :id'
        );

        $query->execute(['id' => $id]);

        if ((int) $query->fetchColumn() > 0) {
            throw new \RuntimeException(
                'Impossible de supprimer cet étudiant : il possède des inscriptions.'
            );
        }

        // Supprimer le compte : le trigger supprimera le profil.
        $query = $this->db->prepare(
            'DELETE FROM Utilisateur
             WHERE id_utilisateur = :id'
        );

        $query->execute([
            'id' => $student['id_utilisateur']
        ]);

        $this->db->commit();

    } catch (\Throwable $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }

        throw $e;
    }
}
public function findTeacher(int $id): ?array
{
    $query = $this->db->prepare(
        'SELECT matricule, nom, prenom, telephone, specialite
         FROM Enseignant
         WHERE id_enseignant = :id'
    );

    $query->execute(['id' => $id]);

    return $query->fetch() ?: null;
}

public function updateTeacher(int $id, array $data): void
{
    $query = $this->db->prepare(
        'UPDATE Enseignant
         SET matricule = :matricule,
             nom = :nom,
             prenom = :prenom,
             telephone = :telephone,
             specialite = :specialite
         WHERE id_enseignant = :id'
    );

    $query->execute([
        'matricule' => $data['matricule'],
        'nom' => $data['nom'],
        'prenom' => $data['prenom'],
        'telephone' => $data['telephone'] ?: null,
        'specialite' => $data['specialite'] ?: null,
        'id' => $id
    ]);
}
public function deleteTeacher(int $id): void
{
    $this->db->beginTransaction();

    try {
        $query = $this->db->prepare(
            'SELECT id_utilisateur
             FROM Enseignant
             WHERE id_enseignant = :id'
        );

        $query->execute(['id' => $id]);
        $teacher = $query->fetch();

        if (!$teacher) {
            throw new \RuntimeException('Enseignant introuvable.');
        }

        $query = $this->db->prepare(
            'SELECT COUNT(*)
             FROM Enseignement
             WHERE id_enseignant = :id'
        );

        $query->execute(['id' => $id]);

        if ((int) $query->fetchColumn() > 0) {
            throw new \RuntimeException(
                'Impossible de supprimer cet enseignant : il possède des enseignements.'
            );
        }

        // Le trigger supprimera automatiquement le profil enseignant.
        $query = $this->db->prepare(
            'DELETE FROM Utilisateur
             WHERE id_utilisateur = :id'
        );

        $query->execute([
            'id' => $teacher['id_utilisateur']
        ]);

        $this->db->commit();

    } catch (\Throwable $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }

        throw $e;
    }
}
public function findAdminProfile(int $userId): ?array
{
    $sql = 'SELECT id_utilisateur, nom_utilisateur, email, role
            FROM Utilisateur
            WHERE id_utilisateur = :id
            AND role = "ADMIN"
            LIMIT 1';

    $query = $this->db->prepare($sql);
    $query->execute(['id' => $userId]);

    return $query->fetch(PDO::FETCH_ASSOC) ?: null;
}
}