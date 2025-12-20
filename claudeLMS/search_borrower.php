<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get search query
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    echo json_encode(['success' => false, 'message' => 'Search query is required']);
    exit();
}

try {
    // Search users by name or member ID
    $stmt = $pdo->prepare("
        SELECT 
            user_id,
            first_name,
            last_name,
            CONCAT(first_name, ' ', last_name) as full_name,
            email,
            phone,
            member_id,
            status
        FROM users 
        WHERE 
            (first_name LIKE ? OR 
            last_name LIKE ? OR 
            CONCAT(first_name, ' ', last_name) LIKE ? OR
            member_id LIKE ?) AND
            status = 'active'
        LIMIT 10
    ");
    
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        $user = $users[0]; // Get first match
        
        // Count currently borrowed books
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM borrowed_books 
            WHERE user_id = ? AND status = 'borrowed'
        ");
        $stmt->execute([$user['user_id']]);
        $borrowed_count = $stmt->fetchColumn();
        
        // Check for overdue books
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM borrowed_books 
            WHERE user_id = ? AND status = 'borrowed' AND due_date < CURDATE()
        ");
        $stmt->execute([$user['user_id']]);
        $overdue_count = $stmt->fetchColumn();
        
        // Get initials for avatar
        $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
        
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['user_id'],
                'full_name' => $user['full_name'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'member_id' => $user['member_id'],
                'borrowed_count' => $borrowed_count,
                'has_overdue' => $overdue_count > 0,
                'overdue_count' => $overdue_count,
                'initials' => $initials
            ],
            'all_results' => $users  // Optional: return all matches
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No user found'
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>