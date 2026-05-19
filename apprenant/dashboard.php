<?php
session_start();
include('../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'apprenant') {
    header('Location: ' . base_url('login.php'));
    exit();
}

include('../includes/db.php');

$user_id = (int) $_SESSION['id'];
$formations = array();

$sql = "SELECT f.id, f.intitule, f.date_debut, f.date_fin,
        SUM(CASE WHEN p.statut = 'present' THEN 1 ELSE 0 END) AS nb_presents,
        SUM(CASE WHEN p.statut = 'retard' THEN 1 ELSE 0 END) AS nb_retards,
        COUNT(p.id) AS total_pointages
        FROM inscriptions i
        INNER JOIN formations f ON f.id = i.formation_id
        LEFT JOIN sessions s ON s.formation_id = f.id
        LEFT JOIN presences p ON p.session_id = s.id AND p.utilisateur_id = i.utilisateur_id
        WHERE i.utilisateur_id = $user_id
        GROUP BY f.id, f.intitule, f.date_debut, f.date_fin
        ORDER BY f.date_debut DESC";

$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $formations[] = $row;
    }
}

$nombre_formations = count($formations);
$moyenne_assiduite = 0;
$somme_taux = 0;
foreach ($formations as $formation_item) {
    $total_item = (int) $formation_item['total_pointages'];
    $credit_item = (int) $formation_item['nb_presents'] + (int) $formation_item['nb_retards'];
    $taux_item = $total_item > 0 ? round(($credit_item / $total_item) * 100, 2) : 0;
    $somme_taux += $taux_item;
}
if ($nombre_formations > 0) {
    $moyenne_assiduite = round($somme_taux / $nombre_formations, 2);
}

$page_title = 'Dashboard apprenant';
include('../includes/header.php');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Bienvenue <?php echo htmlspecialchars($_SESSION['nom']); ?></h2>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted">Formations inscrites</div>
                <div class="fs-3 fw-bold"><?php echo $nombre_formations; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted">Assiduite moyenne</div>
                <div class="fs-3 fw-bold"><?php echo $moyenne_assiduite; ?>%</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h3 class="h5 mb-3">Mes formations et mon assiduite</h3>
        <?php if (count($formations) === 0) { ?>
            <div class="alert alert-info mb-0">Aucune formation trouvee. Contactez votre administrateur pour l'inscription.</div>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Formation</th>
                        <th>Date debut</th>
                        <th>Date fin</th>
                        <th>Taux d'assiduite</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($formations as $formation) { ?>
                        <?php
                        $total = (int) $formation['total_pointages'];
                        $credit = (int) $formation['nb_presents'] + (int) $formation['nb_retards'];
                        $taux = $total > 0 ? round(($credit / $total) * 100, 2) : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($formation['intitule']); ?></td>
                            <td><?php echo htmlspecialchars($formation['date_debut']); ?></td>
                            <td><?php echo htmlspecialchars($formation['date_fin']); ?></td>
                            <td>
                                <div class="progress" role="progressbar" aria-valuenow="<?php echo $taux; ?>" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: <?php echo $taux; ?>%"><?php echo $taux; ?>%</div>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
