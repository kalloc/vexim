#!/bin/bash
if [ ! -f /etc/ip ];then
    printf "\e[1;31mInstaller Not Detected\e[0m\n"
    exit
fi

if [ ! `cat /etc/.installer_version` -eq 000009 ];then 
    printf "\e[1;31mUpdate for 0.09 Only. Already updated  \e[0m\n"
    exit
fi

echo '
09,39 *     * * *     root   [ -d /var/lib/php5 ] && find /www/*/*/tmp /var/lib/php5/ ! -ipath "*static*" -type f -cmin +$(/usr/lib/php5/maxlifetime) -print0 | xargs -r -0 rm -f
' > /etc/cron.d/php5

rm -f /etc/cron.daily/{file,mysql,mail}backup
ln -s /root/user/tools/{file,mysql,mail}backup /etc/cron.daily/
echo 000010 > /etc/.installer_version
#end
printf "\e[1;32m%s\e[0m\n" "Finish!!!!"


