<?php
session_start();
include('../../includes/config.php');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . base_url('login.php')); exit(); }
include('../../includes/db.php');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { $_SESSION['error_message']='ID session invalide.'; header('Location: '.base_url('admin/sessions/list.php')); exit(); }

$delete = mysqli_query($conn, "DELETE FROM sessions WHERE id = $id");
if ($delete) { $_SESSION['success_message']='Session supprimee avec succes.'; }
else { $_SESSION['error_message']='Suppression impossible : '.mysqli_error($conn); }
header('Location: '.base_url('admin/sessions/list.php'));
exit();
?>
