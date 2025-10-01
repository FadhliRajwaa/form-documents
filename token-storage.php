<?php
/**
 * Token Storage dengan PostgreSQL
 * Simpan dan load Google OAuth token dari database
 */

class TokenStorage {
    private $pdo;
    
    public function __construct() {
        $this->connect();
        $this->createTableIfNotExists();
    }
    
    /**
     * Connect ke PostgreSQL
     */
    private function connect() {
        try {
            $databaseUrl = getenv('DATABASE_URL');
            
            if (!$databaseUrl) {
                throw new Exception('DATABASE_URL environment variable not set');
            }
            
            // Parse DATABASE_URL
            // Format: postgresql://user:password@host:port/dbname
            $dbParts = parse_url($databaseUrl);
            
            $host = $dbParts['host'];
            $port = $dbParts['port'] ?? 5432;
            $dbname = ltrim($dbParts['path'], '/');
            $user = $dbParts['user'];
            $password = $dbParts['pass'];
            
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
            
            $this->pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create table jika belum ada
     */
    private function createTableIfNotExists() {
        $sql = "
            CREATE TABLE IF NOT EXISTS oauth_tokens (
                id SERIAL PRIMARY KEY,
                token_key VARCHAR(255) UNIQUE NOT NULL,
                token_data TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        try {
            $this->pdo->exec($sql);
        } catch (Exception $e) {
            error_log("Create table error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Save token ke database
     */
    public function saveToken($tokenKey, $tokenData) {
        $sql = "
            INSERT INTO oauth_tokens (token_key, token_data, updated_at)
            VALUES (:key, :data, CURRENT_TIMESTAMP)
            ON CONFLICT (token_key) 
            DO UPDATE SET 
                token_data = :data,
                updated_at = CURRENT_TIMESTAMP
        ";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'key' => $tokenKey,
                'data' => $tokenData
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Save token error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Load token dari database
     */
    public function loadToken($tokenKey) {
        $sql = "SELECT token_data FROM oauth_tokens WHERE token_key = :key";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['key' => $tokenKey]);
            $result = $stmt->fetch();
            
            return $result ? $result['token_data'] : null;
        } catch (Exception $e) {
            error_log("Load token error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check apakah token ada
     */
    public function hasToken($tokenKey) {
        return $this->loadToken($tokenKey) !== null;
    }
    
    /**
     * Delete token
     */
    public function deleteToken($tokenKey) {
        $sql = "DELETE FROM oauth_tokens WHERE token_key = :key";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['key' => $tokenKey]);
            return true;
        } catch (Exception $e) {
            error_log("Delete token error: " . $e->getMessage());
            return false;
        }
    }
}
