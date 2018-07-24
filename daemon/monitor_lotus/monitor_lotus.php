<?php

require_once '../api/MonitorApi.php';

$monLotus = MonitorApi::getInstance();

$monLotus->runMonitoring();

echo "monitoring completed ...\n";


