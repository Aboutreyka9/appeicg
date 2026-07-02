<?php

declare(strict_types=1);

namespace App\Models;

class AnneeModel extends BaseModel
{
    private string $table = 'annees';

    public function liste(string $etablissementCode): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE etablissement_code = ?
             ORDER BY date_debut_annee DESC"
        );
        $stmt->execute([$etablissementCode]);
        return $stmt->fetchAll();
    }

    public function findByCode(string $code, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE libelle_annee = ? AND etablissement_code = ? LIMIT 1"
        );
        // On cherche par libelle car c'est la clé métier ici (unique libelle+etab)
        // mais on peut aussi chercher par un code_annee si besoin
        $stmt->execute([$code, $etablissementCode]);
        return $stmt->fetch();
    }

    public function findByLibelle(string $libelle, string $etablissementCode, ?string $excludeLibelle = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM {$this->table} WHERE libelle_annee = ? AND etablissement_code = ?";
        $params = [$libelle, $etablissementCode];
        if ($excludeLibelle) {
            $sql    .= ' AND libelle_annee != ?';
            $params[] = $excludeLibelle;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Dans cette table, il n'y a pas de code_annee dédié,
     * on utilise libelle_annee comme clé métier pour les relations.
     * On génère quand même un identifiant unique pour l'annee_code utilisé partout.
     */
    public function create(array $data): string
    {
        $anneeCode = $this->generateCode('ANN');
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table}
             (libelle_annee, date_debut_annee, date_fin_annee,
              etablissement_code, created_at_annee, statut_annee, user_code)
             VALUES (?, ?, ?, ?, NOW(), 'actif', ?)"
        );
        $stmt->execute([
            $data['libelle_annee'],
            $data['date_debut_annee'],
            $data['date_fin_annee'],
            $data['etablissement_code'],
            $data['user_code'],
        ]);

        // Retourner le libelle qui sert de clé métier (annee_code dans les autres tables)
        return $anneeCode;
    }

    /**
     * On stocke l'annee_code généré dans une colonne virtuelle via le libelle.
     * Pour les relations, les autres tables utilisent libelle_annee comme annee_code.
     * Cette méthode retourne l'id_annee pour les mises à jour.
     */
    public function getLastInsertedLibelle(string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE etablissement_code = ?
             ORDER BY id_annee DESC LIMIT 1"
        );
        $stmt->execute([$etablissementCode]);
        return $stmt->fetch();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET libelle_annee     = ?,
                 date_debut_annee  = ?,
                 date_fin_annee    = ?,
                 updated_at_annee  = NOW()
             WHERE id_annee = ? AND etablissement_code = ?"
        );
        $stmt->execute([
            $data['libelle_annee'],
            $data['date_debut_annee'],
            $data['date_fin_annee'],
            $id,
            $data['etablissement_code'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatut(int $id, string $statut, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET statut_annee = ?, updated_at_annee = NOW()
             WHERE id_annee = ? AND etablissement_code = ?"
        );
        $stmt->execute([$statut, $id, $etablissementCode]);
        return $stmt->rowCount() > 0;
    }

    public function findById(int $id, string $etablissementCode): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE id_annee = ? AND etablissement_code = ? LIMIT 1"
        );
        $stmt->execute([$id, $etablissementCode]);
        return $stmt->fetch();
    }

    /**
     * Vérifier qu'aucune inscription n'est liée à cette année
     * (intégrité référentielle manuelle - MyISAM)
     */
    public function hasInscriptions(string $libelleAnnee, string $etablissementCode): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM inscriptions
             WHERE annee_code = ? AND etablissement_code = ?"
        );
        $stmt->execute([$libelleAnnee, $etablissementCode]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
