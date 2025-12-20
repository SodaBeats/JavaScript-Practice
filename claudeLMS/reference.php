<?php
session_start();

//Database Settings
const DBHOST = 'localhost';
const DBUSER = 'root';
const DBPWD = '';
const DBNAME = 'databasefinals';

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $conn = new mysqli(DBHOST, DBUSER, DBPWD, DBNAME);
    
    if($conn->connect_error){
        die("Connection Failed: ".$conn->connect_error);
    }
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 3. Check if user exists
    $sql = "SELECT * FROM users WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($password === $user['Password']) { // plain text comparison
            $_SESSION['user-name'] = $user['Username'];
            $_SESSION['user-id'] = $user['ID'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>


-- Total Books
SELECT COUNT(*) FROM books;

-- Registered Users
SELECT COUNT(*) FROM users WHERE status = 'active';

-- Books Currently Borrowed
SELECT COUNT(*) FROM borrowed_books WHERE status = 'borrowed';

-- Overdue Books
SELECT COUNT(*) FROM borrowed_books 
WHERE status = 'borrowed' AND due_date < CURDATE();

-- Recent Activity (for activity feed)
SELECT * FROM activity_log 
ORDER BY activity_date DESC 
LIMIT 10;