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

$check_sql = "SELECT COUNT(*) AS nb FROM sessions WHERE formation_id = $id";
$check_result = mysqli_query($conn, $check_sql);
if ($check_result) {
    $check_data = mysqli_fetch_assoc($check_result);
    if ((int) $check_data['nb'] > 0) {
        $_SESSION['error_message'] = 'Suppression impossible : cette formation contient des sessions.';
        header('Location: ' . base_url('admin/formations/list.php'));
        exit();
    }
}

$delete_sql = "DELETE FROM formations WHERE id = $id";
$delete_result = mysqli_query($conn, $delete_sql);
if ($delete_result) {
    $_SESSION['success_message'] = 'Formation supprimee avec succes.';
} else {
    $_SESSION['error_message'] = 'Suppression impossible : ' . mysqli_error($conn);
}

header('Location: ' . base_url('admin/formations/list.php'));
exit();
?>
