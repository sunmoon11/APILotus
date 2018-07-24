<?php
require_once '../api/DBApi.php';

$dbApi = new DBApi();
$dbApi->backupDb();

?>