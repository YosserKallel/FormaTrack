<?php
session_start();
include('includes/config.php');
$page_title = "Accueil - FormaTrack";
include('includes/header.php');
?>

<div class="p-5 mb-4 bg-white rounded-3 shadow-sm hero-card">
    <div class="container-fluid py-3">
        <h1 class="display-6 fw-bold">FormaTrack</h1>
        <p class="col-md-9 fs-5">
            Plateforme web pour suivre les presences, absences et retards des apprenants
            pendant les sessions de formation.
        </p>
        <p class="text-muted mb-4">Acces simple pour apprenant, formateur et administrateur.</p>
        <div class="d-flex gap-2">
            <a href="<?php echo base_url('register.php'); ?>" class="btn btn-success btn-lg">Inscription</a>
            <a href="<?php echo base_url('login.php'); ?>" class="btn btn-primary btn-lg">Connexion</a>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card h-100 shadow-sm feature-card">
            <div class="card-body">
                <h5 class="card-title">Apprenant</h5>
                <p class="card-text">Consulte votre dashboard, votre historique et votre profil.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 shadow-sm feature-card">
            <div class="card-body">
                <h5 class="card-title">Formateur</h5>
                <p class="card-text">Gere les sessions assignees et le pointage des presences.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 shadow-sm feature-card">
            <div class="card-body">
                <h5 class="card-title">Administrateur</h5>
                <p class="card-text">Supervise utilisateurs, formations, sessions, presences et rapports.</p>
            </div>
        </div>
    </div>
</div>

<script>
function chargerHeureServeur() {
    fetch('<?php echo base_url('api/server_time.php'); ?>')
        .then((response) => response.json())
        .then((data) => {
            console.log('Heure serveur:', data.server_time, data.timezone);
        })
        .catch(() => {
            console.log('Impossible de charger l\'heure du serveur.');
        });
}

chargerHeureServeur();
</script>

<?php include('includes/footer.php'); ?>
