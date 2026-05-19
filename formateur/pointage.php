<?php
session_start();
include('../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'formateur') { header('Location: ' . base_url('login.php')); exit(); }
include('../includes/db.php');

$session_id = isset($_GET['session']) ? (int)$_GET['session'] : 0;
if ($session_id <= 0) { $_SESSION['error_message']='Session invalide.'; header('Location: '.base_url('formateur/sessions.php')); exit(); }
$formateur_id = (int)$_SESSION['id'];

$session_sql = "SELECT s.*, f.intitule FROM sessions s INNER JOIN formations f ON f.id=s.formation_id WHERE s.id=$session_id AND s.formateur_id=$formateur_id LIMIT 1";
$session_result = mysqli_query($conn, $session_sql);
if (!$session_result || mysqli_num_rows($session_result)!==1) { $_SESSION['error_message']='Session introuvable ou non autorisee.'; header('Location: '.base_url('formateur/sessions.php')); exit(); }
$session_data = mysqli_fetch_assoc($session_result);
$formation_id = (int)$session_data['formation_id'];

if (isset($_POST['save_pointage'])) {
    $utilisateurs_sql = "SELECT u.id FROM inscriptions i INNER JOIN utilisateurs u ON u.id=i.utilisateur_id WHERE i.formation_id=$formation_id";
    $utilisateurs_result = mysqli_query($conn, $utilisateurs_sql);
    if ($utilisateurs_result) {
        while ($u = mysqli_fetch_assoc($utilisateurs_result)) {
            $uid = (int)$u['id'];
            $statut = isset($_POST['statut'][$uid]) ? trim($_POST['statut'][$uid]) : 'absent';
            $commentaire = isset($_POST['commentaire'][$uid]) ? trim($_POST['commentaire'][$uid]) : '';
            if ($statut !== 'present' && $statut !== 'absent' && $statut !== 'retard') { $statut = 'absent'; }
            $statut_safe = mysqli_real_escape_string($conn, $statut);
            $commentaire_safe = mysqli_real_escape_string($conn, $commentaire);
            $check_sql = "SELECT id FROM presences WHERE session_id=$session_id AND utilisateur_id=$uid LIMIT 1";
            $check_result = mysqli_query($conn, $check_sql);
            if ($check_result && mysqli_num_rows($check_result)===1) {
                $presence = mysqli_fetch_assoc($check_result);
                $pid = (int)$presence['id'];
                mysqli_query($conn, "UPDATE presences SET statut='$statut_safe', commentaire='$commentaire_safe', date_saisie=NOW() WHERE id=$pid");
            } else {
                mysqli_query($conn, "INSERT INTO presences (session_id, utilisateur_id, statut, commentaire, date_saisie) VALUES ($session_id, $uid, '$statut_safe', '$commentaire_safe', NOW())");
            }
        }
    }
    $_SESSION['success_message'] = 'Pointage enregistre avec succes.';
    header('Location: '.base_url('formateur/pointage.php?session='.$session_id));
    exit();
}

$apprenants = array();
$sql = "SELECT u.id, u.nom, u.email, p.statut, p.commentaire
        FROM inscriptions i
        INNER JOIN utilisateurs u ON u.id=i.utilisateur_id
        LEFT JOIN presences p ON p.utilisateur_id=u.id AND p.session_id=$session_id
        WHERE i.formation_id=$formation_id
        ORDER BY u.nom ASC";
$result = mysqli_query($conn, $sql);
if ($result) { while ($r=mysqli_fetch_assoc($result)) { $apprenants[] = $r; } }

$page_title = 'Pointage';
include('../includes/header.php');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Pointage - <?php echo htmlspecialchars($session_data['intitule']); ?> (<?php echo htmlspecialchars($session_data['date']); ?>)</h2>
    <a href="<?php echo base_url('formateur/sessions.php'); ?>" class="btn btn-secondary">Retour</a>
</div>
<div class="card shadow-sm"><div class="card-body">
<?php if(count($apprenants)===0){ ?><div class="alert alert-info mb-0">Aucun apprenant inscrit a cette formation.</div><?php } else { ?>
<form action="" method="post">
<div class="table-responsive"><table class="table table-hover align-middle">
<thead class="table-light"><tr><th>Apprenant</th><th>Email</th><th>Statut</th><th>Commentaire</th></tr></thead><tbody>
<?php foreach($apprenants as $a){ $uid=(int)$a['id']; $st=$a['statut']!==null?$a['statut']:'absent'; ?>
<tr>
<td><?php echo htmlspecialchars($a['nom']); ?></td>
<td><?php echo htmlspecialchars($a['email']); ?></td>
<td>
    <select class="form-select" name="statut[<?php echo $uid; ?>]">
        <option value="present" <?php echo $st==='present'?'selected':''; ?>>present</option>
        <option value="absent" <?php echo $st==='absent'?'selected':''; ?>>absent</option>
        <option value="retard" <?php echo $st==='retard'?'selected':''; ?>>retard</option>
    </select>
</td>
<td><input type="text" class="form-control" name="commentaire[<?php echo $uid; ?>]" value="<?php echo htmlspecialchars($a['commentaire']); ?>"></td>
</tr>
<?php } ?>
</tbody></table></div>
<button type="submit" name="save_pointage" class="btn btn-primary">Enregistrer la feuille de presence</button>
</form>
<?php } ?>
</div></div>
<?php include('../includes/footer.php'); ?>
