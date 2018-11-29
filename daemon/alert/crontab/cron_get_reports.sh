#!/bin/bash

cd /var/www/html/daemon/alert
php ./alert_prospect.php
sleep 250
php ./alert_cap_update.php
sleep 250
php ./alert_cap_update_per_affiliate.php
sleep 250
#php ./alert_decline_percentage_day.php
#sleep 255
#php ./alert_decline_percentage_week.php
#sleep 252
#php ./alert_rebill_2weeks.php

