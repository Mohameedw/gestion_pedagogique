<?php

namespace App\Models;

use App\Core\Model;

final class Professor extends Model
{
    public function profile(int $userId): ?array
    {
        return $this->row(
            'SELECT id_enseignant, nom, prenom, matricule, telephone, specialite
             FROM Enseignant
             WHERE id_utilisateur = ?',
            [$userId]
        );
    }

    public function modules(int $professorId): array
    {
        return $this->rows(
            'SELECT en.id_enseignement, en.id_filiere, en.id_annee,
                    m.code_module, m.nom_module, f.nom_filiere,
                    a.libelle AS annee, s.libelle AS semestre, en.statut
             FROM Enseignement en
             JOIN Module m ON m.id_module = en.id_module
             JOIN Filiere f ON f.id_filiere = en.id_filiere
             JOIN Annee_univ a ON a.id_annee = en.id_annee
             JOIN Semestre s ON s.id_semestre = en.id_semestre
             WHERE en.id_enseignant = ?
             ORDER BY a.date_debut DESC, s.numero_semestre',
            [$professorId]
        );
    }

    public function students(int $professorId): array
    {
        return $this->rows(
            "SELECT DISTINCT e.CNE, e.nom, e.prenom, u.email,
                    m.code_module, m.nom_module, f.nom_filiere
             FROM Enseignement en
             JOIN Module m ON m.id_module = en.id_module
             JOIN Inscription i ON i.id_filiere = en.id_filiere
                 AND i.id_annee = en.id_annee
                 AND i.statut = 'VALIDEE'
             JOIN Etudiant e ON e.id_etudiant = i.id_etudiant
             JOIN Utilisateur u ON u.id_utilisateur = e.id_utilisateur
             JOIN Filiere f ON f.id_filiere = en.id_filiere
             WHERE en.id_enseignant = ?
             ORDER BY m.nom_module, e.nom",
            [$professorId]
        );
    }

    public function evaluations(int $professorId): array
    {
        return $this->rows(
            'SELECT m.code_module, m.nom_module, ev.libelle,
                    ev.type_evaluation, ev.date_evaluation, ev.note_max
             FROM Evaluation ev
             JOIN Enseignement en ON en.id_enseignement = ev.id_enseignement
             JOIN Module m ON m.id_module = en.id_module
             WHERE en.id_enseignant = ?
             ORDER BY ev.date_evaluation DESC',
            [$professorId]
        );
    }

    public function gradeRows(int $professorId): array
    {
        return $this->rows(
            'SELECT ev.id_evaluation, i.id_inscription,
                    m.code_module, ev.libelle AS evaluation,
                    e.CNE, CONCAT(e.prenom, " ", e.nom) AS etudiant,
                    n.valeur, ev.note_max, n.statut_validation
             FROM Evaluation ev
             JOIN Enseignement en ON en.id_enseignement = ev.id_enseignement
             JOIN Module m ON m.id_module = en.id_module
             JOIN Inscription i ON i.id_filiere = en.id_filiere
                 AND i.id_annee = en.id_annee
                 AND i.statut = "VALIDEE"
             JOIN Etudiant e ON e.id_etudiant = i.id_etudiant
             LEFT JOIN Note n ON n.id_evaluation = ev.id_evaluation
                 AND n.id_inscription = i.id_inscription
             WHERE en.id_enseignant = ?
             ORDER BY ev.date_evaluation DESC, e.nom',
            [$professorId]
        );
    }

    public function stats(int $professorId): array
    {
        $stats = [count($this->modules($professorId))];

        foreach ([
            'SELECT COUNT(*) FROM Evaluation ev
             JOIN Enseignement en ON en.id_enseignement = ev.id_enseignement
             WHERE en.id_enseignant = ?',

            'SELECT COUNT(*) FROM Note n
             JOIN Evaluation ev ON ev.id_evaluation = n.id_evaluation
             JOIN Enseignement en ON en.id_enseignement = ev.id_enseignement
             WHERE en.id_enseignant = ?'
        ] as $sql) {
            $query = $this->db->prepare($sql);
            $query->execute([$professorId]);
            $stats[] = (int) $query->fetchColumn();
        }

        return $stats;
    }
}
