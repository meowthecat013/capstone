<?php
// Get user info for sidebar
$stmt = $pdo->prepare("SELECT full_name, TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) AS age, stroke_date, caregiver_name, caregiver_relationship, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>
<!-- Sidebar Styles -->
<style>
    /* Sidebar Styles */
    .sidebar {
        width: 240px;
        background: #2d5a4c;
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .sidebar-header {
        text-align: center;
        padding-bottom: 20px;
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .profile-avatar {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
    }

    .profile-avatar i {
        color: white;
        font-size: 24px;
    }

    .welcome-text {
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
    }

    .user-name {
        color: white;
        font-weight: 600;
        font-size: 16px;
        margin-top: 4px;
    }

    .sidebar-menu {
        list-style: none;
        margin-bottom: 20px;
    }

    .sidebar-menu li {
        margin-bottom: 5px;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        gap: 10px;
        color: white;
        text-decoration: none;
        padding: 10px 12px;
        border-radius: 6px;
        transition: all 0.2s ease;
        font-size: 14px;
    }

    .sidebar-menu a:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .sidebar-menu li.active a {
        background: rgba(255, 255, 255, 0.2);
    }

    .sidebar-menu i {
        width: 20px;
        text-align: center;
        font-size: 14px;
    }

    .logout-section {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 15px;
    }

    .logout-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        color: white;
        text-decoration: none;
        padding: 10px 12px;
        border-radius: 6px;
        transition: all 0.2s ease;
        font-size: 14px;
    }

    .logout-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #ff6b6b;
    }

    /* Dropdown Menu Styles */
    .sidebar-menu .has-dropdown {
        position: relative;
    }

    .sidebar-menu .dropdown-menu {
        display: none;
        list-style: none;
        padding-left: 20px;
        background: #2d5a4c;
        border-left: 2px solid rgba(255, 255, 255, 0.2);
        margin-top: 4px;
    }

    .sidebar-menu .has-dropdown:hover .dropdown-menu {
        display: block;
    }

    .sidebar-menu .dropdown-toggle {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sidebar-menu .dropdown-arrow {
        font-size: 12px;
        transition: transform 0.3s ease;
    }

    .sidebar-menu .has-dropdown:hover .dropdown-arrow {
        transform: rotate(180deg);
    }

    .sidebar-menu .dropdown-menu a {
        padding: 10px 16px;
        font-size: 13px;
    }

    .sidebar-menu .dropdown-menu a:hover {
        background: rgba(255, 255, 255, 0.2);
    }
</style>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="profile-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="welcome-text">Welcome back,</div>
        <div class="user-name"><?php echo htmlspecialchars($userData['full_name']); ?></div>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="user_dashboard.php">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
        </li>
        <li>
            <a href="health.php">
                <i class="fas fa-heartbeat"></i>
                <span>Health Management</span>
            </a>
        </li>
        <li class="has-dropdown">
            <a href="#" class="dropdown-toggle">
                <i class="fas fa-bell"></i>
                <span>Reminders & Schedule</span>
                <i class="fas fa-chevron-down dropdown-arrow"></i>
            </a>
            <ul class="dropdown-menu">
                <li><a href="reminder.php"><i class="fas fa-bell"></i> Reminders</a></li>
                <li><a href="schedule_list.php"><i class="fas fa-calendar"></i> Schedule</a></li>
                <li><a href="alarm.php"><i class="fas fa-clock"></i> Alarm</a></li>
            </ul>
        </li>

        <li class="has-dropdown">
            <a href="#" class="dropdown-toggle">
                <i class="fas fa-brain"></i>
                <span>Cognitive Training</span>
                <i class="fas fa-chevron-down dropdown-arrow"></i>
            </a>
            <ul class="dropdown-menu">
                <li><a href="game_dashboard.php"><i class="fas fa-tachometer-alt"></i> Games Overview</a></li>
                <li><a href="games.php"><i class="fas fa-gamepad"></i> Games</a></li>
            </ul>
        </li>

        <li>
            <a href="journal.php">
                <i class="fas fa-book"></i>
                <span>Personal Journal</span>
            </a>
        </li>
        <li>
            <a href="profile.php">
                <i class="fas fa-user-cog"></i>
                <span>Profile</span>
            </a>
        </li>
        <li>
            <a href="settings.php">
                <i class="fas fa-user-cog"></i>
                <span>Settings</span>
            </a>
        </li>
    </ul>
    
    <div class="logout-section">
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log Out</span>
        </a>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all menu items
    const menuItems = document.querySelectorAll('.sidebar-menu li:not(.has-dropdown)');
    const dropdownMenuItems = document.querySelectorAll('.sidebar-menu .dropdown-menu li');
    
    // Function to set active menu item
    function setActiveMenuItem(clickedItem) {
        // Remove active class from all menu items
        menuItems.forEach(item => {
            item.classList.remove('active');
        });
        
        // Remove active class from all dropdown menu items
        dropdownMenuItems.forEach(item => {
            item.classList.remove('active');
        });
        
        // Add active class to clicked item
        clickedItem.classList.add('active');
        
        // If it's a dropdown menu item, also highlight its parent dropdown
        if (clickedItem.closest('.dropdown-menu')) {
            const parentDropdown = clickedItem.closest('.has-dropdown');
            if (parentDropdown) {
                parentDropdown.classList.add('active');
            }
        }
    }
    
    // Add click event to main menu items
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            setActiveMenuItem(this);
        });
    });
    
    // Add click event to dropdown menu items
    dropdownMenuItems.forEach(item => {
        item.addEventListener('click', function() {
            setActiveMenuItem(this);
        });
    });
    
    // Set initial active menu based on current page
    const currentPage = window.location.pathname.split('/').pop();
    let activeFound = false;
    
    // Check main menu items
    menuItems.forEach(item => {
        const link = item.querySelector('a');
        if (link && link.getAttribute('href') === currentPage) {
            item.classList.add('active');
            activeFound = true;
        }
    });
    
    // Check dropdown menu items if not found in main menu
    if (!activeFound) {
        dropdownMenuItems.forEach(item => {
            const link = item.querySelector('a');
            if (link && link.getAttribute('href') === currentPage) {
                item.classList.add('active');
                // Also highlight parent dropdown
                const parentDropdown = item.closest('.has-dropdown');
                if (parentDropdown) {
                    parentDropdown.classList.add('active');
                }
                activeFound = true;
            }
        });
    }
});
</script>