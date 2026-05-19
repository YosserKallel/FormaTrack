<?php
session_start();
include('../../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . base_url('login.php'));
    exit();
}
include('../../includes/db.php');

$users = array();
$sql = "SELECT id, nom, email, role, created_at FROM utilisateurs ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

$page_title = 'Liste utilisateurs';
include('../../includes/header.php');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Gestion des utilisateurs</h2>
    <a href="<?php echo base_url('admin/users/create.php'); ?>" class="btn btn-success">Ajouter un utilisateur</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (count($users) === 0) { ?>
            <div class="alert alert-info mb-0">Aucun utilisateur trouve.</div>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Date creation</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user) { ?>
                        <tr>
                            <td><?php echo (int) $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['nom']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($user['role']); ?></span></td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                            <td class="text-end">
                                <a href="<?php echo base_url('admin/users/edit.php?id=' . (int) $user['id']); ?>" class="btn btn-sm btn-warning">Modifier</a>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal<?php echo (int) $user['id']; ?>">
                                    Supprimer
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="confirmDeleteModal<?php echo (int) $user['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirmer la suppression</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Voulez-vous vraiment supprimer l'utilisateur <strong><?php echo htmlspecialchars($user['nom']); ?></strong> ?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <a href="<?php echo base_url('admin/users/delete.php?id=' . (int) $user['id']); ?>" class="btn btn-danger">Supprimer</a>
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
