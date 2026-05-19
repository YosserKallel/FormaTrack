<?php
include_once(__DIR__ . '/config.php');
if (!isset($page_title)) {
    $page_title = "FormaTrack";
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-size: 16px;
        }
        .hero-card {
            border: 1px solid #e9ecef;
        }
        .feature-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
        }
        .auth-card {
            border: 1px solid #e9ecef;
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?php echo base_url('index.php'); ?>">FormaTrack</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['id'])) { ?>
                    <?php if ($_SESSION['role'] === 'apprenant') { ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('apprenant/dashboard.php'); ?>">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('apprenant/historique.php'); ?>">Historique</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('apprenant/profil.php'); ?>">Profil</a></li>
                    <?php } elseif ($_SESSION['role'] === 'formateur') { ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('formateur/sessions.php'); ?>">Mes sessions</a></li>
                    <?php } elseif ($_SESSION['role'] === 'admin') { ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/users/list.php'); ?>">Administration</a></li>
                    <?php } ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo base_url('logout.php'); ?>">Deconnexion</a></li>
                <?php } else { ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo base_url('index.php'); ?>">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo base_url('register.php'); ?>">Inscription</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo base_url('login.php'); ?>">Connexion</a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
<?php if (isset($_SESSION['success_message']) && $_SESSION['success_message'] !== '') { ?>
    <div class="alert alert-success auto-hide-alert"><?php echo $_SESSION['success_message']; ?></div>
    <?php $_SESSION['success_message'] = ''; ?>
<?php } ?>
<?php if (isset($_SESSION['error_message']) && $_SESSION['error_message'] !== '') { ?>
    <div class="alert alert-danger auto-hide-alert"><?php echo $_SESSION['error_message']; ?></div>
    <?php $_SESSION['error_message'] = ''; ?>
<?php } ?>
