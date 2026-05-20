<?php
session_start();
include('../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'formateur') {
    header('Location: ' . base_url('login.php'));
    exit();
}
include('../includes/db.php');

$formateur_id = (int) $_SESSION['id'];
$session_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($session_id <= 0) {
    $_SESSION['error_message'] = 'ID session invalide.';
    header('Location: ' . base_url('formateur/sessions.php'));
    exit();
}

$formations = array();
$formations_sql = "SELECT id, intitule FROM formations WHERE formateur_id = $formateur_id ORDER BY intitule ASC";
$formations_result = mysqli_query($conn, $formations_sql);
if ($formations_result) {
    while ($row = mysqli_fetch_assoc($formations_result)) {
        $formations[] = $row;
    }
}

$session_sql = "SELECT * FROM sessions WHERE id = $session_id AND formateur_id = $formateur_id LIMIT 1";
$session_result = mysqli_query($conn, $session_sql);
if (!$session_result || mysqli_num_rows($session_result) !== 1) {
    $_SESSION['error_message'] = 'Session introuvable ou non autorisee.';
    header('Location: ' . base_url('formateur/sessions.php'));
    exit();
}

$session_data = mysqli_fetch_assoc($session_result);
$formation_id = $session_data['formation_id'];
$date = $session_data['date'];
$heure_debut = $session_data['heure_debut'];
$heure_fin = $session_data['heure_fin'];
$salle = $session_data['salle'];
$notes = $session_data['notes'];
$errors = array('formation_id' => '', 'date' => '', 'heure_debut' => '', 'heure_fin' => '', 'salle' => '');
$general_error = '';

if (isset($_POST['update_session'])) {
    $formation_id = trim($_POST['formation_id']);
    $date = trim($_POST['date']);
    $heure_debut = trim($_POST['heure_debut']);
    $heure_fin = trim($_POST['heure_fin']);
    $salle = trim($_POST['salle']);
    $notes = trim($_POST['notes']);

    if ((int) $formation_id <= 0) {
        $errors['formation_id'] = 'Formation obligatoire.';
    }
    if ($date === '') {
        $errors['date'] = 'Date obligatoire.';
    }
    if ($heure_debut === '') {
        $errors['heure_debut'] = 'Heure debut obligatoire.';
    }
    if ($heure_fin === '') {
        $errors['heure_fin'] = 'Heure fin obligatoire.';
    }
    if ($heure_debut !== '' && $heure_fin !== '' && $heure_debut >= $heure_fin) {
        $errors['heure_fin'] = 'Heure fin doit etre apres heure debut.';
    }
    if ($salle === '') {
        $errors['salle'] = 'Salle obligatoire.';
    }

    if ($errors['formation_id'] === '' && $errors['date'] === '' && $errors['heure_debut'] === '' && $errors['heure_fin'] === '' && $errors['salle'] === '') {
        $formation_id_int = (int) $formation_id;
        $check_sql = "SELECT id FROM formations WHERE id = $formation_id_int AND formateur_id = $formateur_id LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);
        if (!$check_result || mysqli_num_rows($check_result) !== 1) {
            $errors['formation_id'] = 'Formation non autorisee.';
        } else {
            $date_safe = mysqli_real_escape_string($conn, $date);
            $hd_safe = mysqli_real_escape_string($conn, $heure_debut);
            $hf_safe = mysqli_real_escape_string($conn, $heure_fin);
            $salle_safe = mysqli_real_escape_string($conn, $salle);
            $notes_safe = mysqli_real_escape_string($conn, $notes);
            $update_sql = "UPDATE sessions
                           SET formation_id = $formation_id_int,
                               date = '$date_safe',
                               heure_debut = '$hd_safe',
                               heure_fin = '$hf_safe',
                               salle = '$salle_safe',
                               notes = '$notes_safe'
                           WHERE id = $session_id AND formateur_id = $formateur_id";
            if (mysqli_query($conn, $update_sql)) {
                $_SESSION['success_message'] = 'Session modifiee avec succes.';
                header('Location: ' . base_url('formateur/sessions.php'));
                exit();
            } else {
                $general_error = 'Erreur SQL : ' . mysqli_error($conn);
            }
        }
    }
}

$page_title = 'Modifier session';
include('../includes/header.php');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Modifier la session</h2>
    <a href="<?php echo base_url('formateur/sessions.php'); ?>" class="btn btn-secondary">Retour</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if ($general_error !== '') { ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($general_error); ?></div>
        <?php } ?>
        <form action="" method="post">
            <div class="mb-3">
                <label for="formation_id" class="form-label">Formation</label>
                <select name="formation_id" id="formation_id" class="form-select <?php echo $errors['formation_id'] !== '' ? 'is-invalid' : ''; ?>">
                    <option value="">Selectionner</option>
                    <?php foreach ($formations as $formation) { ?>
                        <option value="<?php echo (int) $formation['id']; ?>" <?php echo (string) $formation_id === (string) $formation['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($formation['intitule']); ?>
                        </option>
                    <?php } ?>
                </select>
                <div class="invalid-feedback"><?php echo $errors['formation_id']; ?></div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" name="date" id="date" class="form-control <?php echo $errors['date'] !== '' ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($date); ?>">
                    <div class="invalid-feedback"><?php echo $errors['date']; ?></div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="heure_debut" class="form-label">Heure debut</label>
                    <input type="time" name="heure_debut" id="heure_debut" class="form-control <?php echo $errors['heure_debut'] !== '' ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($heure_debut); ?>">
                    <div class="invalid-feedback"><?php echo $errors['heure_debut']; ?></div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="heure_fin" class="form-label">Heure fin</label>
                    <input type="time" name="heure_fin" id="heure_fin" class="form-control <?php echo $errors['heure_fin'] !== '' ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($heure_fin); ?>">
                    <div class="invalid-feedback"><?php echo $errors['heure_fin']; ?></div>
                </div>
            </div>

            <div class="mb-3">
                <label for="salle" class="form-label">Salle</label>
                <input type="text" name="salle" id="salle" class="form-control <?php echo $errors['salle'] !== '' ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($salle); ?>">
                <div class="invalid-feedback"><?php echo $errors['salle']; ?></div>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notes (optionnel)</label>
                <textarea name="notes" id="notes" rows="3" class="form-control"><?php echo htmlspecialchars($notes); ?></textarea>
            </div>

            <button type="submit" name="update_session" class="btn btn-primary">Mettre a jour</button>
        </form>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
