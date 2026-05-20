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
$formations_disponibles = array();
$demandes = array();
$sessions_calendrier = array();

if (isset($_POST['inscription_formation'])) {
    $formation_id = isset($_POST['formation_id']) ? (int) $_POST['formation_id'] : 0;
    if ($formation_id > 0) {
        $check_sql = "SELECT id FROM inscriptions WHERE utilisateur_id = $user_id AND formation_id = $formation_id LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);
        if ($check_result && mysqli_num_rows($check_result) === 0) {
            $insert_sql = "INSERT INTO inscriptions (utilisateur_id, formation_id, date_inscription, statut) VALUES ($user_id, $formation_id, NOW(), 'pending')";
            if (mysqli_query($conn, $insert_sql)) {
                $_SESSION['success_message'] = 'Demande d\'inscription envoyee.';
            } else {
                $_SESSION['error_message'] = "Erreur lors de l'inscription : " . mysqli_error($conn);
            }
        } else {
            $_SESSION['error_message'] = 'Vous avez deja une inscription pour cette formation.';
        }
    } else {
        $_SESSION['error_message'] = 'Formation invalide.';
    }

    header('Location: ' . base_url('apprenant/dashboard.php'));
    exit();
}

if (isset($_POST['annuler_demande'])) {
    $inscription_id = isset($_POST['inscription_id']) ? (int) $_POST['inscription_id'] : 0;
    if ($inscription_id > 0) {
        mysqli_query($conn, "DELETE FROM inscriptions WHERE id = $inscription_id AND utilisateur_id = $user_id AND statut = 'pending'");
        $_SESSION['success_message'] = 'Demande annulee.';
    }
    header('Location: ' . base_url('apprenant/dashboard.php'));
    exit();
}

if (isset($_POST['desinscrire_formation'])) {
    $formation_id = isset($_POST['formation_id']) ? (int) $_POST['formation_id'] : 0;
    if ($formation_id > 0) {
        mysqli_query($conn, "DELETE FROM inscriptions WHERE utilisateur_id = $user_id AND formation_id = $formation_id AND statut = 'approved'");
        $_SESSION['success_message'] = 'Desinscription effectuee.';
    }
    header('Location: ' . base_url('apprenant/dashboard.php'));
    exit();
}
$formations_disponibles = array();

if (isset($_POST['inscription_formation'])) {
    $formation_id = isset($_POST['formation_id']) ? (int) $_POST['formation_id'] : 0;
    if ($formation_id > 0) {
        $check_sql = "SELECT id FROM inscriptions WHERE utilisateur_id = $user_id AND formation_id = $formation_id LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);
        if ($check_result && mysqli_num_rows($check_result) === 0) {
            $insert_sql = "INSERT INTO inscriptions (utilisateur_id, formation_id, date_inscription) VALUES ($user_id, $formation_id, NOW())";
            if (mysqli_query($conn, $insert_sql)) {
                $_SESSION['success_message'] = 'Inscription a la formation effectuee.';
            } else {
                $_SESSION['error_message'] = "Erreur lors de l'inscription : " . mysqli_error($conn);
            }
        } else {
            $_SESSION['error_message'] = 'Vous etes deja inscrit a cette formation.';
        }
    } else {
        $_SESSION['error_message'] = 'Formation invalide.';
    }

    header('Location: ' . base_url('apprenant/dashboard.php'));
    exit();
}

$sql = "SELECT f.id, f.intitule, f.date_debut, f.date_fin,
        SUM(CASE WHEN p.statut = 'present' THEN 1 ELSE 0 END) AS nb_presents,
        SUM(CASE WHEN p.statut = 'retard' THEN 1 ELSE 0 END) AS nb_retards,
        COUNT(p.id) AS total_pointages
        FROM inscriptions i
        INNER JOIN formations f ON f.id = i.formation_id
        LEFT JOIN sessions s ON s.formation_id = f.id
        LEFT JOIN presences p ON p.session_id = s.id AND p.utilisateur_id = i.utilisateur_id
        WHERE i.utilisateur_id = $user_id AND i.statut = 'approved'
        GROUP BY f.id, f.intitule, f.date_debut, f.date_fin
        ORDER BY f.date_debut DESC";

$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $formations[] = $row;
    }
}

$demandes_sql = "SELECT i.id, i.date_inscription, f.intitule, f.date_debut, f.date_fin
                 FROM inscriptions i
                 INNER JOIN formations f ON f.id = i.formation_id
                 WHERE i.utilisateur_id = $user_id AND i.statut = 'pending'
                 ORDER BY i.date_inscription DESC";
$demandes_result = mysqli_query($conn, $demandes_sql);
if ($demandes_result) {
    while ($row = mysqli_fetch_assoc($demandes_result)) {
        $demandes[] = $row;
    }
}

$disponibles_sql = "SELECT f.id, f.intitule, f.description, f.date_debut, f.date_fin
                    FROM formations f
                    WHERE f.id NOT IN (
                        SELECT formation_id FROM inscriptions WHERE utilisateur_id = $user_id AND statut IN ('approved', 'pending')
                    )
                    ORDER BY f.date_debut DESC";
$disponibles_result = mysqli_query($conn, $disponibles_sql);
if ($disponibles_result) {
    while ($row = mysqli_fetch_assoc($disponibles_result)) {
        $formations_disponibles[] = $row;
    }
}

$calendar_sql = "SELECT s.id, s.date, s.heure_debut, s.heure_fin, s.salle, s.notes,
                 f.intitule, u.nom AS formateur_nom
                 FROM inscriptions i
                 INNER JOIN formations f ON f.id = i.formation_id
                 INNER JOIN sessions s ON s.formation_id = f.id
                 LEFT JOIN utilisateurs u ON u.id = s.formateur_id
                 WHERE i.utilisateur_id = $user_id AND i.statut = 'approved'
                 ORDER BY s.date DESC, s.heure_debut DESC";
$calendar_result = mysqli_query($conn, $calendar_sql);
if ($calendar_result) {
    while ($row = mysqli_fetch_assoc($calendar_result)) {
        $sessions_calendrier[] = $row;
    }
}

$disponibles_sql = "SELECT f.id, f.intitule, f.description, f.date_debut, f.date_fin
                    FROM formations f
                    WHERE f.id NOT IN (
                        SELECT formation_id FROM inscriptions WHERE utilisateur_id = $user_id
                    )
                    ORDER BY f.date_debut DESC";
$disponibles_result = mysqli_query($conn, $disponibles_sql);
if ($disponibles_result) {
    while ($row = mysqli_fetch_assoc($disponibles_result)) {
        $formations_disponibles[] = $row;
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
            <div class="alert alert-info mb-0">Aucune formation approuvee pour le moment.</div>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Formation</th>
                        <th>Date debut</th>
                        <th>Date fin</th>
                        <th>Taux d'assiduite</th>
                        <th class="text-end">Action</th>
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
                            <td class="text-end">
                                <form method="post" action="" class="d-inline">
                                    <input type="hidden" name="formation_id" value="<?php echo (int) $formation['id']; ?>">
                                    <button type="submit" name="desinscrire_formation" class="btn btn-sm btn-outline-danger">Se desinscrire</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-body">
        <h3 class="h5 mb-3">Mes demandes d'inscription</h3>
        <?php if (count($demandes) === 0) { ?>
            <div class="alert alert-info mb-0">Aucune demande en attente.</div>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Formation</th>
                        <th>Date debut</th>
                        <th>Date fin</th>
                        <th>Date demande</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($demandes as $demande) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($demande['intitule']); ?></td>
                            <td><?php echo htmlspecialchars($demande['date_debut']); ?></td>
                            <td><?php echo htmlspecialchars($demande['date_fin']); ?></td>
                            <td><?php echo htmlspecialchars($demande['date_inscription']); ?></td>
                            <td class="text-end">
                                <form method="post" action="" class="d-inline">
                                    <input type="hidden" name="inscription_id" value="<?php echo (int) $demande['id']; ?>">
                                    <button type="submit" name="annuler_demande" class="btn btn-sm btn-outline-danger">Annuler</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-body">
        <h3 class="h5 mb-3">Formations disponibles</h3>
        <?php if (count($formations_disponibles) === 0) { ?>
            <div class="alert alert-info mb-0">Aucune nouvelle formation disponible.</div>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Formation</th>
                        <th>Date debut</th>
                        <th>Date fin</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($formations_disponibles as $formation) { ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($formation['intitule']); ?></div>
                                <?php if (!empty($formation['description'])) { ?>
                                    <div class="text-muted small"><?php echo htmlspecialchars($formation['description']); ?></div>
                                <?php } ?>
                            </td>
                            <td><?php echo htmlspecialchars($formation['date_debut']); ?></td>
                            <td><?php echo htmlspecialchars($formation['date_fin']); ?></td>
                            <td class="text-end">
                                <form method="post" action="" class="d-inline">
                                    <input type="hidden" name="formation_id" value="<?php echo (int) $formation['id']; ?>">
                                    <button type="submit" name="inscription_formation" class="btn btn-sm btn-success">S'inscrire</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-body">
        <h3 class="h5 mb-3">Mon calendrier de sessions</h3>
        <?php if (count($sessions_calendrier) === 0) { ?>
            <div class="alert alert-info mb-0">Aucune session planifiee pour le moment.</div>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Formation</th>
                        <th>Date</th>
                        <th>Heures</th>
                        <th>Salle</th>
                        <th>Formateur</th>
                        <th>Notes</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sessions_calendrier as $session) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($session['intitule']); ?></td>
                            <td><?php echo htmlspecialchars($session['date']); ?></td>
                            <td><?php echo htmlspecialchars($session['heure_debut']); ?> - <?php echo htmlspecialchars($session['heure_fin']); ?></td>
                            <td><?php echo htmlspecialchars($session['salle']); ?></td>
                            <td><?php echo htmlspecialchars($session['formateur_nom'] !== null ? $session['formateur_nom'] : '-'); ?></td>
                            <td><?php echo htmlspecialchars($session['notes'] !== null ? $session['notes'] : '-'); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-body">
        <h3 class="h5 mb-3">Formations disponibles</h3>
        <?php if (count($formations_disponibles) === 0) { ?>
            <div class="alert alert-info mb-0">Aucune nouvelle formation disponible.</div>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Formation</th>
                        <th>Date debut</th>
                        <th>Date fin</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($formations_disponibles as $formation) { ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($formation['intitule']); ?></div>
                                <?php if (!empty($formation['description'])) { ?>
                                    <div class="text-muted small"><?php echo htmlspecialchars($formation['description']); ?></div>
                                <?php } ?>
                            </td>
                            <td><?php echo htmlspecialchars($formation['date_debut']); ?></td>
                            <td><?php echo htmlspecialchars($formation['date_fin']); ?></td>
                            <td class="text-end">
                                <form method="post" action="" class="d-inline">
                                    <input type="hidden" name="formation_id" value="<?php echo (int) $formation['id']; ?>">
                                    <button type="submit" name="inscription_formation" class="btn btn-sm btn-success">S'inscrire</button>
                                </form>
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
