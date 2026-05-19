<?php
header('Content-Type: application/json');

echo json_encode(array(
    'server_time' => date('Y-m-d H:i:s'),
    'timezone' => date_default_timezone_get()
));
?>
