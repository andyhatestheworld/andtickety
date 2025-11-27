<head>
	<link rel="stylesheet" href="assets/navbar.css">
</head>


<nav class="navbar">
    <div class="logo">
        <a href="index.php">
            <img src="assets/images/logo.png" alt="Logo" class="logo-img">
        </a>
    </div>

    <div class="menu-toggle" id="mobile-menu">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
    </div>

    <ul class="nav-menu">
        <li class="nav-item">
            <a href="index.php?page=home" class="nav-link">Home</a>
        </li>
        <li class="nav-item">
            <a href="index.php?page=contact" class="nav-link">Contact</a>
        </li>
        <li class="nav-item">
            <a href="index.php?page=about" class="nav-link">About</a>
        </li>
    </ul>
</nav>

<script>
    const menuToggle = document.getElementById('mobile-menu');
    const navMenu = document.querySelector('.nav-menu');

    menuToggle.addEventListener('click', () => {
        menuToggle.classList.toggle('is-active');
        navMenu.classList.toggle('active');
    });
</script>