<?php
// Start session
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Verification - Adorafur Happy Stay</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: Arial, sans-serif;
    }
    .verification-container {
      max-width: 600px;
      margin: 100px auto;
      padding: 30px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    .success-message {
      color: #28a745;
    }
    .error-message {
      color: #dc3545;
    }
    .logo {
      max-width: 150px;
      margin-bottom: 20px;
    }
  </style>
</head>

<body>
  <div class="verification-container">
    <img src="Header-Pics/logo.png" alt="Adorafur Logo" class="logo">
    <h2>Email Verification</h2>

    <div class="mt-4" id="verificationMessage">
      <p>Verifying your email. Please wait...</p>
    </div>

    <div class="mt-4">
      <a href="index.php" class="btn btn-primary">Return to Homepage</a>
      <button type="button" class="btn btn-success ms-2 d-none" id="loginBtn" data-bs-toggle="modal" data-bs-target="#loginModal">
        Login Now
      </button>
    </div>
  </div>

  <!-- Include login modal -->
  <?php include 'login.php'; ?>

  <script type="module">
    import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm';

    const supabaseUrl = 'https://ygbwanzobuielhttdzsw.supabase.co';
    const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ';

    const supabase = createClient(supabaseUrl, supabaseKey);

    const fragment = new URLSearchParams(window.location.hash.substring(1));
    const access_token = fragment.get('access_token');
    const refresh_token = fragment.get('refresh_token');

    const messageBox = document.getElementById('verificationMessage');
    const loginBtn = document.getElementById('loginBtn');

    if (access_token && refresh_token) {
      supabase.auth.setSession({
        access_token,
        refresh_token
      }).then(({ error }) => {
        if (error) {
          messageBox.innerHTML = `<p class="error-message">There was an error verifying your email: ${error.message}</p>`;
        } else {
          messageBox.innerHTML = `<p class="success-message">Your email has been successfully verified! You can now log in to your account.</p>`;
          loginBtn.classList.remove('d-none');

          setTimeout(() => {
            const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show();
          }, 1500);
        }
      });
    } else {
      messageBox.innerHTML = `<p class="error-message">Invalid verification link. Please try again or request a new email.</p>`;
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
