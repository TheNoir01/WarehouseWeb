<?php

define('APP_NAME', 'Sistem Gudang 2 PT');
define('API_BASE_URL', 'http://127.0.0.1:8000/api');
define('BASE_URL', '/'); // adjust according to local server/subfolder

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
