<?php
/**
 * Utility Functions
 * Learning Materials Management System
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate secure CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION[$type . '_message'] = $message;
}

/**
 * Get flash message
 */
function getFlashMessage($type) {
    if (isset($_SESSION[$type . '_message'])) {
        $message = $_SESSION[$type . '_message'];
        unset($_SESSION[$type . '_message']);
        return $message;
    }
    return null;
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'info') {
    setFlashMessage($message, $type);
    header("Location: $url");
    exit;
}

/**
 * Format file size in human readable format
 */
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 B';
    
    $sizes = array('B', 'KB', 'MB', 'GB', 'TB');
    $factor = floor((strlen($bytes) - 1) / 3);
    
    return sprintf("%.2f %s", $bytes / pow(1024, $factor), $sizes[$factor]);
}

/**
 * Format price with currency
 */
function formatPrice($price) {
    return AppConfig::formatPrice($price);
}

/**
 * Get file extension from filename
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($originalName) {
    $extension = getFileExtension($originalName);
    $basename = pathinfo($originalName, PATHINFO_FILENAME);
    $basename = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $basename);
    $timestamp = date('Y-m-d_H-i-s');
    $random = substr(md5(uniqid(rand(), true)), 0, 8);
    
    return $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate URL
 */
function isValidURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Log activity (simple file-based logging)
 */
function logActivity($message, $level = 'INFO', $context = []) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIP();
    $user = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'guest';
    
    $logEntry = [
        'timestamp' => $timestamp,
        'level' => $level,
        'ip' => $ip,
        'user' => $user,
        'message' => $message,
        'context' => $context
    ];
    
    $logLine = json_encode($logEntry) . PHP_EOL;
    file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
}

/**
 * Paginate results
 */
function paginate($totalItems, $itemsPerPage, $currentPage = 1) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($totalPages, $currentPage));
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_items' => $totalItems,
        'items_per_page' => $itemsPerPage,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'previous_page' => $currentPage > 1 ? $currentPage - 1 : null,
        'next_page' => $currentPage < $totalPages ? $currentPage + 1 : null
    ];
}

/**
 * Generate pagination HTML
 */
function generatePaginationHTML($pagination, $baseUrl = '') {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // Previous button
    if ($pagination['has_previous']) {
        $html .= '<a href="' . $baseUrl . '?page=' . $pagination['previous_page'] . '" class="pagination-btn">« Previous</a>';
    } else {
        $html .= '<span class="pagination-btn disabled">« Previous</span>';
    }
    
    // Page numbers
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
    
    if ($start > 1) {
        $html .= '<a href="' . $baseUrl . '?page=1" class="pagination-number">1</a>';
        if ($start > 2) {
            $html .= '<span class="pagination-ellipsis">...</span>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $pagination['current_page']) {
            $html .= '<span class="pagination-number current">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="pagination-number">' . $i . '</a>';
        }
    }
    
    if ($end < $pagination['total_pages']) {
        if ($end < $pagination['total_pages'] - 1) {
            $html .= '<span class="pagination-ellipsis">...</span>';
        }
        $html .= '<a href="' . $baseUrl . '?page=' . $pagination['total_pages'] . '" class="pagination-number">' . $pagination['total_pages'] . '</a>';
    }
    
    // Next button
    if ($pagination['has_next']) {
        $html .= '<a href="' . $baseUrl . '?page=' . $pagination['next_page'] . '" class="pagination-btn">Next »</a>';
    } else {
        $html .= '<span class="pagination-btn disabled">Next »</span>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Time ago function
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31104000) return floor($time/2592000) . ' months ago';
    
    return floor($time/31104000) . ' years ago';
}

/**
 * Generate breadcrumbs
 */
function generateBreadcrumbs($items) {
    $html = '<nav class="breadcrumbs"><ol class="breadcrumb-list">';
    
    foreach ($items as $index => $item) {
        $isLast = $index === array_key_last($items);
        
        if ($isLast) {
            $html .= '<li class="breadcrumb-item active">' . htmlspecialchars($item['title']) . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['title']) . '</a></li>';
        }
    }
    
    $html .= '</ol></nav>';
    return $html;
}

/**
 * Check if request is POST
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if request is GET
 */
function isGet() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Get POST data with default value
 */
function post($key, $default = null) {
    return $_POST[$key] ?? $default;
}

/**
 * Get GET data with default value
 */
function get($key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * Check if running on HTTPS
 */
function isHTTPS() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
}

/**
 * Get current URL
 */
function getCurrentURL() {
    $protocol = isHTTPS() ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Debug function (only for development)
 */
function debug($var, $die = false) {
    if (defined('DEBUG') && DEBUG) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        if ($die) die();
    }
}
?>