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
    $_SESSION['error_message'] = 'ID utilisateur invalide.';
    header('Location: ' . base_url('admin/users/list.php'));
    exit();
}

if (isset($_SESSION['id']) && (int) $_SESSION['id'] === $id) {
    $_SESSION['error_message'] = 'Vous ne pouvez pas supprimer votre propre compte admin.';
    header('Location: ' . base_url('admin/users/list.php'));
    exit();
}

$delete_sql = "DELETE FROM utilisateurs WHERE id = $id";
$delete_result = mysqli_query($conn, $delete_sql);

if ($delete_result) {
    $_SESSION['success_message'] = 'Utilisateur supprime avec succes.';
} else {
    $_SESSION['error_message'] = 'Suppression impossible : ' . mysqli_error($conn);
}

header('Location: ' . base_url('admin/users/list.php'));
exit();
?>
