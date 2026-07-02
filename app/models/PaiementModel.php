<?php

declare(strict_types=1);

namespace App\Models;

class PaiementModel extends BaseModel
{
    private string $table = 'paiements';

    public function liste(string $etablissementCode, array $filters = []): array
    {
        $sql    = "SELECT p.*,
                          e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant,
                          i.code_inscription, i.classe_code
                   FROM {$this->table} p
                   LEFT JOIN inscriptions i ON i.code_inscription = p.reference_paiement
                   LEFT JOIN etudiants e    ON e.code_etudiant    = i.etudiant_code
                   WHERE p.etablissement_code = ?";
        $params = [$etablissementCode];

        if (!empty($filters['annee_code'])) {
            $sql    .= ' AND p.annee_code = ?';
            $params[] = $filters['annee_code'];
        }
        if (!empty($filters['statut'])) {
            $sql    .= ' AND p.statut_paiement = ?';
            $params[] = $filters['statut'];
        }
        if (!empty($filters['type_paiement'])) {
            $sql    .= ' AND p.type_paiement = ?';
            $params[] = $filters['type_paiement'];
        }
        if (!empty($filters['search'])) {
            $sql    .= ' AND (e.nom_etudiant LIKE ? OR e.prenom_etudiant LIKE ?
                              OR e.matricule_etudiant LIKE ? OR p.code_paiement LIKE ?)';
            $s        = '%' . $filters['search'] . '%';
            $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s;
        }

        $sql .= ' ORDER BY p.date_paiement DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT p.*,
                    e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant,
                    i.classe_code, i.annee_code as ins_annee_code
             FROM {$this->table} p
             LEFT JOIN inscriptions i ON i.code_inscription = p.reference_paiement
             LEFT JOIN etudiants e    ON e.code_etudiant    = i.etudiant_code
             WHERE p.code_paiement = ? AND p.etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function totalByInscription(string $inscriptionCode, string $etablissementCode): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(montant_paiement), 0) FROM {$this->table}
             WHERE reference_paiement = ? AND etablissement_code = ?
               AND statut_paiement = 'confirme'"
        );
        $stmt->execute([$inscriptionCode, $etablissementCode]);
        return (float) $stmt->fetchColumn();
    }

    public function statsParMois(string $etablissementCode, string $anneeCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT DATE_FORMAT(date_paiement, '%Y-%m') as mois,
                    SUM(montant_paiement) as total,
                    COUNT(*) as nb
             FROM {$this->table}
             WHERE etablissement_code = ? AND annee_code = ? AND statut_paiement = 'confirme'
             GROUP BY mois ORDER BY mois ASC"
        );
        $stmt->execute([$etablissementCode, $anneeCode]);
        return $stmt->fetchAll();
    }

    public function totalConfirme(string $etablissementCode, ?string $anneeCode = null): float
    {
        $sql    = "SELECT COALESCE(SUM(montant_paiement), 0) FROM {$this->table}
                   WHERE etablissement_code = ? AND statut_paiement = 'confirme'";
        $params = [$etablissementCode];
        if ($anneeCode) { $sql .= ' AND annee_code = ?'; $params[] = $anneeCode; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }

    public function create(array $data): string
    {
        $code = $this->generateCode('PAY');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (code_paiement, montant_paiement, date_paiement, statut_paiement,
              reference_paiement, observations, type_paiement, mode_paiement,
              user_code, annee_code, etablissement_code)
             VALUES (?, ?, NOW(), 'confirme', ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $code,
            $data['montant_paiement'],
            $data['reference_paiement'] ?: null,
            $data['observations']       ?: null,
            $data['type_paiement'],
            $data['mode_paiement'],
            $data['user_code'],
            $data['annee_code']         ?: null,
            $data['etablissement_code'],
        ]);
        return $code;
    }

    public function annuler(string $code, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_paiement = 'annule'
             WHERE code_paiement = ? AND etablissement_code = ?"
        );
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }
}
