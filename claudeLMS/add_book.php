<?php
require_once "config.php";

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

$title            = $_POST['title'] ?? '';
$author           = $_POST['author'] ?? '';
$isbn = trim($_POST['isbn']) !== '' ? $_POST['isbn'] : null;
$category         = $_POST['category'] ?? '';
$publisher        = $_POST['publisher'] ?? '';
$publication_year = $_POST['publication_year'] ?? null;
$total_copies     = $_POST['total_copies'] ?? 1;
$image_url = null;

// ------------------ image upload block ------------------
if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === 0) {

    $uploadsDir = 'assets/uploads/';

    // create file name
    $ext      = pathinfo($_FILES['book_image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid("img_") . "." . $ext;

    $targetFile = $uploadsDir . $filename;

    // move file
    move_uploaded_file($_FILES['book_image']['tmp_name'], $targetFile);

    $image_url = $targetFile;
}

// insert
$sql = "INSERT INTO books 
    (title, author, isbn, category, publisher, publication_year, total_copies, image_url)
    VALUES
    (:title, :author, :isbn, :category, :publisher, :publication_year, :total_copies, :image_url)";

$stmt = $pdo->prepare($sql);

$success = $stmt->execute([
    ':title'            => $title,
    ':author'           => $author,
    ':isbn'             => $isbn,
    ':category'         => $category,
    ':publisher'        => $publisher,
    ':publication_year' => $publication_year,
    ':total_copies'     => $total_copies,
    ':image_url'        => $image_url
]);

// Log the activity
$activity_desc = "Book Added: '$title'";
$stmt = $pdo->prepare("
INSERT INTO activity_log (activity_type, description) 
VALUES (?, ?)
");
$stmt->execute(['add_book', $activity_desc]);





header('Location: admin.php');
?>
