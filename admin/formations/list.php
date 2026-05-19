<?php
session_start();
include('../../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . base_url('login.php'));
    exit();
}
include('../../includes/db.php');

$formations = array();
$sql = "SELECT f.id, f.intitule, f.description, f.date_debut, f.date_fin, u.nom AS formateur_nom
        FROM formations f
        LEFT JOIN utilisateurs u ON u.id = f.formateur_id
        ORDER BY f.id DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $formations[] = $row;
    }
}

$page_title = 'Liste formations';
include('../../includes/header.php');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Gestion des formations</h2>
    <a href="<?php echo base_url('admin/formations/create.php'); ?>" class="btn btn-success">Ajouter une formation</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (count($formations) === 0) { ?>
            <div class="alert alert-info mb-0">Aucune formation trouvee.</div>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Intitule</th>
                        <th>Description</th>
                        <th>Date debut</th>
                        <th>Date fin</th>
                        <th>Formateur</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($formations as $formation) { ?>
                        <tr>
                            <td><?php echo (int) $formation['id']; ?></td>
                            <td><?php echo htmlspecialchars($formation['intitule']); ?></td>
                            <td><?php echo htmlspecialchars($formation['description']); ?></td>
                            <td><?php echo htmlspecialchars($formation['date_debut']); ?></td>
                            <td><?php echo htmlspecialchars($formation['date_fin']); ?></td>
                            <td><?php echo htmlspecialchars($formation['formateur_nom'] !== null ? $formation['formateur_nom'] : '-'); ?></td>
                            <td class="text-end">
                                <a href="<?php echo base_url('admin/formations/inscriptions.php?id=' . (int) $formation['id']); ?>" class="btn btn-sm btn-info text-white">Inscriptions</a>
                                <a href="<?php echo base_url('admin/formations/edit.php?id=' . (int) $formation['id']); ?>" class="btn btn-sm btn-warning">Modifier</a>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteFormation<?php echo (int) $formation['id']; ?>">
                                    Supprimer
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="confirmDeleteFormation<?php echo (int) $formation['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirmer la suppression</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Voulez-vous supprimer la formation <strong><?php echo htmlspecialchars($formation['intitule']); ?></strong> ?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <a href="<?php echo base_url('admin/formations/delete.php?id=' . (int) $formation['id']); ?>" class="btn btn-danger">Supprimer</a>
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
