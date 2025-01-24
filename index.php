<?php
define ('ROOTPATH', __DIR__);

require_once ROOTPATH . '/internal/config.php';
require_once ROOTPATH . '/internal/Handler.php';
require_once ROOTPATH . '/internal/Database.php';

$handler = new Handler(Database::getConnection());
$result = $handler->getData();

echo json_encode($result);
