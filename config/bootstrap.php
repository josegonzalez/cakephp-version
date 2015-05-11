<?php

$isCli = php_sapi_name() === 'cli';
if ($isCli) {
    require __DIR__ . '/bootstrap_cli.php';
}
