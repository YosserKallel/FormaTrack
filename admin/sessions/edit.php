<?php
session_start();
include('../../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . base_url('login.php')); exit(); }
include('../../includes/db.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { $_SESSION['error_message']='ID session invalide.'; header('Location: '.base_url('admin/sessions/list.php')); exit(); }

$formations=array(); $formateurs=array();
$fr=mysqli_query($conn,"SELECT id,intitule FROM formations ORDER BY intitule ASC"); if($fr){while($r=mysqli_fetch_assoc($fr)){$formations[]=$r;}}
$fo=mysqli_query($conn,"SELECT id,nom FROM utilisateurs WHERE role='formateur' ORDER BY nom ASC"); if($fo){while($r=mysqli_fetch_assoc($fo)){$formateurs[]=$r;}}

$sr=mysqli_query($conn,"SELECT * FROM sessions WHERE id=$id LIMIT 1");
if(!$sr||mysqli_num_rows($sr)!==1){$_SESSION['error_message']='Session introuvable.'; header('Location: '.base_url('admin/sessions/list.php')); exit();}
$s=mysqli_fetch_assoc($sr);
$formation_id=$s['formation_id'];$formateur_id=$s['formateur_id'];$date=$s['date'];$heure_debut=$s['heure_debut'];$heure_fin=$s['heure_fin'];$salle=$s['salle'];
$errors=array('formation_id'=>'','formateur_id'=>'','date'=>'','heure_debut'=>'','heure_fin'=>'','salle'=>''); $general_error='';

if(isset($_POST['update_session'])){
    $formation_id=trim($_POST['formation_id']);$formateur_id=trim($_POST['formateur_id']);$date=trim($_POST['date']);$heure_debut=trim($_POST['heure_debut']);$heure_fin=trim($_POST['heure_fin']);$salle=trim($_POST['salle']);
    if((int)$formation_id<=0){$errors['formation_id']='Formation obligatoire.';}
    if((int)$formateur_id<=0){$errors['formateur_id']='Formateur obligatoire.';}
    if($date===''){$errors['date']='Date obligatoire.';}
    if($heure_debut===''){$errors['heure_debut']='Heure debut obligatoire.';}
    if($heure_fin===''){$errors['heure_fin']='Heure fin obligatoire.';}
    if($heure_debut!==''&&$heure_fin!==''&&$heure_debut>=$heure_fin){$errors['heure_fin']='Heure fin doit etre apres heure debut.';}
    if($salle===''){$errors['salle']='Salle obligatoire.';}
    if($errors['formation_id']===''&&$errors['formateur_id']===''&&$errors['date']===''&&$errors['heure_debut']===''&&$errors['heure_fin']===''&&$errors['salle']===''){
        $f=(int)$formation_id;$foi=(int)$formateur_id;$d=mysqli_real_escape_string($conn,$date);$hd=mysqli_real_escape_string($conn,$heure_debut);$hf=mysqli_real_escape_string($conn,$heure_fin);$sa=mysqli_real_escape_string($conn,$salle);
        $uq="UPDATE sessions SET formation_id=$f, formateur_id=$foi, date='$d', heure_debut='$hd', heure_fin='$hf', salle='$sa' WHERE id=$id";
        if(mysqli_query($conn,$uq)){$_SESSION['success_message']='Session modifiee avec succes.'; header('Location: '.base_url('admin/sessions/list.php')); exit();}
        else{$general_error='Erreur SQL : '.mysqli_error($conn);}
    }
}

$page_title='Modifier session'; include('../../includes/header.php');
?>
<div class="row justify-content-center"><div class="col-md-9 col-lg-8"><div class="card shadow-sm auth-card"><div class="card-body">
<h2 class="h4 mb-3">Modifier session</h2>
<?php if($general_error!==''){ ?><div class="alert alert-danger"><?php echo htmlspecialchars($general_error); ?></div><?php } ?>
<form action="" method="post" onsubmit="return valider(this)">
<div class="row">
<div class="col-md-6 mb-3"><label class="form-label">Formation</label><select id="formation_id" name="formation_id" class="form-select <?php echo $errors['formation_id']!==''?'is-invalid':''; ?>"><option value="">Selectionner</option><?php foreach($formations as $f){ ?><option value="<?php echo (int)$f['id']; ?>" <?php echo (string)$formation_id===(string)$f['id']?'selected':''; ?>><?php echo htmlspecialchars($f['intitule']); ?></option><?php } ?></select><div class="invalid-feedback" id="formation_id_error"><?php echo $errors['formation_id']; ?></div></div>
<div class="col-md-6 mb-3"><label class="form-label">Formateur</label><select id="formateur_id" name="formateur_id" class="form-select <?php echo $errors['formateur_id']!==''?'is-invalid':''; ?>"><option value="">Selectionner</option><?php foreach($formateurs as $f){ ?><option value="<?php echo (int)$f['id']; ?>" <?php echo (string)$formateur_id===(string)$f['id']?'selected':''; ?>><?php echo htmlspecialchars($f['nom']); ?></option><?php } ?></select><div class="invalid-feedback" id="formateur_id_error"><?php echo $errors['formateur_id']; ?></div></div>
</div>
<div class="row">
<div class="col-md-4 mb-3"><label class="form-label">Date</label><input id="date" type="date" name="date" class="form-control <?php echo $errors['date']!==''?'is-invalid':''; ?>" value="<?php echo htmlspecialchars($date); ?>"><div class="invalid-feedback" id="date_error"><?php echo $errors['date']; ?></div></div>
<div class="col-md-4 mb-3"><label class="form-label">Heure debut</label><input id="heure_debut" type="time" name="heure_debut" class="form-control <?php echo $errors['heure_debut']!==''?'is-invalid':''; ?>" value="<?php echo htmlspecialchars($heure_debut); ?>"><div class="invalid-feedback" id="heure_debut_error"><?php echo $errors['heure_debut']; ?></div></div>
<div class="col-md-4 mb-3"><label class="form-label">Heure fin</label><input id="heure_fin" type="time" name="heure_fin" class="form-control <?php echo $errors['heure_fin']!==''?'is-invalid':''; ?>" value="<?php echo htmlspecialchars($heure_fin); ?>"><div class="invalid-feedback" id="heure_fin_error"><?php echo $errors['heure_fin']; ?></div></div>
</div>
<div class="mb-3"><label class="form-label">Salle</label><input id="salle" type="text" name="salle" class="form-control <?php echo $errors['salle']!==''?'is-invalid':''; ?>" value="<?php echo htmlspecialchars($salle); ?>"><div class="invalid-feedback" id="salle_error"><?php echo $errors['salle']; ?></div></div>
<div class="d-flex gap-2"><button type="submit" name="update_session" class="btn btn-primary">Mettre a jour</button><a href="<?php echo base_url('admin/sessions/list.php'); ?>" class="btn btn-secondary">Retour</a></div>
</form>
</div></div></div></div>
<script>
function setErr(id,msg){document.getElementById(id).classList.add('is-invalid');document.getElementById(id+'_error').innerHTML=msg;}
function clearErr(id){document.getElementById(id).classList.remove('is-invalid');document.getElementById(id+'_error').innerHTML='';}
function valider(f){['formation_id','formateur_id','date','heure_debut','heure_fin','salle'].forEach(clearErr);let ok=true;
if(document.getElementById('formation_id').value===''){setErr('formation_id','Formation obligatoire.');ok=false;}
if(document.getElementById('formateur_id').value===''){setErr('formateur_id','Formateur obligatoire.');ok=false;}
if(document.getElementById('date').value===''){setErr('date','Date obligatoire.');ok=false;}
const hd=document.getElementById('heure_debut').value; const hf=document.getElementById('heure_fin').value;
if(hd===''){setErr('heure_debut','Heure debut obligatoire.');ok=false;}
if(hf===''){setErr('heure_fin','Heure fin obligatoire.');ok=false;}
if(hd!==''&&hf!==''&&hd>=hf){setErr('heure_fin','Heure fin doit etre apres heure debut.');ok=false;}
if(document.getElementById('salle').value.trim()===''){setErr('salle','Salle obligatoire.');ok=false;}
return ok;}
</script>
<?php include('../../includes/footer.php'); ?>
