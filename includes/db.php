<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'gestion_assiduite';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die('Erreur de connexion a la base de donnees : ' . mysqli_connect_error());
}
?>
