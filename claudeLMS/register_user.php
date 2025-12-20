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

// Get form data
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$address = trim($_POST['address']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

//Validate Password
if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit();
}

// Validate password length
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit();
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Check if email already exists
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit();
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

// Generate unique member ID (e.g., LIB-2025-001)
try {
    $year = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE member_id LIKE 'LIB-$year-%'");
    $count = $stmt->fetchColumn();
    $member_id = sprintf("LIB-%s-%03d", $year, $count + 1);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error generating member ID']);
    exit();
}

// Insert new user
try {
    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, phone, address, password_hash, member_id, registration_date, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), 'active')
    ");
    
    $stmt->execute([
        $first_name,
        $last_name,
        $email,
        $phone,
        $address,
        $password_hash,
        $member_id
    ]);
    
    $user_id = $pdo->lastInsertId();
    
    // Log the activity
    $activity_desc = "$first_name $last_name registered as a new member";
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (activity_type, user_id, description) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute(['register_user', $user_id, $activity_desc]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'User registered successfully!',
        'member_id' => $member_id,
        'user_id' => $user_id
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to register user: ' . $e->getMessage()]);
}
?>