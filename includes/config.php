<?php
function base_url($path = '')
{
    $script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $parts = explode('/', trim($script_name, '/'));
    $base = '';

    if (isset($parts[0]) && strpos($parts[0], '.php') === false) {
        $base = '/' . $parts[0];
    }

    if ($path === '') {
        return $base;
    }

    return $base . '/' . ltrim($path, '/');
}
?>
