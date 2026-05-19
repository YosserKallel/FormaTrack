<?php
session_start();
include('includes/config.php');
session_unset();
session_destroy();
header('Location: ' . base_url('index.php'));
exit();
?>
