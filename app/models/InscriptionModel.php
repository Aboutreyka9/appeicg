<?php

declare(strict_types=1);

namespace App\Models;

class InscriptionModel extends BaseModel
{
    private string $table = 'inscriptions';

    public function liste(string $etablissementCode, array $filters = []): array
    {
        $sql    = "SELECT i.*,
                          e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant,
                          c.libelle_classe,
                          n.libelle_niveau,
                          f.libelle_filiere,
                          a.libelle_annee
                   FROM {$this->table} i
                   JOIN etudiants e  ON e.code_etudiant  = i.etudiant_code
                   JOIN classes c    ON c.code_classe    = i.classe_code
                   JOIN niveaux n    ON n.code_niveau    = c.niveau_code
                   JOIN filieres f   ON f.code_filiere   = n.filiere_code
                   LEFT JOIN annees a ON a.libelle_annee = i.annee_code
                   WHERE i.etablissement_code = ?";
        $params = [$etablissementCode];

        if (!empty($filters['annee_code'])) {
            $sql    .= ' AND i.annee_code = ?';
            $params[] = $filters['annee_code'];
        }
        if (!empty($filters['classe_code'])) {
            $sql    .= ' AND i.classe_code = ?';
            $params[] = $filters['classe_code'];
        }
        if (!empty($filters['statut'])) {
            $sql    .= ' AND i.statut_inscription = ?';
            $params[] = $filters['statut'];
        }
        if (!empty($filters['search'])) {
            $sql    .= ' AND (e.nom_etudiant LIKE ? OR e.prenom_etudiant LIKE ? OR e.matricule_etudiant LIKE ?)';
            $s        = '%' . $filters['search'] . '%';
            $params[] = $s; $params[] = $s; $params[] = $s;
        }

        $sql .= ' ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT i.*,
                    e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant,
                    c.libelle_classe, n.libelle_niveau, f.libelle_filiere
             FROM {$this->table} i
             JOIN etudiants e ON e.code_etudiant = i.etudiant_code
             JOIN classes c   ON c.code_classe   = i.classe_code
             JOIN niveaux n   ON n.code_niveau   = c.niveau_code
             JOIN filieres f  ON f.code_filiere  = n.filiere_code
             WHERE i.code_inscription = ? AND i.etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function findByEtudiantAnnee(string $etudiantCode, string $anneeCode, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE etudiant_code = ? AND annee_code = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$etudiantCode, $anneeCode, $etablissementCode]);
        return $stmt->fetch();
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('INS');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_inscription, etudiant_code, classe_code, etablissement_code,
              annee_code, user_code, montant_scolarite_inscription,
              statut_inscription, created_at_inscription)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'valide', NOW())"
        );
        $stmt->execute([
            $code,
            $data['etudiant_code'],
            $data['classe_code'],
            $data['etablissement_code'],
            $data['annee_code'],
            $data['user_code'],
            $data['montant_scolarite_inscription'] ?? 0,
        ]);
        return $code;
    }

    public function updateStatut(string $code, string $statut, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_inscription = ?, updated_at_inscription = NOW()
             WHERE code_inscription = ? AND etablissement_code = ?"
        );
        $stmt->execute([$statut, $code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    public function updateMontant(string $code, float $montant, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET montant_scolarite_inscription = ?, updated_at_inscription = NOW()
             WHERE code_inscription = ? AND etablissement_code = ?"
        );
        $stmt->execute([$montant, $code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    public function updateClasse(string $code, string $classeCode, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET classe_code = ?, updated_at_inscription = NOW()
             WHERE code_inscription = ? AND etablissement_code = ?"
        );
        $stmt->execute([$classeCode, $code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    public function countByClasse(string $classeCode, string $etablissementCode): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table}
             WHERE classe_code = ? AND etablissement_code = ? AND statut_inscription != 'annule'"
        );
        $stmt->execute([$classeCode, $etablissementCode]);
        return (int) $stmt->fetchColumn();
    }
}
