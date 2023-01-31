#! /bin/bash

function start() {
  echo "Starting xtreamcodes services..."
  start-stop-daemon --start --quiet --pidfile /home/xtreamcodes/iptv_xtream_codes/php/VaiIb8.pid --exec /home/xtreamcodes/iptv_xtream_codes/php/sbin/php-fpm -- --daemonize --fpm-config /home/xtreamcodes/iptv_xtream_codes/php/etc/VaiIb8.conf
  start-stop-daemon --start --quiet --pidfile /home/xtreamcodes/iptv_xtream_codes/php/JdlJXm.pid --exec /home/xtreamcodes/iptv_xtream_codes/php/sbin/php-fpm -- --daemonize --fpm-config /home/xtreamcodes/iptv_xtream_codes/php/etc/JdlJXm.conf
  start-stop-daemon --start --quiet --pidfile /home/xtreamcodes/iptv_xtream_codes/php/CWcfSP.pid --exec /home/xtreamcodes/iptv_xtream_codes/php/sbin/php-fpm -- --daemonize --fpm-config /home/xtreamcodes/iptv_xtream_codes/php/etc/CWcfSP.conf
  /home/xtreamcodes/iptv_xtream_codes/nginx_rtmp/sbin/nginx_rtmp
  /home/xtreamcodes/iptv_xtream_codes/nginx/sbin/nginx
}

function stop() {
  echo "Stopping xtreamcodes services..."
  /home/xtreamcodes/iptv_xtream_codes/nginx_rtmp/sbin/nginx_rtmp -s stop
  /home/xtreamcodes/iptv_xtream_codes/nginx/sbin/nginx -s stop
  kill $(ps aux | grep 'xtreamcodes' | grep -v grep | grep -v 'start_services.sh' | awk '{print $2}') 2>/dev/null
}

function restart() {
  stop
  sleep 4
  start
}

case "$1" in
  start)
    start
    ;;
  stop)
    stop
    ;;
  restart)
    restart
    ;;
  *)
    echo "Usage: $0 {start|stop|restart}"
    exit 1
    ;;
esac

exit 0
