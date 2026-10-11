<?php
/**
 * CraveHub - MongoDB Database Handler
 * Uses native PHP MongoDB Extension with seamless fail-safe document storage
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $manager = null;
    private $dbName = 'food_delivery_db';
    private $connectionUri = 'mongodb://127.0.0.1:27017';
    private $isMongoConnected = false;
    private $storageDir;

    private function __construct() {
        $envUri = getenv('MONGODB_URI') ?: (getenv('MONGO_URL') ?: getenv('DATABASE_URL'));
        if (!empty($envUri)) {
            $this->connectionUri = $envUri;
        }
        $envDb = getenv('MONGODB_DB') ?: (getenv('MONGO_INITDB_DATABASE') ?: getenv('MONGO_DATABASE'));
        if (!empty($envDb)) {
            $this->dbName = $envDb;
        }

        $this->storageDir = dirname(__DIR__) . '/data/db';
        if (!file_exists($this->storageDir)) {
            mkdir($this->storageDir, 0777, true);
        }

        // Check if MongoDB extension is installed
        if (extension_loaded('mongodb')) {
            try {
                // Initialize Manager with 1.5s timeout for fast health check
                $options = [
                    'serverSelectionTimeoutMS' => 1500,
                    'connectTimeoutMS' => 1500
                ];
                $this->manager = new MongoDB\Driver\Manager($this->connectionUri, $options);
                
                // Ping to verify actual server connectivity
                $command = new MongoDB\Driver\Command(['ping' => 1]);
                $this->manager->executeCommand($this->dbName, $command);
                $this->isMongoConnected = true;
            } catch (Exception $e) {
                $this->isMongoConnected = false;
                error_log("MongoDB connection notice: " . $e->getMessage() . " - Using local NoSQL store fallback.");
            }
        } else {
            $this->isMongoConnected = false;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function isConnected() {
        return $this->isMongoConnected;
    }

    public function getStatusInfo() {
        return [
            'extension_loaded' => extension_loaded('mongodb'),
            'connected' => $this->isMongoConnected,
            'uri' => $this->connectionUri,
            'database' => $this->dbName,
            'engine' => $this->isMongoConnected ? 'MongoDB Server (Live)' : 'NoSQL Document Store (Synced)'
        ];
    }

    /**
     * Generate unique 24-character hexadecimal ObjectId
     */
    public function generateId() {
        if (extension_loaded('mongodb') && class_exists('MongoDB\BSON\ObjectId')) {
            return (string) new MongoDB\BSON\ObjectId();
        }
        return bin2hex(random_bytes(12));
    }

    /**
     * Find multiple documents
     */
    public function find($collection, $filter = [], $options = []) {
        if ($this->isMongoConnected) {
            try {
                // Transform _id if string
                $mongoFilter = $this->prepareFilter($filter);
                $query = new MongoDB\Driver\Query($mongoFilter, $options);
                $cursor = $this->manager->executeQuery("{$this->dbName}.{$collection}", $query);
                
                $results = [];
                foreach ($cursor as $doc) {
                    $results[] = $this->documentToArray($doc);
                }
                return $results;
            } catch (Exception $e) {
                error_log("Mongo Find error: " . $e->getMessage());
            }
        }

        // Local Document Store fallback
        return $this->localFind($collection, $filter, $options);
    }

    /**
     * Find single document
     */
    public function findOne($collection, $filter = []) {
        $options = ['limit' => 1];
        $results = $this->find($collection, $filter, $options);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Find by ID
     */
    public function findById($collection, $id) {
        return $this->findOne($collection, ['_id' => $id]);
    }

    /**
     * Insert a document
     */
    public function insert($collection, $document) {
        if (!isset($document['_id'])) {
            $document['_id'] = $this->generateId();
        }
        if (!isset($document['created_at'])) {
            $document['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($document['updated_at'])) {
            $document['updated_at'] = date('Y-m-d H:i:s');
        }

        if ($this->isMongoConnected) {
            try {
                $bulk = new MongoDB\Driver\BulkWrite();
                $mongoDoc = $document;
                if (is_string($mongoDoc['_id']) && strlen($mongoDoc['_id']) === 24) {
                    try {
                        $mongoDoc['_id'] = new MongoDB\BSON\ObjectId($mongoDoc['_id']);
                    } catch (Exception $e) {}
                }
                $bulk->insert($mongoDoc);
                $this->manager->executeBulkWrite("{$this->dbName}.{$collection}", $bulk);
            } catch (Exception $e) {
                error_log("Mongo Insert error: " . $e->getMessage());
            }
        }

        // Also persist to local store for reliability & sync
        $this->localInsert($collection, $document);
        return $document;
    }

    /**
     * Update documents matching filter
     */
    public function update($collection, $filter, $updateData, $options = ['multi' => false]) {
        $updateData['updated_at'] = date('Y-m-d H:i:s');

        if ($this->isMongoConnected) {
            try {
                $bulk = new MongoDB\Driver\BulkWrite();
                $mongoFilter = $this->prepareFilter($filter);
                // wrap in $set if not already an operator
                $isOperator = false;
                foreach (array_keys($updateData) as $k) {
                    if (strpos($k, '$') === 0) {
                        $isOperator = true;
                        break;
                    }
                }
                $updatePayload = $isOperator ? $updateData : ['$set' => $updateData];
                
                $bulk->update($mongoFilter, $updatePayload, [
                    'multi' => $options['multi'] ?? false,
                    'upsert' => $options['upsert'] ?? false
                ]);
                $this->manager->executeBulkWrite("{$this->dbName}.{$collection}", $bulk);
            } catch (Exception $e) {
                error_log("Mongo Update error: " . $e->getMessage());
            }
        }

        return $this->localUpdate($collection, $filter, $updateData, $options);
    }

    /**
     * Delete documents matching filter
     */
    public function delete($collection, $filter, $options = ['limit' => 1]) {
        if ($this->isMongoConnected) {
            try {
                $bulk = new MongoDB\Driver\BulkWrite();
                $mongoFilter = $this->prepareFilter($filter);
                $bulk->delete($mongoFilter, ['limit' => $options['limit'] ?? 1]);
                $this->manager->executeBulkWrite("{$this->dbName}.{$collection}", $bulk);
            } catch (Exception $e) {
                error_log("Mongo Delete error: " . $e->getMessage());
            }
        }

        return $this->localDelete($collection, $filter, $options);
    }

    /**
     * Count documents matching filter
     */
    public function count($collection, $filter = []) {
        if ($this->isMongoConnected) {
            try {
                $mongoFilter = $this->prepareFilter($filter);
                $command = new MongoDB\Driver\Command([
                    'count' => $collection,
                    'query' => (object)$mongoFilter
                ]);
                $cursor = $this->manager->executeCommand($this->dbName, $command);
                $response = current($cursor->toArray());
                return (int)($response->n ?? 0);
            } catch (Exception $e) {
                error_log("Mongo Count error: " . $e->getMessage());
            }
        }

        $all = $this->localFind($collection, $filter);
        return count($all);
    }

    // Helper: Convert MongoDB BSON Document to Standard PHP Associative Array
    private function documentToArray($doc) {
        $json = json_encode($doc);
        $array = json_decode($json, true);
        if (isset($array['_id']) && is_array($array['_id']) && isset($array['_id']['$oid'])) {
            $array['_id'] = $array['_id']['$oid'];
        } elseif (isset($doc->_id) && is_object($doc->_id) && method_exists($doc->_id, '__toString')) {
            $array['_id'] = (string)$doc->_id;
        }
        return $array;
    }

    // Helper: Prepare filter with ObjectId conversion
    private function prepareFilter($filter) {
        $prepared = [];
        foreach ($filter as $key => $val) {
            if ($key === '_id' && is_string($val) && strlen($val) === 24) {
                try {
                    $prepared[$key] = new MongoDB\BSON\ObjectId($val);
                } catch (Exception $e) {
                    $prepared[$key] = $val;
                }
            } else {
                $prepared[$key] = $val;
            }
        }
        return $prepared;
    }

    // ==========================================
    // LOCAL DOCUMENT STORE (FAILSAFE ENGINE)
    // ==========================================

    private function getCollectionFilePath($collection) {
        return $this->storageDir . '/' . preg_replace('/[^a-zA-Z0-9_]/', '', $collection) . '.json';
    }

    private function readCollection($collection) {
        $file = $this->getCollectionFilePath($collection);
        if (!file_exists($file)) {
            return [];
        }
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    private function writeCollection($collection, array $data) {
        $file = $this->getCollectionFilePath($collection);
        file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function localFind($collection, $filter = [], $options = []) {
        $records = $this->readCollection($collection);
        $results = [];

        foreach ($records as $item) {
            if ($this->matchesFilter($item, $filter)) {
                $results[] = $item;
            }
        }

        // Sorting
        if (!empty($options['sort'])) {
            $sortField = array_key_first($options['sort']);
            $sortDir = $options['sort'][$sortField];
            usort($results, function ($a, $b) use ($sortField, $sortDir) {
                $valA = $a[$sortField] ?? null;
                $valB = $b[$sortField] ?? null;
                if ($valA === $valB) return 0;
                $cmp = ($valA > $valB) ? 1 : -1;
                return ($sortDir == -1) ? -$cmp : $cmp;
            });
        }

        // Skip
        if (!empty($options['skip'])) {
            $results = array_slice($results, $options['skip']);
        }

        // Limit
        if (!empty($options['limit'])) {
            $results = array_slice($results, 0, $options['limit']);
        }

        return $results;
    }

    private function localInsert($collection, $document) {
        $records = $this->readCollection($collection);
        $records[] = $document;
        $this->writeCollection($collection, $records);
        return true;
    }

    private function localUpdate($collection, $filter, $updateData, $options = []) {
        $records = $this->readCollection($collection);
        $modifiedCount = 0;
        $multi = $options['multi'] ?? false;

        $fieldsToSet = $updateData['$set'] ?? $updateData;

        foreach ($records as $idx => $item) {
            if ($this->matchesFilter($item, $filter)) {
                foreach ($fieldsToSet as $k => $v) {
                    if (strpos($k, '$') !== 0) {
                        $records[$idx][$k] = $v;
                    }
                }
                $modifiedCount++;
                if (!$multi) {
                    break;
                }
            }
        }

        if ($modifiedCount > 0) {
            $this->writeCollection($collection, $records);
        }
        return $modifiedCount;
    }

    private function localDelete($collection, $filter, $options = []) {
        $records = $this->readCollection($collection);
        $remaining = [];
        $deleted = 0;
        $limit = $options['limit'] ?? 1;

        foreach ($records as $item) {
            if (($limit === 0 || $deleted < $limit) && $this->matchesFilter($item, $filter)) {
                $deleted++;
            } else {
                $remaining[] = $item;
            }
        }

        if ($deleted > 0) {
            $this->writeCollection($collection, $remaining);
        }
        return $deleted;
    }

    private function matchesFilter($item, $filter) {
        if (empty($filter)) return true;

        foreach ($filter as $key => $expected) {
            // Handle $or
            if ($key === '$or' && is_array($expected)) {
                $matchedAny = false;
                foreach ($expected as $subFilter) {
                    if ($this->matchesFilter($item, $subFilter)) {
                        $matchedAny = true;
                        break;
                    }
                }
                if (!$matchedAny) return false;
                continue;
            }

            // Normal field match
            $actual = $item[$key] ?? null;

            if (is_array($expected)) {
                if (isset($expected['$in'])) {
                    if (!in_array($actual, $expected['$in'])) return false;
                } elseif (isset($expected['$regex'])) {
                    $pattern = '/' . str_replace('/', '\/', $expected['$regex']) . '/';
                    if (!empty($expected['$options']) && strpos($expected['$options'], 'i') !== false) {
                        $pattern .= 'i';
                    }
                    if (!preg_match($pattern, (string)$actual)) return false;
                } elseif (isset($expected['$gte'])) {
                    if ($actual < $expected['$gte']) return false;
                } elseif (isset($expected['$lte'])) {
                    if ($actual > $expected['$lte']) return false;
                } elseif (isset($expected['$ne'])) {
                    if ($actual === $expected['$ne']) return false;
                }
            } else {
                if ((string)$actual !== (string)$expected) {
                    return false;
                }
            }
        }

        return true;
    }
}

// Global db helper function
function getDB() {
    return Database::getInstance();
}
