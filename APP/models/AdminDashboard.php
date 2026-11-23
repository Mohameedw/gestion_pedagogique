<?php

namespace App\Models;

use App\Core\Model;

final class AdminDashboard extends Model
{
    private const PAGES = [

        'students' => [
            'title' => 'Étudiants',
            'sql' => '
                SELECT e.id_etudiant, e.CNE, e.nom, e.prenom,
                       u.email, e.telephone
                FROM Etudiant e
                JOIN Utilisateur u
                    ON u.id_utilisateur = e.id_utilisateur
                ORDER BY e.nom
            '
        ],

        'teachers' => [
            'title' => 'Enseignants',
            'sql' => '
                SELECT e.id_enseignant, e.matricule, e.nom, e.prenom,
                       u.email, e.telephone, e.specialite
                FROM Enseignant e
                JOIN Utilisateur u
                    ON u.id_utilisateur = e.id_utilisateur
                ORDER BY e.nom
            '
        ],


        'departments' => [
            'title' => 'Départements',
            'sql' => '
                SELECT code_dept, nom_dept, description
                FROM Departement
                ORDER BY nom_dept
            '
        ],

        'formations' => [
            'title' => 'Formations',
            'sql' => '
                SELECT f.code_formation, f.nom_formation, f.niveau,
                       d.nom_dept AS departement
                FROM Formation f
                JOIN Departement d
                    ON d.id_departement = f.id_departement
                ORDER BY f.nom_formation
            '
        ],

        'filieres' => [
            'title' => 'Filières',
            'sql' => '
                SELECT f.code_filiere, f.nom_filiere,
                       fo.nom_formation AS formation
                FROM Filiere f
                JOIN Formation fo
                    ON fo.id_formation = f.id_formation
                ORDER BY f.nom_filiere
            '
        ],

        'modules' => [
            'title' => 'Modules',
            'sql' => '
                SELECT code_module, nom_module, description
                FROM Module
                ORDER BY nom_module
            '
        ],

        'registrations' => [
            'title' => 'Inscriptions',
            'sql' => '
                SELECT e.CNE,
                       CONCAT(e.prenom, " ", e.nom) AS etudiant,
                       f.nom_filiere,
                       a.libelle AS annee,
                       i.date_inscription,
                       i.statut
                FROM Inscription i
                JOIN Etudiant e
                    ON e.id_etudiant = i.id_etudiant
                JOIN Filiere f
                    ON f.id_filiere = i.id_filiere
                JOIN Annee_univ a
                    ON a.id_annee = i.id_annee
                ORDER BY i.date_inscription DESC
            '
        ],

        'evaluations' => [
            'title' => 'Évaluations',
            'sql' => '
                SELECT m.code_module, m.nom_module, ev.libelle,
                       ev.type_evaluation, ev.date_evaluation, ev.note_max
                FROM Evaluation ev
                JOIN Enseignement en
                    ON en.id_enseignement = ev.id_enseignement
                JOIN Module m
                    ON m.id_module = en.id_module
                ORDER BY ev.date_evaluation DESC
            '
        ],

        'grades' => [
            'title' => 'Notes',
            'sql' => '
                SELECT e.CNE,
                       CONCAT(e.prenom, " ", e.nom) AS etudiant,
                       m.code_module,
                       ev.libelle AS evaluation,
                       n.valeur,
                       ev.note_max,
                       n.statut_validation
                FROM Note n
                JOIN Evaluation ev
                    ON ev.id_evaluation = n.id_evaluation
                JOIN Enseignement en
                    ON en.id_enseignement = ev.id_enseignement
                JOIN Module m
                    ON m.id_module = en.id_module
                JOIN Inscription i
                    ON i.id_inscription = n.id_inscription
                JOIN Etudiant e
                    ON e.id_etudiant = i.id_etudiant
                ORDER BY m.nom_module, e.nom
            '
        ],
    ];

    public function overview(): array
    {
        $counts = [];

        $tables = [
            'Étudiants' => 'Etudiant',
            'Enseignants' => 'Enseignant',
            'Modules' => 'Module',
            'Inscriptions' => 'Inscription',
        ];

        foreach ($tables as $label => $table) {
            $total = $this->db
                ->query("SELECT COUNT(*) FROM `$table`")
                ->fetchColumn();

            $counts[] = [$label, (int) $total];
        }

        return $counts;
    }

    public function page(string $page): ?array
    {
        if (!isset(self::PAGES[$page])) {
            return null;
        }

        $sql = self::PAGES[$page]['sql'];
        $title = self::PAGES[$page]['title'];

        $records = $this->rows($sql);

        $columns = array_values(array_filter(
    array_keys($records[0] ?? []),
    fn($key) => !is_numeric($key) && !in_array($key, ['id_etudiant', 'id_enseignant'], true)
));

        return [
            'title' => $title,
            'records' => $records,
            'columns' => $columns,
        ];
    }
}
