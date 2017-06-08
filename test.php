<?php

$command="ssh -o 'StrictHostKeyChecking no' -o IdentityFile=/var/www/updater/updater_rsa root@151.236.220.176 'if [ -d /var/www/i.isoftik.kz/company/admin ] ; then echo 'directory' ; elif [ -f /var/www/i.isoftik.kz/company/admin ] ; then echo 'file' ; fi'";
//command="ssh -o IdentityFile=/var/www/updater/updater_rsa root@151.236.220.176 ls ~ > /var/www/test/output 2>&1";
//$command="ssh -o 'StrictHostKeyChecking no' -o IdentityFile=/var/www/updater/updater_rsa root@151.236.220.176 ls ~";
//$command="date > /var/www/test/output 2>&1";

$result = \system($command, $retval);

