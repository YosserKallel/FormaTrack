<?php
session_start();
include('../../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . base_url('login.php')); exit(); }
include('../../includes/db.php');

$formations=array(); $apprenants=array();
$fr=mysqli_query($conn,"SELECT id,intitule FROM formations ORDER BY intitule ASC"); if($fr){while($r=mysqli_fetch_assoc($fr)){$formations[]=$r;}}
$ar=mysqli_query($conn,"SELECT id,nom FROM utilisateurs WHERE role='apprenant' ORDER BY nom ASC"); if($ar){while($r=mysqli_fetch_assoc($ar)){$apprenants[]=$r;}}

$formation_id = isset($_GET['formation_id']) ? (int)$_GET['formation_id'] : 0;
$utilisateur_id = isset($_GET['utilisateur_id']) ? (int)$_GET['utilisateur_id'] : 0;

$where = " WHERE 1=1 ";
if($formation_id>0){ $where .= " AND s.formation_id = $formation_id "; }
if($utilisateur_id>0){ $where .= " AND p.utilisateur_id = $utilisateur_id "; }

$data=array();
$sql="SELECT f.intitule, u.nom, p.statut, COUNT(*) AS total
      FROM presences p
      INNER JOIN sessions s ON s.id=p.session_id
      INNER JOIN formations f ON f.id=s.formation_id
      INNER JOIN utilisateurs u ON u.id=p.utilisateur_id
      $where
      GROUP BY f.intitule, u.nom, p.statut
      ORDER BY f.intitule ASC, u.nom ASC";
$rs=mysqli_query($conn,$sql);
if($rs){while($r=mysqli_fetch_assoc($rs)){$data[]=$r;}}

$page_title='Rapport presences'; include('../../includes/header.php');
?>
<h2 class="h4 mb-3">Rapport des presences</h2>
<div class="card shadow-sm mb-3"><div class="card-body">
<form method="get" action="" class="row g-2">
<div class="col-md-5"><select name="formation_id" class="form-select"><option value="0">Toutes les formations</option><?php foreach($formations as $f){ ?><option value="<?php echo (int)$f['id']; ?>" <?php echo $formation_id===(int)$f['id']?'selected':''; ?>><?php echo htmlspecialchars($f['intitule']); ?></option><?php } ?></select></div>
<div class="col-md-5"><select name="utilisateur_id" class="form-select"><option value="0">Tous les apprenants</option><?php foreach($apprenants as $a){ ?><option value="<?php echo (int)$a['id']; ?>" <?php echo $utilisateur_id===(int)$a['id']?'selected':''; ?>><?php echo htmlspecialchars($a['nom']); ?></option><?php } ?></select></div>
<div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Filtrer</button></div>
</form>
</div></div>

<div class="card shadow-sm"><div class="card-body">
<?php if(count($data)===0){ ?><div class="alert alert-info mb-0">Aucune donnee a afficher.</div>
<?php } else { ?>
<div class="table-responsive"><table class="table table-hover align-middle">
<thead class="table-light"><tr><th>Formation</th><th>Apprenant</th><th>Statut</th><th>Total</th></tr></thead>
<tbody>
<?php foreach($data as $d){ ?><tr><td><?php echo htmlspecialchars($d['intitule']); ?></td><td><?php echo htmlspecialchars($d['nom']); ?></td><td><?php echo htmlspecialchars($d['statut']); ?></td><td><?php echo (int)$d['total']; ?></td></tr><?php } ?>
</tbody></table></div>
<?php } ?>
</div></div>
<?php include('../../includes/footer.php'); ?>
