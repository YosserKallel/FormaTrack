<?php
session_start();
include('../../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . base_url('login.php')); exit(); }
include('../../includes/db.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id<=0){$_SESSION['error_message']='ID presence invalide.'; header('Location: '.base_url('admin/presences/list.php')); exit();}

$q="SELECT p.*, u.nom, s.id AS session_id, f.intitule, s.date AS session_date
    FROM presences p
    INNER JOIN utilisateurs u ON u.id=p.utilisateur_id
    INNER JOIN sessions s ON s.id=p.session_id
    INNER JOIN formations f ON f.id=s.formation_id
    WHERE p.id=$id LIMIT 1";
$r=mysqli_query($conn,$q);
if(!$r||mysqli_num_rows($r)!==1){$_SESSION['error_message']='Presence introuvable.'; header('Location: '.base_url('admin/presences/list.php')); exit();}
$p=mysqli_fetch_assoc($r);
$statut=$p['statut']; $commentaire=$p['commentaire']; $errors=array('statut'=>''); $general_error='';

if(isset($_POST['save_presence'])){
    $statut=trim($_POST['statut']); $commentaire=trim($_POST['commentaire']);
    if($statut!=='present'&&$statut!=='absent'&&$statut!=='retard'){$errors['statut']='Statut invalide.';}
    if($errors['statut']===''){
        $st=mysqli_real_escape_string($conn,$statut); $co=mysqli_real_escape_string($conn,$commentaire);
        if(mysqli_query($conn,"UPDATE presences SET statut='$st', commentaire='$co', date_saisie=NOW() WHERE id=$id")){
            $_SESSION['success_message']='Presence corrigee avec succes.';
            header('Location: '.base_url('admin/presences/list.php?session='.(int)$p['session_id'])); exit();
        } else { $general_error='Erreur SQL : '.mysqli_error($conn); }
    }
}

$page_title='Corriger presence'; include('../../includes/header.php');
?>
<div class="row justify-content-center"><div class="col-md-8 col-lg-7"><div class="card shadow-sm auth-card"><div class="card-body">
<h2 class="h4 mb-3">Correction presence</h2>
<p class="text-muted"><?php echo htmlspecialchars($p['nom'].' - '.$p['intitule'].' ('.$p['session_date'].')'); ?></p>
<?php if($general_error!==''){ ?><div class="alert alert-danger"><?php echo htmlspecialchars($general_error); ?></div><?php } ?>
<form action="" method="post">
<div class="mb-3"><label class="form-label">Statut</label><select name="statut" class="form-select <?php echo $errors['statut']!==''?'is-invalid':''; ?>">
<option value="present" <?php echo $statut==='present'?'selected':''; ?>>present</option>
<option value="absent" <?php echo $statut==='absent'?'selected':''; ?>>absent</option>
<option value="retard" <?php echo $statut==='retard'?'selected':''; ?>>retard</option>
</select><div class="invalid-feedback"><?php echo $errors['statut']; ?></div></div>
<div class="mb-3"><label class="form-label">Commentaire</label><textarea name="commentaire" class="form-control" rows="3"><?php echo htmlspecialchars($commentaire); ?></textarea></div>
<div class="d-flex gap-2"><button type="submit" name="save_presence" class="btn btn-primary">Enregistrer</button><a href="<?php echo base_url('admin/presences/list.php?session='.(int)$p['session_id']); ?>" class="btn btn-secondary">Retour</a></div>
</form>
</div></div></div></div>
<?php include('../../includes/footer.php'); ?>
