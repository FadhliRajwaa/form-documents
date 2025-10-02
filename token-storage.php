<?php
/**
 * Token Storage dengan PostgreSQL
 * Simpan dan load Google OAuth token dari database
 */

class TokenStorage {
    private $pdo;
    private $useDatabase = true;
    private $fileStoragePath;
    
    public function __construct() {
        $this->fileStoragePath = __DIR__ . '/token.json';
        
        // Check if DATABASE_URL is available
        if (getenv('DATABASE_URL')) {
            try {
                $this->connect();
                $this->createTableIfNotExists();
            } catch (Exception $e) {
                // Fallback to file-based storage
                error_log("Database connection failed, using file storage: " . $e->getMessage());
                $this->useDatabase = false;
            }
        } else {
            // Use file-based storage for local development
            $this->useDatabase = false;
        }
    }
    
    /**
     * Connect ke PostgreSQL
     */
    private function connect() {
        $databaseUrl = getenv('DATABASE_URL');
        
        if (!$databaseUrl) {
            throw new Exception('DATABASE_URL environment variable not set');
        }
        
        try {
            
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
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5, // 5 second timeout
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
     * Save token (database atau file)
     */
    public function saveToken($tokenKey, $tokenData) {
        if ($this->useDatabase) {
            return $this->saveToDatabase($tokenKey, $tokenData);
        } else {
            return $this->saveToFile($tokenData);
        }
    }
    
    /**
     * Save token ke database
     */
    private function saveToDatabase($tokenKey, $tokenData) {
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
            error_log("Save token to database error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Save token ke file (fallback untuk local)
     */
    private function saveToFile($tokenData) {
        try {
            $dir = dirname($this->fileStoragePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0700, true);
            }
            file_put_contents($this->fileStoragePath, $tokenData);
            return true;
        } catch (Exception $e) {
            error_log("Save token to file error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Load token (database atau file)
     */
    public function loadToken($tokenKey) {
        if ($this->useDatabase) {
            return $this->loadFromDatabase($tokenKey);
        } else {
            return $this->loadFromFile();
        }
    }
    
    /**
     * Load token dari database
     */
    private function loadFromDatabase($tokenKey) {
        $sql = "SELECT token_data FROM oauth_tokens WHERE token_key = :key";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['key' => $tokenKey]);
            $result = $stmt->fetch();
            
            return $result ? $result['token_data'] : null;
        } catch (Exception $e) {
            error_log("Load token from database error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Load token dari file (fallback untuk local)
     */
    private function loadFromFile() {
        try {
            if (file_exists($this->fileStoragePath)) {
                return file_get_contents($this->fileStoragePath);
            }
            return null;
        } catch (Exception $e) {
            error_log("Load token from file error: " . $e->getMessage());
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
        if ($this->useDatabase) {
            return $this->deleteFromDatabase($tokenKey);
        } else {
            return $this->deleteFromFile();
        }
    }
    
    /**
     * Delete token dari database
     */
    private function deleteFromDatabase($tokenKey) {
        $sql = "DELETE FROM oauth_tokens WHERE token_key = :key";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['key' => $tokenKey]);
            return true;
        } catch (Exception $e) {
            error_log("Delete token from database error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete token dari file
     */
    private function deleteFromFile() {
        try {
            if (file_exists($this->fileStoragePath)) {
                unlink($this->fileStoragePath);
            }
            return true;
        } catch (Exception $e) {
            error_log("Delete token from file error: " . $e->getMessage());
            return false;
        }
    }
}
