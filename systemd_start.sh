#! /bin/bash
sudo -u xtreamcodes /home/xtreamcodes/iptv_xtream_codes/php/bin/php /home/xtreamcodes/iptv_xtream_codes/crons/setup_cache.php
sudo -u xtreamcodes /home/xtreamcodes/iptv_xtream_codes/php/bin/php /home/xtreamcodes/iptv_xtream_codes/tools/signal_receiver.php >/dev/null 2>/dev/null &
sudo -u xtreamcodes /home/xtreamcodes/iptv_xtream_codes/php/bin/php /home/xtreamcodes/iptv_xtream_codes/tools/pipe_reader.php >/dev/null 2>/dev/null &
chown -R xtreamcodes:xtreamcodes /sys/class/net
chown -R xtreamcodes:xtreamcodes /home/xtreamcodes 2>/dev/null
/home/xtreamcodes/iptv_xtream_codes/nginx_rtmp/sbin/nginx_rtmp 
/home/xtreamcodes/iptv_xtream_codes/nginx/sbin/nginx 
/home/xtreamcodes/iptv_xtream_codes/php/sbin/php-fpm --daemonize --fpm-config /home/xtreamcodes/iptv_xtream_codes/php/etc/VaiIb8.conf
/home/xtreamcodes/iptv_xtream_codes/php/sbin/php-fpm --daemonize --fpm-config /home/xtreamcodes/iptv_xtream_codes/php/etc/JdlJXm.conf
/home/xtreamcodes/iptv_xtream_codes/php/sbin/php-fpm --daemonize --fpm-config /home/xtreamcodes/iptv_xtream_codes/php/etc/CWcfSP.conf
