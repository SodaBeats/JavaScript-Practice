<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

$book_id = isset($data['book_id']) ? intval($data['book_id']) : 0;
$user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
$due_date = isset($data['due_date']) ? trim($data['due_date']) : '';

// Validate required fields
if ($book_id <= 0 || $user_id <= 0 || empty($due_date)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Validate due date format and that it's in the future
$due_date_obj = DateTime::createFromFormat('Y-m-d', $due_date);
$today = new DateTime();
if (!$due_date_obj || $due_date_obj < $today) {
    echo json_encode(['success' => false, 'message' => 'Invalid due date. Must be a future date.']);
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Check if book exists and has available copies
    $stmt = $pdo->prepare("SELECT title, available_copies FROM books WHERE book_id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$book) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Book not found']);
        exit();
    }
    
    if ($book['available_copies'] <= 0) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'No available copies of this book']);
        exit();
    }
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT first_name, last_name, member_id FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    // Insert borrow record
    $stmt = $pdo->prepare("
        INSERT INTO borrowed_books (user_id, book_id, borrow_date, due_date, status) 
        VALUES (?, ?, CURDATE(), ?, 'borrowed')
    ");
    $stmt->execute([$user_id, $book_id, $due_date]);
    $borrow_id = $pdo->lastInsertId();
    
    // Decrease available copies
    $stmt = $pdo->prepare("
        UPDATE books 
        SET available_copies = available_copies - 1 
        WHERE book_id = ?
    ");
    $stmt->execute([$book_id]);
    
    // Log the activity
    $activity_desc = "{$user['first_name']} {$user['last_name']} borrowed \"{$book['title']}\"";
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (activity_type, user_id, book_id, admin_id, description) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute(['borrow', $user_id, $book_id, $_SESSION['admin_id'], $activity_desc]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Book lent successfully!',
        'borrow_id' => $borrow_id,
        'due_date' => $due_date
    ]);
    
} catch(PDOException $e) {
    // Rollback on error
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to lend book: ' . $e->getMessage()
    ]);
}
?>