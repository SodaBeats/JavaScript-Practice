
const dropdown = document.getElementById('accountDropdown');
const dropdownToggle = dropdown.querySelector('.dropdown-toggle');

// Toggle dropdown on click
dropdownToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.classList.toggle('active');
});

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    if (!dropdown.contains(e.target)) {
        dropdown.classList.remove('active');
    }
});


// MODAL FUNCTIONS
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});


//REGISTER USER MODAL
document.getElementById('registerUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('register_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('User registered successfully! Member ID: ' + data.member_id);
            closeModal('registerUserModal');
            this.reset();
            location.reload(); // Refresh to update stats
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});


//LEND BOOK MODAL
function searchBook() {
    const searchTerm = document.getElementById('bookSearch').value;
    
    if (searchTerm.trim() === '') {
        alert('Please enter a search term');
        return;
    }
    
    // Show loading or disable button
    const searchBtn = event.target;
    searchBtn.disabled = true;
    searchBtn.textContent = '🔍 Searching...';
    
    fetch('search_book.php?query=' + encodeURIComponent(searchTerm))
        .then(response => response.json())
        .then(data => {
            // Re-enable button
            searchBtn.disabled = false;
            searchBtn.textContent = '🔍 Search';
            
            if(data.success && data.book) {
                // Hide no result message
                document.getElementById('bookNoResult').style.display = 'none';
                
                // Update book info in the card
                document.getElementById('bookTitle').textContent = data.book.title;
                document.getElementById('bookAuthor').textContent = data.book.author;
                document.getElementById('bookISBN').textContent = data.book.isbn || 'N/A';
                document.getElementById('bookPublisher').textContent = data.book.publisher || 'N/A';
                document.getElementById('bookCategory').textContent = data.book.category || 'N/A';

                
                // Update availability
                const availability = document.getElementById('bookAvailability');
                availability.textContent = `Available: ${data.book.available_copies}/${data.book.total_copies} copies`;
                
                if(data.book.available_copies > 0) {
                    availability.className = 'availability available';
                } else {
                    availability.className = 'availability unavailable';
                }
                
                // Show result card
                document.getElementById('bookResult').classList.add('active');
                
                // Store selected book
                selectedBook = {
                    id: data.book.book_id,  // ← Map book_id to id
                    title: data.book.title,
                    author: data.book.author,
                    available_copies: parseInt(data.book.available_copies),
                    total_copies: parseInt(data.book.total_copies)
                };
                checkIfCanLend();
            } else {
                // Hide result card
                document.getElementById('bookResult').classList.remove('active');
                
                // Show no result message
                document.getElementById('bookNoResult').style.display = 'block';
                
                selectedBook = null;
                checkIfCanLend();
            }
        })
        .catch(error => {
            searchBtn.disabled = false;
            searchBtn.textContent = '🔍 Search';
            alert('Error: ' + error.message);
        });
}


//SEARCH BORROWER
function searchBorrower() {
    const searchTerm = document.getElementById('borrowerSearch').value;
    
    if (searchTerm.trim() === '') {
        alert('Please enter a search term');
        return;
    }
    
    // Show loading
    const searchBtn = event.target;
    searchBtn.disabled = true;
    searchBtn.textContent = '🔍 Searching...';
    
    fetch('search_borrower.php?query=' + encodeURIComponent(searchTerm))
        .then(response => response.json())
        .then(data => {
            // Re-enable button
            searchBtn.disabled = false;
            searchBtn.textContent = '🔍 Search';
            
            if(data.success && data.user) {
                // Hide no result message
                document.getElementById('borrowerNoResult').style.display = 'none';
                
                // Update user info in the card
                document.getElementById('userName').textContent = data.user.full_name;
                document.getElementById('userMemberID').textContent = data.user.member_id;
                document.getElementById('userEmail').textContent = data.user.email;
                document.getElementById('userBorrowed').textContent = `${data.user.borrowed_count}/5 books`;
                
                // Update avatar with initials
                document.querySelector('.user-avatar').textContent = data.user.initials;
                
                // Show/hide overdue warning
                const warningDiv = document.getElementById('userWarning');
                if(data.user.has_overdue) {
                    warningDiv.style.display = 'block';
                    warningDiv.querySelector('p').textContent = `⚠️ This user has ${data.user.overdue_count} overdue book(s)`;
                } else {
                    warningDiv.style.display = 'none';
                }
                
                // Show result card
                document.getElementById('borrowerResult').classList.add('active');
                
                // Store selected borrower with all info
                selectedBorrower = {
                    id: data.user.id,
                    name: data.user.full_name,
                    member_id: data.user.member_id,
                    borrowed_count: parseInt(data.user.borrowed_count),
                    has_overdue: data.user.has_overdue,
                    overdue_count: parseInt(data.user.overdue_count)
                };
                
                // Check if can lend
                checkIfCanLend();
            } else {
                // Hide result card
                document.getElementById('borrowerResult').classList.remove('active');
                
                // Show no result message
                document.getElementById('borrowerNoResult').style.display = 'block';
                
                selectedBorrower = null;
                checkIfCanLend();
            }
        })
        .catch(error => {
            searchBtn.disabled = false;
            searchBtn.textContent = '🔍 Search';
            alert('Error: ' + error.message);
        });
}




//CHECK IF CAN LEND
function checkIfCanLend() {
    const lendBtn = document.getElementById('lendBtn');
    
    // Basic check: both book and borrower must be selected
    if (!selectedBook || !selectedBorrower) {
        lendBtn.disabled = true;
        lendBtn.style.opacity = '0.5';
        return;
    }
    
    // Check if book has available copies
    if (selectedBook.available_copies <= 0) {
        lendBtn.disabled = true;
        lendBtn.style.opacity = '0.5';
        alert('Cannot lend: This book has no available copies');
        return;
    }
    
    // Check if borrower has overdue books (optional warning - still allows lending)
    if (selectedBorrower.has_overdue) {
        lendBtn.style.background = '#ff9800'; // Orange warning color
        lendBtn.textContent = '⚠️ Lend Book (User has overdue)';
    } else {
        lendBtn.style.background = '#4CAF50'; // Green normal color
        lendBtn.textContent = 'Lend Book';
    }
    
    // Check if borrower reached max borrow limit (e.g., 5 books)
    const MAX_BORROW_LIMIT = 5;
    if (selectedBorrower.borrowed_count >= MAX_BORROW_LIMIT) {
        lendBtn.disabled = true;
        lendBtn.style.opacity = '0.5';
        alert(`Cannot lend: This user has reached the maximum borrow limit (${MAX_BORROW_LIMIT} books)`);
        return;
    }
    
    // All checks passed - enable lend button
    lendBtn.disabled = false;
    lendBtn.style.opacity = '1';
}

//SUBMIT LEND
function submitLend() {
    const dueDate = document.getElementById('dueDate').value;

    // DEBUG: Check what's stored
    console.log('selectedBook:', selectedBook);
    console.log('selectedBorrower:', selectedBorrower);
    console.log('book_id:', selectedBook.id);
    console.log('user_id:', selectedBorrower.id);
    
    // Validate all required data
    if (!selectedBook || !selectedBorrower || !dueDate) {
        alert('Please complete all fields');
        return;
    }
    
    // Confirm before lending
    const confirmMsg = `Lend "${selectedBook.title}" to ${selectedBorrower.name}?\nDue date: ${dueDate}`;
    if (!confirm(confirmMsg)) {
        return;
    }
    
    // Disable button during submission
    const lendBtn = document.getElementById('lendBtn');
    lendBtn.disabled = true;
    lendBtn.textContent = 'Processing...';
    
    // Prepare data
    const lendData = {
        book_id: selectedBook.id,
        user_id: selectedBorrower.id,
        due_date: dueDate
    };
    
    // Send to backend
    fetch('lend_book.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(lendData)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Book lent successfully!\nDue date: ' + data.due_date);
            closeModal('lendBookModal');
            location.reload(); // Refresh to update stats
        } else {
            alert('Error: ' + data.message);
            lendBtn.disabled = false;
            lendBtn.textContent = 'Lend Book';
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
        lendBtn.disabled = false;
        lendBtn.textContent = 'Lend Book';
    });
}