<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'application.php';
if (!empty($argv[1])) {
    $_SERVER['REQUEST_URI'] = $argv[1];
}
ZfApplication::bootstrap(dirname(__FILE__));