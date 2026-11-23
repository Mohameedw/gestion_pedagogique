<?php

namespace App\Models;

use App\Core\Model;

final class Student extends Model
{
    public function profile(int $userId): ?array
    {
        return $this->row(
            'SELECT id_etudiant, CNE, nom, prenom, date_naissance,
                    telephone, adresse
             FROM Etudiant
             WHERE id_utilisateur = ?',
            [$userId]
        );
    }

    public function academicRows(int $studentId, string $page): array
    {
        $select = 'SELECT m.code_module, m.nom_module, f.nom_filiere,
                          a.libelle AS annee, s.libelle AS semestre';

        $extra = '';

        if ($page === 'evaluations') {
            $extra = ', ev.libelle AS evaluation, ev.type_evaluation,
                         ev.date_evaluation, ev.note_max';
        }

        if ($page === 'grades') {
            $extra = ', ev.libelle AS evaluation, ev.note_max,
                         n.valeur, n.statut_validation';
        }

        $joins = ' FROM Inscription i
                   JOIN Enseignement en
                     ON en.id_filiere = i.id_filiere
                     AND en.id_annee = i.id_annee
                   JOIN Module m ON m.id_module = en.id_module
                   JOIN Filiere f ON f.id_filiere = en.id_filiere
                   JOIN Annee_univ a ON a.id_annee = en.id_annee
                   JOIN Semestre s ON s.id_semestre = en.id_semestre';

        if ($page !== 'modules') {
            $joins .= ' LEFT JOIN Evaluation ev
                          ON ev.id_enseignement = en.id_enseignement
                        LEFT JOIN Note n
                          ON n.id_evaluation = ev.id_evaluation
                          AND n.id_inscription = i.id_inscription
                          AND n.statut_validation = "VALIDEE"';
        }

        return $this->rows(
            $select . $extra . $joins .
            ' WHERE i.id_etudiant = ?
              AND i.statut = "VALIDEE"
              ORDER BY a.date_debut DESC,
                       s.numero_semestre,
                       m.nom_module',
            [$studentId]
        );
    }

    public function stats(int $studentId): array
    {
        $query = $this->db->prepare(
            'SELECT COUNT(DISTINCT en.id_enseignement)
             FROM Inscription i
             JOIN Enseignement en
               ON en.id_filiere = i.id_filiere
               AND en.id_annee = i.id_annee
             WHERE i.id_etudiant = ?
               AND i.statut = "VALIDEE"'
        );

        $query->execute([$studentId]);
        $modules = (int) $query->fetchColumn();

        $query = $this->db->prepare(
            'SELECT COUNT(*)
             FROM Note n
             JOIN Inscription i
               ON i.id_inscription = n.id_inscription
             WHERE i.id_etudiant = ?
               AND n.statut_validation = "VALIDEE"'
        );

        $query->execute([$studentId]);

        return [$modules, (int) $query->fetchColumn()];
    }
}
