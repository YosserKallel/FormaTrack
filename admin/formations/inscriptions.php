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
        $check_sql = "SELECT id FROM inscriptions WHERE utilisateur_id = $utilisateur_id AND formation_id = $formation_id LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);
        if ($check_result && mysqli_num_rows($check_result) === 0) {
            $insert_sql = "INSERT INTO inscriptions (utilisateur_id, formation_id, date_inscription) VALUES ($utilisateur_id, $formation_id, NOW())";
            if (mysqli_query($conn, $insert_sql)) {
                $_SESSION['success_message'] = 'Apprenant inscrit avec succes.';
            } else {
                $_SESSION['error_message'] = "Erreur lors de l'inscription : " . mysqli_error($conn);
            }
        } else {
            $_SESSION['error_message'] = 'Cet apprenant est deja inscrit.';
        }
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
$inscriptions_sql = "SELECT i.id, i.date_inscription, u.nom, u.email
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
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($inscriptions as $inscription) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($inscription['nom']); ?></td>
                            <td><?php echo htmlspecialchars($inscription['email']); ?></td>
                            <td><?php echo htmlspecialchars($inscription['date_inscription']); ?></td>
                            <td class="text-end">
                                <a href="<?php echo base_url('admin/formations/inscriptions.php?id=' . $formation_id . '&desinscrire=' . (int) $inscription['id']); ?>" class="btn btn-sm btn-danger">Desinscrire</a>
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
