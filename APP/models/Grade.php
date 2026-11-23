<?php

namespace App\Models;

use App\Core\Model;
use RuntimeException;

final class Grade extends Model
{
    public function saveForProfessor(
        int $professorId,
        int $evaluationId,
        int $registrationId,
        float $value,
        string $observation
    ): void {
        $check = $this->db->prepare(
            "SELECT ev.note_max
             FROM Evaluation ev
             JOIN Enseignement en
                ON en.id_enseignement = ev.id_enseignement
             JOIN Inscription i
                ON i.id_filiere = en.id_filiere
                AND i.id_annee = en.id_annee
             WHERE ev.id_evaluation = ?
                AND en.id_enseignant = ?
                AND i.id_inscription = ?
                AND i.statut = 'VALIDEE'"
        );

        $check->execute([$evaluationId, $professorId, $registrationId]);
        $limit = $check->fetchColumn();

        if ($limit === false) {
            throw new RuntimeException(
                'Cette évaluation ou cette inscription ne fait pas partie de vos enseignements.'
            );
        }

        if ($value < 0 || $value > (float) $limit) {
            throw new RuntimeException(
                'La note doit être comprise entre 0 et ' . $limit . '.'
            );
        }

        $query = $this->db->prepare(
            'SELECT id_note FROM Note
             WHERE id_evaluation = ? AND id_inscription = ?'
        );

        $query->execute([$evaluationId, $registrationId]);
        $noteId = $query->fetchColumn();

        if ($noteId) {
            $save = $this->db->prepare(
                'UPDATE Note
                 SET valeur = ?, observation = ?, date_saisie = NOW()
                 WHERE id_note = ?'
            );

            $save->execute([$value, $observation ?: null, $noteId]);
            return;
        }

        $save = $this->db->prepare(
            "INSERT INTO Note
             (id_evaluation, id_inscription, valeur, observation, statut_validation)
             VALUES (?, ?, ?, ?, 'BROUILLON')"
        );

        $save->execute([
            $evaluationId,
            $registrationId,
            $value,
            $observation ?: null
        ]);
    }

    public function evaluationOptions(int $professorId): array
    {
        $query = $this->db->prepare(
            'SELECT ev.id_evaluation, ev.libelle, ev.note_max, m.code_module
             FROM Evaluation ev
             JOIN Enseignement en
                ON en.id_enseignement = ev.id_enseignement
             JOIN Module m ON m.id_module = en.id_module
             WHERE en.id_enseignant = ?
             ORDER BY ev.date_evaluation DESC'
        );

        $query->execute([$professorId]);

        return $query->fetchAll();
    }

    public function registrationOptions(int $professorId): array
    {
        $query = $this->db->prepare(
            'SELECT DISTINCT i.id_inscription, e.CNE, e.prenom, e.nom,
                    m.code_module
             FROM Inscription i
             JOIN Etudiant e ON e.id_etudiant = i.id_etudiant
             JOIN Enseignement en
                ON en.id_filiere = i.id_filiere
                AND en.id_annee = i.id_annee
             JOIN Module m ON m.id_module = en.id_module
             WHERE en.id_enseignant = ?
                AND i.statut = "VALIDEE"
             ORDER BY e.nom'
        );

        $query->execute([$professorId]);

        return $query->fetchAll();
    }
}
