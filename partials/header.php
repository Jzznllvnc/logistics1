<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard Logistics 1</title>
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha384-nRgPTkuX86pH8yjPJUAFuASXQSSl2/bBUiNV47vSYpKFxHJhbcrGnmlYpYJMeD7a" crossorigin="anonymous">
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Enhanced FOUC prevention with CSS load detection  -->
   <script>
    (function() {
      document.documentElement.classList.add('loading', 'preload');

      const theme = localStorage.getItem('theme');
      if (theme === 'dark') {
        document.documentElement.classList.add('dark-mode');
      }
      
      function showContent() {
        document.documentElement.classList.remove('loading');
        document.documentElement.classList.add('loaded');
        setTimeout(() => {
          document.documentElement.classList.remove('preload');
        }, 150);
      }
      
      if (document.readyState === 'complete') {
        showContent();
      } else {
        window.addEventListener('load', showContent);
        setTimeout(showContent, 500);
      }
    })();
  </script>
</head>
<body>
  <div class="content" id="mainContent">
    <div class="header">
      <div class="hamburger" id="hamburger">
        <i class="fa-solid fa-bars hamburger-icon" id="barsIcon"></i>
        <i class="fa-solid fa-angles-left close-icon" id="xmarkIcon"></i>
      </div>
      <div>
        <h1><?php echo ($_SESSION['role'] === 'admin') ? 'Admin Panel' : 'Staff Panel'; ?> <span class="system-title">| LOGISTICS 1</span></h1>
      </div>
        <div class="theme-toggle-container">
            <div class="admin-profile-dropdown">
                <div class="admin-profile flex items-center bg-[var(--card-bg)] rounded-full shadow-[inset_0_0_0_2px_var(--border-color)] p-2 pr-2" id="adminProfileToggle">
                    <span class="admin-name ml-2 mr-1 text-[var(--text-color)]"><?php echo ($_SESSION['role'] === 'admin') ? 'Administrator' : ucfirst($_SESSION['username'] ?? 'User'); ?></span>
                    <img src="../assets/images/admin.png" alt="Admin Avatar" class="admin-avatar h-7 w-7 rounded-full">
                    <svg class="w-4 h-4 text-[var(--text-color)] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
                <div class="dropdown-menu" id="adminDropdownMenu">
                    <a href="#"><i class="fas fa-chart-bar dropdown-icon"></i> Reports</a>
                    <a href="#" id="logoutButton"><i class="fas fa-sign-out-alt dropdown-icon"></i> Logout</a>
                </div>
            </div>
            <span class="theme-label ml-4"></span>
            <label class="theme-switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
    </div>
    <div class="header-line"></div>

<!-- Logout Confirmation Modal -->
<div id="logoutConfirmModal" class="modal hidden fixed inset-0 flex items-center justify-center z-50">
    <div class="modal-content bg-[var(--card-bg)] p-10 rounded-3xl shadow-xl w-11/12 md:w-1/3 lg:w-1/4 max-h-[90vh] overflow-y-auto relative flex flex-col items-center justify-center text-center">
      <i class="fa-solid fa-arrow-right-from-bracket text-gray-500 text-7xl mb-8"></i>
      <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 mb-8">Confirm Logout</h2>
      <p class="mb-4 text-[var(--text-color)]">Are you sure you want to log out?</p>
      <div class="form-actions flex justify-center pt-4 border-gray-200 dark:border-gray-700 mt-4">
        <button type="button" class="btn bg-[var(--cancel-btn-bg)] hover:bg-gray-400 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" onclick="window.closeModal(document.getElementById('logoutConfirmModal'))">No, cancel</button>
        <button id="confirmLogoutBtn" class="btn btn-danger bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Yes, logout</button>
      </div>
    </div>
</div>
<!-- Custom Alert Component -->
<script src="../assets/js/custom-alerts.js"></script>