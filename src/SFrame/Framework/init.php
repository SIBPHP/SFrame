<?php
define('SF_ROOT', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);
define('D_TIMESTAMP', time());
define('D_DATETIME', date('Y-m-d H:i:s', D_TIMESTAMP));

require_once __DIR__ .'/helper.php';