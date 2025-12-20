<?php
require_once 'config.php';

// Check if logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
date_default_timezone_set('Asia/Manila');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin.css">
    <title>Admin Dashboard</title>
</head>
<body>
    <header>
        <a href="admin.php">
            <img src="assets/logo/Library(1).svg" alt="logo" onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=\'font-size:24px;font-weight:bold;color:#4CAF50\'>📚 LIBRARY</span>'">
        </a>
        <nav class="navbar">
            <ul>
                <li>DASHBOARD</li>
                <li class="dropdown" id="accountDropdown">
                    <div class="dropdown-toggle">ACCOUNT</div>
                    <ul class="dropdown-menu">
                        <li>Profile</li>
                        <li>Settings</li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <?php
        $stmt = $pdo->query("SELECT SUM(total_copies) AS total_books FROM books");
        $totalBooks = $stmt->fetchColumn();
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND status = 'active'");
        $totalUsers = $stmt->fetchColumn();
        $stmt = $pdo->query("SELECT COUNT(*) FROM borrowed_books WHERE status = 'borrowed'");
        $borrowedBooks = $stmt->fetchColumn();
        $stmt = $pdo->query("SELECT COUNT(*) FROM borrowed_books WHERE status = 'overdue'");
        $overdue = $stmt->fetchColumn();
    ?>
    <!--DASHBOARD-->
    <section id="dashboard">
        <div class="stats-grid">
            <a href="totalbooks.html">
                <div class="stat-card">
                    <img src="assets/images/books.png" alt="total books">
                    <div class="numbers-desc">
                        <?php echo "<p>$totalBooks</p>";?>
                        <p>Total Books</p>
                    </div>
                </div>
            </a>
            <a href="users.html">
                <div class="stat-card">
                    <img src="assets/images/users.png" alt="users">
                    <div class="numbers-desc">
                        <?php echo"<p>$totalUsers</p>";?>
                        <p>Registered Users</p>
                    </div>
                </div>
            </a>
            <a href="borrowed.html">
                <div class="stat-card">
                    <img src="assets/images/borrowed.png" alt="borrowed books">
                    <div class="numbers-desc">
                        <?php echo "<p>$borrowedBooks</p>"?>
                        <p>Borrowed Books</p>
                    </div>
                </div>
            </a>
            <a href="overdue.html">
                <div class="stat-card">
                    <img src="assets/images/overdue.png" alt="overdue">
                    <div class="numbers-desc">
                        <?php echo"<p>$overdue</p>"?>
                        <p>Overdue Books</p>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <?php
        $stmt = $pdo->query("SELECT activity_type, description, activity_date FROM activity_log ORDER BY activity_date DESC LIMIT 5");
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <section id="activity-log">


    
        <div class="dashboard-grid">

            <div class="activity-grid">
                <h3>Recent Activity</h3>

                <?php foreach($activities as $act): ?>
                    <div class="activity-item">
                        <div class="activity-info">
                            <h4><?= htmlspecialchars($act['description']); ?></h4>
                            <p><?= htmlspecialchars($act['activity_type']); ?></p>
                        </div>
                        <div class="activity-time">
                            <?= time_elapsed_string($act['activity_date']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php
            // OPTIONAL helper function for "2 minutes ago" style text
            function time_elapsed_string($datetime) {
                $time = strtotime($datetime);
                $diff = time() - $time;

                if ($diff < 60) return $diff . " seconds ago";
                $diff = floor($diff/60);
                if ($diff < 60) return $diff . " minutes ago";
                $diff = floor($diff/60);
                if ($diff < 24) return $diff . " hours ago";
                $diff = floor($diff/24);
                return $diff . " days ago";
            }
            ?>

            <div class="activity-grid">
                <h3>Quick Action</h3>
                <div class="quick-actions">
                    <button class="action-btn" id="addBookBtn" onclick="openModal('addBookModal')">Add New Books</button>
                    <button class="action-btn" onclick="openModal('registerUserModal')">Register New User</button>
                    <button class="action-btn secondary" onclick="openModal('lendBookModal')">Lend Book</button>
                    <button class="action-btn secondary">Return Book</button>
                </div>
            </div>

        </div>

        <!-- Add Book Modal -->
        <div class="modal" id="addBookModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add New Book</h2>
                    <button class="close-btn" onclick="closeModal('addBookModal')">&times;</button>
                </div>
                <form id="addBookForm" method="post" action="add_book.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Book Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="author">Author</label>
                        <input type="text" id="author" name="author" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="isbn">ISBN</label>
                        <input type="text" id="isbn" name="isbn">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="" style="color: gray;">Select Category</option>
                            <option value="Fiction">Fiction</option>
                            <option value="Non-Fiction">Non-Fiction</option>
                            <option value="Romance">Romance</option>
                            <option value="Science">Science</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="History">History</option>
                            <option value="Biography">Biography</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="publisher">Publisher</label>
                        <input type="text" id="publisher" name="publisher">
                    </div>
                    
                    <div class="form-group">
                        <label for="publication_year">Publication Year</label>
                        <input type="number" id="publication_year" name="publication_year" min="1800" max="2025">
                    </div>
                    
                    <div class="form-group">
                        <label for="total_copies">Total Copies</label>
                        <input type="number" id="total_copies" name="total_copies" min="1" value="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="book_image">Book Cover Image</label>
                        <input type="file" id="book_image" name="book_image" accept="image/*">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('addBookModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Book</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Register User Modal -->
        <div class="modal" id="registerUserModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Register New User</h2>
                    <button class="close-btn" onclick="closeModal('registerUserModal')">&times;</button>
                </div>
                <form id="registerUserForm">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('registerUserModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Register User</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Lend Book Modal -->
        <div class="modal" id="lendBookModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Lend Book</h2>
                    <button class="close-btn" onclick="closeModal('lendBookModal')">&times;</button>
                </div>

                <!-- Search Book Section -->
                <div class="search-section">
                    <h3>📚 Search Book</h3>
                    <div class="search-box">
                        <input type="text" id="bookSearch" placeholder="Search by title, author, or ISBN...">
                        <button class="search-btn" onclick="searchBook()">🔍 Search</button>
                    </div>
                    
                    <!-- Book Result Card -->
                    <div class="result-card" id="bookResult">
                        <div class="book-result">
                            <div class="book-image">📖</div>
                            <div class="book-info">
                                <h4 id="bookTitle">The Great Gatsby</h4>
                                <p><strong>Author:</strong> <span id="bookAuthor">F. Scott Fitzgerald</span></p>
                                <p><strong>ISBN:</strong> <span id="bookISBN">978-0-7432-7356-5</span></p>
                                <p><strong>Publisher:</strong> <span id="bookPublisher">Scribner</span></p>
                                <p><strong>Category:</strong> <span id="bookCategory">Fiction</span></p>
                                <span class="availability available" id="bookAvailability">Available: 3/5 copies</span>
                            </div>
                        </div>
                    </div>

                    <div class="no-result" id="bookNoResult" style="display: none;">
                        No book found. Try a different search term.
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Search Borrower Section -->
                <div class="search-section">
                    <h3>👤 Search Borrower</h3>
                    <div class="search-box">
                        <input type="text" id="borrowerSearch" placeholder="Search by name or member ID...">
                        <button class="search-btn" onclick="searchBorrower()">🔍 Search</button>
                    </div>
                    
                    <!-- User Result Card -->
                    <div class="result-card" id="borrowerResult">
                        <div class="user-result">
                            <div class="user-avatar">JD</div>
                            <div class="user-info">
                                <h4 id="userName">John Doe</h4>
                                <p><strong>Member ID:</strong> <span id="userMemberID">LIB-2025-001</span></p>
                                <p><strong>Email:</strong> <span id="userEmail">john.doe@email.com</span></p>
                                <p><strong>Currently Borrowed:</strong> <span id="userBorrowed">2/5 books</span></p>
                            </div>
                        </div>
                        <div class="warning" id="userWarning" style="display: none;">
                            <p>⚠️ This user has 1 overdue book</p>
                        </div>
                    </div>

                    <div class="no-result" id="borrowerNoResult" style="display: none;">
                        No user found. Try a different search term.
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Due Date Section -->
                <div class="form-group">
                    <label for="dueDate">📅 Due Date</label>
                    <input type="date" id="dueDate" name="due_date" required>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('lendBookModal')">Cancel</button>
                    <button type="button" class="btn btn-primary" id="lendBtn" disabled onclick="submitLend()">Lend Book</button>
                </div>
            </div>
        </div>


    </section>
    <!--/DASHBOARD-->



    <script src = "scripts/script.js"></script>
</body>
</html>