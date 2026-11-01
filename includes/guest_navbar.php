<?php
$secondaryLabel = $guestNav['secondary_label'] ?? 'Home';
$secondaryHref = $guestNav['secondary_href'] ?? '../index.php';

$primaryLabel = $guestNav['primary_label'] ?? 'Login';
$primaryHref = $guestNav['primary_href'] ?? '../login.php';

$logoSrc = $guestNav['logo_src'] ?? '../assets/images/med.png';
?>

<header class="navbar-wrapper">
    <nav class="navbar">

        <div class="logo">
            <img
                src="<?= htmlspecialchars($logoSrc) ?>"
                alt="MediTrack Logo"
            >
        </div>

        <div class="nav-auth">

            <a href="<?= htmlspecialchars($secondaryHref) ?>"
               class="home-link">
                <?= htmlspecialchars($secondaryLabel) ?>
            </a>

            <a href="<?= htmlspecialchars($primaryHref) ?>"
               class="btn-login">
                <?= htmlspecialchars($primaryLabel) ?>
            </a>

        </div>

    </nav>
</header>