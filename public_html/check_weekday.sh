#!/bin/bash
if [[ $(date +%u) -lt 6 ]]; then
    /usr/bin/php /home/u421017040/domains/alpha-investment.org/public_html/daily_profit_update.php
fi
