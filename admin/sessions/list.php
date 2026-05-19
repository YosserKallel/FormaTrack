<?php
session_start();
include('../../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . base_url('login.php'));
    exit();
}
include('../../includes/db.php');

$sessions = array();
$sql = "SELECT s.id, s.date, s.heure_debut, s.heure_fin, s.salle, f.intitule, u.nom AS formateur_nom
        FROM sessions s
        INNER JOIN formations f ON f.id = s.formation_id
        LEFT JOIN utilisateurs u ON u.id = s.formateur_id
        ORDER BY s.date DESC, s.heure_debut DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sessions[] = $row;
    }
}

$page_title = 'Liste sessions';
include('../../includes/header.php');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Gestion des sessions</h2>
    <a href="<?php echo base_url('admin/sessions/create.php'); ?>" class="btn btn-success">Ajouter une session</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (count($sessions) === 0) { ?>
            <div class="alert alert-info mb-0">Aucune session trouvee.</div>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Formation</th>
                        <th>Date</th>
                        <th>Heures</th>
                        <th>Salle</th>
                        <th>Formateur</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sessions as $session) { ?>
                        <tr>
                            <td><?php echo (int) $session['id']; ?></td>
                            <td><?php echo htmlspecialchars($session['intitule']); ?></td>
                            <td><?php echo htmlspecialchars($session['date']); ?></td>
                            <td><?php echo htmlspecialchars($session['heure_debut']); ?> - <?php echo htmlspecialchars($session['heure_fin']); ?></td>
                            <td><?php echo htmlspecialchars($session['salle']); ?></td>
                            <td><?php echo htmlspecialchars($session['formateur_nom'] !== null ? $session['formateur_nom'] : '-'); ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-info text-white" href="<?php echo base_url('admin/presences/list.php?session=' . (int) $session['id']); ?>">Presences</a>
                                <a class="btn btn-sm btn-warning" href="<?php echo base_url('admin/sessions/edit.php?id=' . (int) $session['id']); ?>">Modifier</a>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteSessionModal<?php echo (int) $session['id']; ?>">Supprimer</button>
                            </td>
                        </tr>
                        <div class="modal fade" id="deleteSessionModal<?php echo (int) $session['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirmer la suppression</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">Supprimer cette session ?</div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <a class="btn btn-danger" href="<?php echo base_url('admin/sessions/delete.php?id=' . (int) $session['id']); ?>">Supprimer</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>
<?php include('../../includes/footer.php'); ?>
