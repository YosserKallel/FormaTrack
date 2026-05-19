<?php
session_start();
include('../../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . base_url('login.php')); exit(); }
include('../../includes/db.php');

$session_id = isset($_GET['session']) ? (int)$_GET['session'] : 0;
$sessions = array();
$sr = mysqli_query($conn, "SELECT s.id, s.date, f.intitule FROM sessions s INNER JOIN formations f ON f.id=s.formation_id ORDER BY s.date DESC");
if($sr){while($s=mysqli_fetch_assoc($sr)){$sessions[]=$s;}}

$presences = array();
if ($session_id > 0) {
    $sql = "SELECT p.id, p.statut, p.commentaire, p.date_saisie, u.nom, u.email, s.date AS session_date, f.intitule
            FROM presences p
            INNER JOIN utilisateurs u ON u.id=p.utilisateur_id
            INNER JOIN sessions s ON s.id=p.session_id
            INNER JOIN formations f ON f.id=s.formation_id
            WHERE p.session_id=$session_id
            ORDER BY u.nom ASC";
    $rs = mysqli_query($conn, $sql);
    if($rs){while($r=mysqli_fetch_assoc($rs)){$presences[]=$r;}}
}

$page_title = 'Liste presences';
include('../../includes/header.php');
?>
<h2 class="h4 mb-3">Feuille de presences</h2>
<div class="card shadow-sm mb-3"><div class="card-body">
<form method="get" action="" class="row g-2">
<div class="col-md-8"><select name="session" class="form-select" required><option value="">Selectionner une session</option>
<?php foreach($sessions as $s){ ?><option value="<?php echo (int)$s['id']; ?>" <?php echo $session_id===(int)$s['id']?'selected':''; ?>><?php echo htmlspecialchars($s['date'].' - '.$s['intitule']); ?></option><?php } ?>
</select></div>
<div class="col-md-4"><button class="btn btn-primary w-100" type="submit">Afficher</button></div>
</form>
</div></div>

<div class="card shadow-sm"><div class="card-body">
<?php if($session_id<=0){ ?><div class="alert alert-info mb-0">Choisissez une session pour voir la feuille.</div>
<?php elseif(count($presences)===0){ ?><div class="alert alert-warning mb-0">Aucune presence saisie pour cette session.</div>
<?php } else { ?>
<div class="table-responsive"><table class="table table-hover align-middle">
<thead class="table-light"><tr><th>Nom</th><th>Email</th><th>Formation</th><th>Date session</th><th>Statut</th><th>Commentaire</th><th class="text-end">Action</th></tr></thead>
<tbody>
<?php foreach($presences as $p){ ?>
<tr>
<td><?php echo htmlspecialchars($p['nom']); ?></td><td><?php echo htmlspecialchars($p['email']); ?></td><td><?php echo htmlspecialchars($p['intitule']); ?></td><td><?php echo htmlspecialchars($p['session_date']); ?></td>
<td><?php echo htmlspecialchars($p['statut']); ?></td><td><?php echo htmlspecialchars($p['commentaire']); ?></td>
<td class="text-end"><a class="btn btn-sm btn-warning" href="<?php echo base_url('admin/presences/edit.php?id='.(int)$p['id']); ?>">Corriger</a></td>
</tr>
<?php } ?>
</tbody></table></div>
<?php } ?>
</div></div>
<?php include('../../includes/footer.php'); ?>
