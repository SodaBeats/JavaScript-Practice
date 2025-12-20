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
    // Search books by title, author, or ISBN
    $stmt = $pdo->prepare("
        SELECT 
            book_id,
            title,
            author,
            isbn,
            category,
            publisher,
            publication_year,
            total_copies,
            available_copies,
            image_url
        FROM books 
        WHERE 
            title LIKE ? OR 
            author LIKE ? OR 
            isbn LIKE ?
        LIMIT 10
    ");
    
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($books) > 0) {
        // Return first result (or you can return all and let frontend choose)
        echo json_encode([
            'success' => true,
            'book' => $books[0],  // Return first match
            'all_results' => $books  // Optional: return all matches
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No book found'
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>