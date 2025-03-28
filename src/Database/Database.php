<?php
namespace Kipay\Database;

use Kipay\Config\Database as DatabaseConfig;
use PDO;
use PDOException;

/**
 * Database Class for Kipay Payment Gateway
 * 
 * This class handles all database operations using PDO.
 */
class Database
{
    /**
     * @var \PDO|null PDO instance
     */
    protected $pdo = null;
    
    /**
     * @var \Kipay\Config\Database Database configuration
     */
    protected $config;
    
    /**
     * Database constructor
     */
    public function __construct()
    {
        $this->config = new DatabaseConfig();
        $this->connect();
    }
    
    /**
     * Connect to the database
     * 
     * @return bool True if connection successful
     */
    protected function connect(): bool
    {
        try {
            $dsn = "mysql:host={$this->config->host};dbname={$this->config->database};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, $this->config->username, $this->config->password, $options);
            
            return true;
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a query
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return bool True if successful
     */
    public function execute(string $query, array $params = []): bool
    {
        try {
            $stmt = $this->pdo->prepare($query);
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage());
            error_log("Query: " . $query);
            error_log("Params: " . json_encode($params));
            return false;
        }
    }
    
    /**
     * Execute a query and fetch results
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array|bool Query results or false on failure
     */
    public function query(string $query, array $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($query);
            
            // Bind parameters with correct type
            foreach ($params as $key => $value) {
                // Skip numeric keys for positional parameters
                if (is_int($key)) {
                    continue;
                }
                
                $type = PDO::PARAM_STR;
                
                if (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $type = PDO::PARAM_NULL;
                }
                
                $stmt->bindValue($key, $value, $type);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            error_log("Query: " . $query);
            error_log("Params: " . json_encode($params));
            return false;
        }
    }
    
    /**
     * Insert a record
     * 
     * @param string $table Table name
     * @param array $data Data to insert
     * @return int|bool Last insert ID or false on failure
     */
    public function insert(string $table, array $data)
    {
        try {
            $keys = array_keys($data);
            $placeholders = array_map(function($key) {
                return ":$key";
            }, $keys);
            
            $query = "INSERT INTO $table (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->pdo->prepare($query);
            
            foreach ($data as $key => $value) {
                $type = PDO::PARAM_STR;
                
                if (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $type = PDO::PARAM_NULL;
                }
                
                $stmt->bindValue(":$key", $value, $type);
            }
            
            $stmt->execute();
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Insert failed: " . $e->getMessage());
            error_log("Table: " . $table);
            error_log("Data: " . json_encode($data));
            return false;
        }
    }
    
    /**
     * Update a record
     * 
     * @param string $table Table name
     * @param int $id Record ID
     * @param array $data Data to update
     * @return bool True if successful
     */
    public function update(string $table, int $id, array $data): bool
    {
        try {
            $setClause = [];
            
            foreach ($data as $key => $value) {
                $setClause[] = "$key = :$key";
            }
            
            $query = "UPDATE $table SET " . implode(', ', $setClause) . " WHERE id = :id";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            foreach ($data as $key => $value) {
                $type = PDO::PARAM_STR;
                
                if (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $type = PDO::PARAM_NULL;
                }
                
                $stmt->bindValue(":$key", $value, $type);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update failed: " . $e->getMessage());
            error_log("Table: " . $table);
            error_log("ID: " . $id);
            error_log("Data: " . json_encode($data));
            return false;
        }
    }
    
    /**
     * Delete a record
     * 
     * @param string $table Table name
     * @param int $id Record ID
     * @return bool True if successful
     */
    public function delete(string $table, int $id): bool
    {
        try {
            $query = "DELETE FROM $table WHERE id = :id";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete failed: " . $e->getMessage());
            error_log("Table: " . $table);
            error_log("ID: " . $id);
            return false;
        }
    }
    
    /**
     * Get a record by ID
     * 
     * @param string $table Table name
     * @param int $id Record ID
     * @return array|bool Record data or false if not found
     */
    public function getById(string $table, int $id)
    {
        try {
            $query = "SELECT * FROM $table WHERE id = :id LIMIT 1";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch();
            
            return $result ?: false;
        } catch (PDOException $e) {
            error_log("GetById failed: " . $e->getMessage());
            error_log("Table: " . $table);
            error_log("ID: " . $id);
            return false;
        }
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool True if successful
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool True if successful
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool True if successful
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }
    
    /**
     * Get the last insert ID
     * 
     * @return string Last insert ID
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Get the PDO instance
     * 
     * @return \PDO PDO instance
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}