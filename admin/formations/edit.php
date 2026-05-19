<?php
session_start();
include('../../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . base_url('login.php'));
    exit();
}
include('../../includes/db.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['error_message'] = 'ID formation invalide.';
    header('Location: ' . base_url('admin/formations/list.php'));
    exit();
}

$errors = array(
    'intitule' => '',
    'date_debut' => '',
    'date_fin' => '',
    'formateur_id' => ''
);
$intitule = '';
$description = '';
$date_debut = '';
$date_fin = '';
$formateur_id = '';
$general_error = '';

$formateurs = array();
$formateurs_sql = "SELECT id, nom FROM utilisateurs WHERE role = 'formateur' ORDER BY nom ASC";
$formateurs_result = mysqli_query($conn, $formateurs_sql);
if ($formateurs_result) {
    while ($f = mysqli_fetch_assoc($formateurs_result)) {
        $formateurs[] = $f;
    }
}

$select_sql = "SELECT * FROM formations WHERE id = $id LIMIT 1";
$select_result = mysqli_query($conn, $select_sql);
if (!$select_result || mysqli_num_rows($select_result) !== 1) {
    $_SESSION['error_message'] = 'Formation introuvable.';
    header('Location: ' . base_url('admin/formations/list.php'));
    exit();
}
$formation = mysqli_fetch_assoc($select_result);
$intitule = $formation['intitule'];
$description = $formation['description'];
$date_debut = $formation['date_debut'];
$date_fin = $formation['date_fin'];
$formateur_id = $formation['formateur_id'];

if (isset($_POST['update_formation'])) {
    $intitule = trim($_POST['intitule']);
    $description = trim($_POST['description']);
    $date_debut = trim($_POST['date_debut']);
    $date_fin = trim($_POST['date_fin']);
    $formateur_id = trim($_POST['formateur_id']);

    if ($intitule === '') {
        $errors['intitule'] = 'Intitule obligatoire.';
    }
    if ($date_debut === '') {
        $errors['date_debut'] = 'Date debut obligatoire.';
    }
    if ($date_fin === '') {
        $errors['date_fin'] = 'Date fin obligatoire.';
    }
    if ($date_debut !== '' && $date_fin !== '' && $date_debut > $date_fin) {
        $errors['date_fin'] = 'La date de fin doit etre >= date debut.';
    }

    $formateur_value = "NULL";
    if ($formateur_id !== '') {
        $formateur_id_int = (int) $formateur_id;
        if ($formateur_id_int <= 0) {
            $errors['formateur_id'] = 'Formateur invalide.';
        } else {
            $formateur_value = (string) $formateur_id_int;
        }
    }

    if ($errors['intitule'] === '' && $errors['date_debut'] === '' && $errors['date_fin'] === '' && $errors['formateur_id'] === '') {
        $intitule_safe = mysqli_real_escape_string($conn, $intitule);
        $description_safe = mysqli_real_escape_string($conn, $description);
        $date_debut_safe = mysqli_real_escape_string($conn, $date_debut);
        $date_fin_safe = mysqli_real_escape_string($conn, $date_fin);

        $update_sql = "UPDATE formations
                       SET intitule = '$intitule_safe',
                           description = '$description_safe',
                           date_debut = '$date_debut_safe',
                           date_fin = '$date_fin_safe',
                           formateur_id = $formateur_value
                       WHERE id = $id";
        $update_result = mysqli_query($conn, $update_sql);
        if ($update_result) {
            $_SESSION['success_message'] = 'Formation modifiee avec succes.';
            header('Location: ' . base_url('admin/formations/list.php'));
            exit();
        } else {
            $general_error = 'Erreur SQL : ' . mysqli_error($conn);
        }
    }
}

$page_title = 'Modifier formation';
include('../../includes/header.php');
?>

<div class="row justify-content-center">
    <div class="col-md-9 col-lg-8">
        <div class="card shadow-sm auth-card">
            <div class="card-body">
                <h2 class="h4 mb-3">Modifier la formation</h2>
                <?php if ($general_error !== '') { ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($general_error); ?></div>
                <?php } ?>
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="intitule" class="form-label">Intitule</label>
                        <input type="text" name="intitule" id="intitule" class="form-control <?php echo $errors['intitule'] !== '' ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($intitule); ?>">
                        <div class="invalid-feedback"><?php echo $errors['intitule']; ?></div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="4" class="form-control"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_debut" class="form-label">Date debut</label>
                            <input type="date" name="date_debut" id="date_debut" class="form-control <?php echo $errors['date_debut'] !== '' ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($date_debut); ?>">
                            <div class="invalid-feedback"><?php echo $errors['date_debut']; ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_fin" class="form-label">Date fin</label>
                            <input type="date" name="date_fin" id="date_fin" class="form-control <?php echo $errors['date_fin'] !== '' ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($date_fin); ?>">
                            <div class="invalid-feedback"><?php echo $errors['date_fin']; ?></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="formateur_id" class="form-label">Formateur</label>
                        <select name="formateur_id" id="formateur_id" class="form-select <?php echo $errors['formateur_id'] !== '' ? 'is-invalid' : ''; ?>">
                            <option value="">-- Aucun formateur --</option>
                            <?php foreach ($formateurs as $formateur) { ?>
                                <option value="<?php echo (int) $formateur['id']; ?>" <?php echo (string) $formateur_id === (string) $formateur['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($formateur['nom']); ?>
                                </option>
                            <?php } ?>
                        </select>
                        <div class="invalid-feedback"><?php echo $errors['formateur_id']; ?></div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="update_formation" class="btn btn-primary">Mettre a jour</button>
                        <a href="<?php echo base_url('admin/formations/list.php'); ?>" class="btn btn-secondary">Retour</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
