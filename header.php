<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <title>Adorafur Happy Stay</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="header.css">
</head>
<body>
  <!-- HEADER IMAGES -->
  <div class="lHead">
    <img src="Header-Pics/PIC4.png" alt="pic4" class="paws1">
    <img src="Header-Pics/PIC2.png" alt="pic2" class="paw1">
    <img src="Header-Pics/logo.png" alt="LOGO" class="logos">
    <img src="Header-Pics/PIC3.png" alt="pic3" class="paw2">
    <img src="Header-Pics/PIC5.png" alt="pic5" class="paws2">
  </div>

  <?php include 'login.php'?>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <button class="navbar-toggler shadow-none border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="sidebar offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
        
        <div class="offcanvas-header text-white border-bottom">
          <img src="logo.png" alt="LOGO" class="log">
          <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">
          <ul class="navbar-nav justify-content-between flex-grow-1 pe-3">
            
            <!-- ABOUT US -->
            <li class="nav-item dropdown">
            <a href="aboutus.php" class="nav-link <?php echo ($activePage == 'about') ? 'active' : ''; ?> dropdown-toggle">ABOUT US</a>              
              <ul class="dropdown-menu" aria-labelledby="aboutUsDropdown">
                <li><a class="dropdown-item" href="aboutus.php#rule-section">House Rules</a></li>
                <li><a class="dropdown-item" href="aboutus.php#ourstory">Our Story</a></li>
                <li><a class="dropdown-item" href="aboutus.php#time">Opening Hours</a></li>
              </ul>
            </li>

            <!-- BOOK -->
            <li class="nav-item dropdown">
              <a href="home.php#second-scroll-title" class="nav-link <?php echo ($activePage == 'home') ? 'active' : ''; ?> dropdown-toggle">BOOK</a>              
              <ul class="dropdown-menu" aria-labelledby="bookDropdown">
                <li><a class="dropdown-item" href="home.php#second-scroll-title">Book</a></li>
                <li><a class="dropdown-item" href="home.php#inclusions">Inclusion and Perks</a></li>
              </ul>
            </li>

            <!-- HOME -->
            <li class="nav-item">
              <a class="nav-link <?php echo ($activePage == 'home') ? 'active' : ''; ?>" href="home.php">HOME</a>
            </li>

            <!-- CONTACT US -->
            <li class="nav-item dropdown">
            <a href="Contact_Us.php" class="nav-link <?php echo ($activePage == 'contact') ? 'active' : ''; ?> dropdown-toggle">CONTACT US</a>              
              <ul class="dropdown-menu" aria-labelledby="contactUsDropdown">
                <li><a class="dropdown-item" href="Contact_Us.php#main">Contact Us</a></li>
                <li><a class="dropdown-item" href="Contact_Us.php#faqs">FAQs</a></li>
              </ul>
            </li>

            <!-- LOGIN BUTTON -->
            <?php if (isset($_SESSION['c_id'])): ?>
                                <!-- If the user is logged in -->
              <li class="nav-item dropdown">
                <a href="profile.php" class="nav-link <?php echo ($activePage == 'profile') ? 'active' : ''; ?> dropdown-toggle">PROFILE</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
              </li>
              
              <?php else: ?>
                <li class="nav-item">
                  <button type="button" class="nav-link dropdown-toggle" data-bs-toggle="modal" data-bs-target="#loginModal" data-backdrop="false">LOGIN</button>
                </li>
              <?php endif; ?>

          </ul>
        </div>
      </div>
    </div>
  </nav>

  <!-- JAVASCRIPT FOR SMOOTH SCROLLING -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Smooth scroll for in-page links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
          });
        });
      });
    });
  </script>
</body>
</html>