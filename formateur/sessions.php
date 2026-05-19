<?php
session_start();
include('../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'formateur') {
    header('Location: ' . base_url('login.php'));
    exit();
}
include('../includes/db.php');
$formateur_id = (int) $_SESSION['id'];
$sessions = array();
$sql = "SELECT s.id, s.date, s.heure_debut, s.heure_fin, s.salle, f.intitule
        FROM sessions s
        INNER JOIN formations f ON f.id = s.formation_id
        WHERE s.formateur_id = $formateur_id
        ORDER BY s.date DESC, s.heure_debut DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sessions[] = $row;
    }
}
$page_title = 'Sessions formateur';
include('../includes/header.php');
?>
<h2 class="h4 mb-3">Mes sessions</h2>
<div class="card shadow-sm">
    <div class="card-body">
        <?php if (count($sessions) === 0) { ?>
            <div class="alert alert-info mb-0">Aucune session affectee.</div>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light"><tr><th>Formation</th><th>Date</th><th>Heures</th><th>Salle</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($sessions as $s) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['intitule']); ?></td>
                            <td><?php echo htmlspecialchars($s['date']); ?></td>
                            <td><?php echo htmlspecialchars($s['heure_debut']); ?> - <?php echo htmlspecialchars($s['heure_fin']); ?></td>
                            <td><?php echo htmlspecialchars($s['salle']); ?></td>
                            <td class="text-end"><a class="btn btn-sm btn-primary" href="<?php echo base_url('formateur/pointage.php?session=' . (int)$s['id']); ?>">Faire le pointage</a></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>
<?php include('../includes/footer.php'); ?>
