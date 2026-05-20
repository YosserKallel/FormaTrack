<?php
session_start();
include('../../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . base_url('login.php'));
    exit();
}
include('../../includes/db.php');

$formation_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($formation_id <= 0) {
    $_SESSION['error_message'] = 'ID formation invalide.';
    header('Location: ' . base_url('admin/formations/list.php'));
    exit();
}

$formation_sql = "SELECT id, intitule FROM formations WHERE id = $formation_id LIMIT 1";
$formation_result = mysqli_query($conn, $formation_sql);
if (!$formation_result || mysqli_num_rows($formation_result) !== 1) {
    $_SESSION['error_message'] = 'Formation introuvable.';
    header('Location: ' . base_url('admin/formations/list.php'));
    exit();
}
$formation = mysqli_fetch_assoc($formation_result);

if (isset($_POST['inscrire'])) {
    $utilisateur_id = (int) $_POST['utilisateur_id'];
    if ($utilisateur_id > 0) {
        $check_sql = "SELECT id, statut FROM inscriptions WHERE utilisateur_id = $utilisateur_id AND formation_id = $formation_id LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);
        if ($check_result && mysqli_num_rows($check_result) === 0) {
            $insert_sql = "INSERT INTO inscriptions (utilisateur_id, formation_id, date_inscription, statut) VALUES ($utilisateur_id, $formation_id, NOW(), 'approved')";
            if (mysqli_query($conn, $insert_sql)) {
                $_SESSION['success_message'] = 'Apprenant inscrit avec succes.';
            } else {
                $_SESSION['error_message'] = "Erreur lors de l'inscription : " . mysqli_error($conn);
            }
        } elseif ($check_result && mysqli_num_rows($check_result) === 1) {
            $existing = mysqli_fetch_assoc($check_result);
            if ($existing['statut'] === 'rejected') {
                mysqli_query($conn, "UPDATE inscriptions SET statut = 'approved' WHERE id = " . (int) $existing['id']);
                $_SESSION['success_message'] = 'Inscription re-approuvee.';
            } else {
                $_SESSION['error_message'] = 'Cet apprenant a deja une inscription en cours.';
            }
        }
    }
    header('Location: ' . base_url('admin/formations/inscriptions.php?id=' . $formation_id));
    exit();
}

if (isset($_GET['approve'])) {
    $inscription_id = (int) $_GET['approve'];
    if ($inscription_id > 0) {
        mysqli_query($conn, "UPDATE inscriptions SET statut = 'approved' WHERE id = $inscription_id AND formation_id = $formation_id");
        $_SESSION['success_message'] = 'Inscription approuvee.';
    }
    header('Location: ' . base_url('admin/formations/inscriptions.php?id=' . $formation_id));
    exit();
}

if (isset($_GET['reject'])) {
    $inscription_id = (int) $_GET['reject'];
    if ($inscription_id > 0) {
        mysqli_query($conn, "UPDATE inscriptions SET statut = 'rejected' WHERE id = $inscription_id AND formation_id = $formation_id");
        $_SESSION['success_message'] = 'Inscription rejetee.';
    }
    header('Location: ' . base_url('admin/formations/inscriptions.php?id=' . $formation_id));
    exit();
}

if (isset($_GET['desinscrire'])) {
    $inscription_id = (int) $_GET['desinscrire'];
    if ($inscription_id > 0) {
        $delete_sql = "DELETE FROM inscriptions WHERE id = $inscription_id AND formation_id = $formation_id";
        if (mysqli_query($conn, $delete_sql)) {
            $_SESSION['success_message'] = 'Apprenant desinscrit avec succes.';
        } else {
            $_SESSION['error_message'] = 'Erreur lors de la desinscription.';
        }
    }
    header('Location: ' . base_url('admin/formations/inscriptions.php?id=' . $formation_id));
    exit();
}

$apprenants = array();
$apprenants_sql = "SELECT id, nom, email FROM utilisateurs WHERE role = 'apprenant' ORDER BY nom ASC";
$apprenants_result = mysqli_query($conn, $apprenants_sql);
if ($apprenants_result) {
    while ($a = mysqli_fetch_assoc($apprenants_result)) {
        $apprenants[] = $a;
    }
}

$inscriptions = array();
$inscriptions_sql = "SELECT i.id, i.date_inscription, i.statut, u.nom, u.email
                     FROM inscriptions i
                     INNER JOIN utilisateurs u ON u.id = i.utilisateur_id
                     WHERE i.formation_id = $formation_id
                     ORDER BY i.date_inscription DESC";
$inscriptions_result = mysqli_query($conn, $inscriptions_sql);
if ($inscriptions_result) {
    while ($ins = mysqli_fetch_assoc($inscriptions_result)) {
        $inscriptions[] = $ins;
    }
}

$page_title = 'Inscriptions formation';
include('../../includes/header.php');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Inscriptions - <?php echo htmlspecialchars($formation['intitule']); ?></h2>
    <a href="<?php echo base_url('admin/formations/list.php'); ?>" class="btn btn-secondary">Retour formations</a>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h3 class="h5 mb-3">Inscrire un apprenant</h3>
        <form action="" method="post" class="row g-2">
            <div class="col-md-8">
                <select name="utilisateur_id" class="form-select" required>
                    <option value="">Selectionner un apprenant</option>
                    <?php foreach ($apprenants as $apprenant) { ?>
                        <option value="<?php echo (int) $apprenant['id']; ?>">
                            <?php echo htmlspecialchars($apprenant['nom'] . ' - ' . $apprenant['email']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" name="inscrire" class="btn btn-success w-100">Inscrire</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h3 class="h5 mb-3">Liste des apprenants inscrits</h3>
        <?php if (count($inscriptions) === 0) { ?>
            <div class="alert alert-info mb-0">Aucun apprenant inscrit.</div>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Date inscription</th>
                        <th>Statut</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($inscriptions as $inscription) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($inscription['nom']); ?></td>
                            <td><?php echo htmlspecialchars($inscription['email']); ?></td>
                            <td><?php echo htmlspecialchars($inscription['date_inscription']); ?></td>
                            <td>
                                <?php if ($inscription['statut'] === 'approved') { ?>
                                    <span class="badge bg-success">Approuve</span>
                                <?php } elseif ($inscription['statut'] === 'rejected') { ?>
                                    <span class="badge bg-danger">Rejete</span>
                                <?php } else { ?>
                                    <span class="badge bg-warning text-dark">En attente</span>
                                <?php } ?>
                            </td>
                            <td class="text-end">
                                <?php if ($inscription['statut'] === 'pending') { ?>
                                    <a href="<?php echo base_url('admin/formations/inscriptions.php?id=' . $formation_id . '&approve=' . (int) $inscription['id']); ?>" class="btn btn-sm btn-success">Approuver</a>
                                    <a href="<?php echo base_url('admin/formations/inscriptions.php?id=' . $formation_id . '&reject=' . (int) $inscription['id']); ?>" class="btn btn-sm btn-outline-danger">Rejeter</a>
                                <?php } elseif ($inscription['statut'] === 'approved') { ?>
                                    <a href="<?php echo base_url('admin/formations/inscriptions.php?id=' . $formation_id . '&desinscrire=' . (int) $inscription['id']); ?>" class="btn btn-sm btn-danger">Desinscrire</a>
                                <?php } else { ?>
                                    <a href="<?php echo base_url('admin/formations/inscriptions.php?id=' . $formation_id . '&approve=' . (int) $inscription['id']); ?>" class="btn btn-sm btn-success">Re-approuver</a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
