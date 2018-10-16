#!/bin/bash

cd /var/www/html/daemon/alert
php ./alert_initial_approval_week.php
sleep 200
php ./alert_initial_approval_day.php

