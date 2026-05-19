<?php
session_start();
include('../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'apprenant') {
    header('Location: ' . base_url('login.php'));
    exit();
}

include('../includes/db.php');

$user_id = (int) $_SESSION['id'];
$historique = array();

$sql = "SELECT p.id, p.statut, p.commentaire, p.date_saisie,
        s.date AS session_date, s.heure_debut, s.heure_fin, s.salle,
        f.intitule
        FROM presences p
        INNER JOIN sessions s ON s.id = p.session_id
        INNER JOIN formations f ON f.id = s.formation_id
        WHERE p.utilisateur_id = $user_id
        ORDER BY s.date DESC, s.heure_debut DESC";

$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $historique[] = $row;
    }
}

$nb_present = 0;
$nb_absent = 0;
$nb_retard = 0;
foreach ($historique as $ligne_count) {
    if ($ligne_count['statut'] === 'present') {
        $nb_present++;
    } elseif ($ligne_count['statut'] === 'retard') {
        $nb_retard++;
    } else {
        $nb_absent++;
    }
}

$page_title = 'Historique des presences';
include('../includes/header.php');
?>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-success-subtle">
            <div class="card-body">
                <div class="text-muted">Presents</div>
                <div class="fs-3 fw-bold text-success"><?php echo $nb_present; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-warning-subtle">
            <div class="card-body">
                <div class="text-muted">Retards</div>
                <div class="fs-3 fw-bold text-warning"><?php echo $nb_retard; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100 border-danger-subtle">
            <div class="card-body">
                <div class="text-muted">Absences</div>
                <div class="fs-3 fw-bold text-danger"><?php echo $nb_absent; ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h2 class="h4 mb-3">Historique de mes presences</h2>
        <?php if (count($historique) === 0) { ?>
            <div class="alert alert-info mb-0">Aucun pointage disponible pour le moment.</div>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Formation</th>
                        <th>Date session</th>
                        <th>Heure</th>
                        <th>Salle</th>
                        <th>Statut</th>
                        <th>Commentaire</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($historique as $ligne) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ligne['intitule']); ?></td>
                            <td><?php echo htmlspecialchars($ligne['session_date']); ?></td>
                            <td><?php echo htmlspecialchars($ligne['heure_debut']); ?> - <?php echo htmlspecialchars($ligne['heure_fin']); ?></td>
                            <td><?php echo htmlspecialchars($ligne['salle']); ?></td>
                            <td>
                                <?php if ($ligne['statut'] === 'present') { ?>
                                    <span class="badge bg-success">Present</span>
                                <?php } elseif ($ligne['statut'] === 'retard') { ?>
                                    <span class="badge bg-warning text-dark">Retard</span>
                                <?php } else { ?>
                                    <span class="badge bg-danger">Absent</span>
                                <?php } ?>
                            </td>
                            <td><?php echo htmlspecialchars($ligne['commentaire']); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
