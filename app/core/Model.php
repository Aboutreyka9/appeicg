<?php
namespace App\Core;

use Exception;
use PDO;


abstract class Model
{
    protected object $db;
    protected string $table;
    protected string $id;
    protected string $coditions;
    protected string $fields = '*';
    protected string $fetch = 'fetchAll';
    protected string $key;
    protected array $relations = [];
    protected array $where = [];
    protected array $params = [];
    protected array $withRelations = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected ?string $lastInsertIdColumn = null;

    public function __construct()
    {
        // $this->db = Database::getInstance();
        $this->db = Database::getInstance()->getConnection();

        if (!$this->table) {
            $class = (new \ReflectionClass($this))->getShortName();
            $this->table = strtolower($class) . 's';
        }

        if (!$this->id) {
           $this->getId();
        //    get_class_vars($this->id);
        }
        
    }

    // public function setTable( string $table): self
    // {
    //     $this->table = $table;
    //     return $this;
      
    // }

    public function getId(): string
    {
        return $this->id;
    }
    /**
 * Définir la colonne pour lastInsertId
 */
public function setLastInsertIdColumn(string $column): self
{
    $this->lastInsertIdColumn = $column;
    return $this;
}

/**
 * Retourne le dernier ID inséré
 */
public function lastInsertId(): string
{
    if ($this->lastInsertIdColumn) {
        // Si la colonne est définie, récupère la valeur via une requête
        $stmt = $this->db->query(
            "SELECT LAST_INSERT_ID({$this->lastInsertIdColumn}) as last_id"
        );
        return $stmt->fetch()['last_id'];
    }

    // Sinon retour par défaut PDO
    return $this->db->lastInsertId();
}


    public function all()
    {
        return $this->getQuery()->fetchAll();
    }

    public function first():mixed
    {
        // $this->limit(1);
        $this->fetch = 'fetch';
        return $this->getQuery()->fetchAll();
    }

   
    public function find(string $table, string $field, string $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE {$field} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function where(string $column, $value): self
    {
        $this->where[] = "$column = :$column";
        $this->params[$column] = $value;
        return $this;
    }

    public function limit(int $limit, int $offset = 0): self
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    public function with(string $relation): self
    {
        $this->withRelations[] = $relation;
        return $this;
    }

    protected function getQuery()
    {
        $sql = "SELECT {$this->fields} FROM {$this->table}";
        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(' AND ', $this->where);
        }
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit} OFFSET {$this->offset}";
        }
        $sql .= " ORDER BY id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->params);

        $results = $this->fetch ? $stmt->{$this->fetch}() : $stmt->fetchAll();

        // Charger relations si nécessaire
        if (!empty($this->withRelations)) {
            foreach ($this->withRelations as $relation) {
                $method = 'relation' . ucfirst($relation);
                if (method_exists($this, $method)) {
                    foreach ($results as &$row) {
                        $row[$relation] = $this->$method($row['id']);
                    }
                }
            }

            // foreach ($this->withRelations as $relation) {
            //     // Logique pour charger les relations
            //     // Par exemple, si c'est une relation de type "belongsTo"
            //     // ou "hasMany", vous pouvez faire une requête supplémentaire ici
            // }
        }

        return new class($results) {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function fetchAll() { return $this->data; }
        };
    }

    public function select(string $table, array $columns = ['*']): self
    {
        $this->table = $table;
        $cols = implode(', ', $columns);
        $this->fields = $cols;
        return $this;
        // $stmt = $this->db->query("SELECT $cols FROM {$table}");
        // return $stmt->fetchAll();
    }

    // public function select(string $table, array $columns = ['*']): array
    // {
    //     $cols = implode(', ', $columns);
    //     $stmt = $this->db->query("SELECT $cols FROM {$table}");
    //     return $stmt->fetchAll();
    // }

   
    public function create(string $table, array $data)
    {
        $result = false;
        $keys = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ($keys) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        if ($stmt->rowCount() > 0) {
            $result = true;
        }
        // return $this->db->lastInsertId();
        return $result;
    }

    public function update(string $table,string $key,string $id, array $data)
    {
        $result = false;
        $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $sql = "UPDATE {$table} SET $set WHERE {$key} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($data, ['id' => $id]));
        if ($stmt->rowCount() > 0) {
            $result = true;
        }
        return $result;
    }

    public function update2(string $table,array $keys, array $data)
    {
        $result = false;
         $key = implode(' AND ', array_map(fn($k) => "$k = :$k", array_keys($keys)));
         $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        
         $sql = "UPDATE {$table} SET $set WHERE $key";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($data, $keys));

        if ($stmt->rowCount() > 0) {
            $result = true;
        }
        return $result;

    }

    public function updateServiceClient(string $code)
    {
        $result = false;

        $sql = "UPDATE consommations SET etat_consommation = :etat, caisse_id = :caisse_id WHERE reservation_id = :code AND etat_consommation = :statut AND hotel_id = :hotel_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute( [
            'code' => $code, 
            'statut' => 0, 
            'etat' => 1,
            'hotel_id' => Auth::user('hotel_id'),
            'caisse_id' => Auth::user('caisse')
        ]);

        if ($stmt->rowCount() > 0) {
            $result = true;
        }
        return $result;
    }

    public function delete(string $table,string $key,string $id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$table} WHERE {$this->id} = ?");
        return $stmt->execute([$id]);
    }

    public function transaction(callable $callback)
    {
        try {
            $this->db->beginTransaction();
            $callback($this);
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            echo "Échec de la transaction : " . $e->getMessage();
        }
    }

    // Requête brute
    public function raw(string $sql, array $params = [], string $fetch = 'fetchAll')
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $data =$stmt->$fetch();
        
    }

 

    public function generatorCode(string $table, string $field)
{
    $code= generetor(rand(5,32));

    if(!empty($this->find($table,$field,$code))){
        $this->generatorCode($table,$field);
    }
    return $code;   
}

}
