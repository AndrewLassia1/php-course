<?php
/**
 * Material Management Class
 * Learning Materials Management System
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config/app.php';

class Material {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all active materials with pagination
     */
    public function getAllMaterials($page = 1, $limit = null) {
        $limit = $limit ?? AppConfig::ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM materials 
                WHERE is_active = 1 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        return $this->db->fetchAll($sql, [
            'limit' => $limit,
            'offset' => $offset
        ]);
    }
    
    /**
     * Get material by ID
     */
    public function getMaterialById($id) {
        $sql = "SELECT * FROM materials WHERE id = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
    
    /**
     * Search materials by title or description
     */
    public function searchMaterials($query, $page = 1, $limit = null) {
        $limit = $limit ?? AppConfig::ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM materials 
                WHERE is_active = 1 
                AND (title LIKE :query OR description LIKE :query)
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $searchTerm = '%' . $query . '%';
        return $this->db->fetchAll($sql, [
            'query' => $searchTerm,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }
    
    /**
     * Filter materials by file type
     */
    public function getMaterialsByType($fileType, $page = 1, $limit = null) {
        $limit = $limit ?? AppConfig::ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM materials 
                WHERE is_active = 1 AND file_type = :fileType
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        return $this->db->fetchAll($sql, [
            'fileType' => $fileType,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }
    
    /**
     * Get total materials count
     */
    public function getTotalMaterials($search = null, $fileType = null) {
        $sql = "SELECT COUNT(*) as total FROM materials WHERE is_active = 1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (title LIKE :search OR description LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        if ($fileType) {
            $sql .= " AND file_type = :fileType";
            $params['fileType'] = $fileType;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'];
    }
    
    /**
     * Add new material (Admin)
     */
    public function addMaterial($data) {
        $sql = "INSERT INTO materials (title, description, file_path, file_type, file_size, price) 
                VALUES (:title, :description, :file_path, :file_type, :file_size, :price)";
        
        $this->db->execute($sql, [
            'title' => $data['title'],
            'description' => $data['description'],
            'file_path' => $data['file_path'],
            'file_type' => $data['file_type'],
            'file_size' => $data['file_size'],
            'price' => $data['price']
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update material (Admin)
     */
    public function updateMaterial($id, $data) {
        $sql = "UPDATE materials 
                SET title = :title, description = :description, 
                    price = :price, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $params = [
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price']
        ];
        
        // Update file info if new file uploaded
        if (isset($data['file_path'])) {
            $sql = "UPDATE materials 
                    SET title = :title, description = :description, 
                        file_path = :file_path, file_type = :file_type, 
                        file_size = :file_size, price = :price, 
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
            
            $params['file_path'] = $data['file_path'];
            $params['file_type'] = $data['file_type'];
            $params['file_size'] = $data['file_size'];
        }
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Delete material (Admin)
     */
    public function deleteMaterial($id) {
        $sql = "DELETE FROM materials WHERE id = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }
    
    /**
     * Increment download counter
     */
    public function incrementDownloads($id) {
        $sql = "UPDATE materials SET downloads_count = downloads_count + 1 WHERE id = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }
    
    /**
     * Log download
     */
    public function logDownload($materialId, $ipAddress, $userAgent) {
        $sql = "INSERT INTO download_logs (material_id, ip_address, user_agent) 
                VALUES (:material_id, :ip_address, :user_agent)";
        
        return $this->db->execute($sql, [
            'material_id' => $materialId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ]);
    }
    
    /**
     * Get statistics for admin dashboard
     */
    public function getStatistics() {
        $stats = [];
        
        // Total materials
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM materials WHERE is_active = 1");
        $stats['total_materials'] = $result['total'];
        
        // Total downloads
        $result = $this->db->fetchOne("SELECT SUM(downloads_count) as total FROM materials");
        $stats['total_downloads'] = $result['total'] ?? 0;
        
        // Materials by type
        $stats['by_type'] = $this->db->fetchAll("
            SELECT file_type, COUNT(*) as count 
            FROM materials 
            WHERE is_active = 1 
            GROUP BY file_type
        ");
        
        // Recent downloads
        $stats['recent_downloads'] = $this->db->fetchAll("
            SELECT m.title, dl.downloaded_at, dl.ip_address
            FROM download_logs dl
            JOIN materials m ON dl.material_id = m.id
            ORDER BY dl.downloaded_at DESC
            LIMIT 10
        ");
        
        return $stats;
    }
}
?>