#! /bin/bash
kill $(ps aux | grep 'xtreamc+' | grep -v grep | grep -v 'start_services.sh' | awk '{print $2}') 2>/dev/null
sleep 1
kill $(ps aux | grep 'xtreamc+' | grep -v grep | grep -v 'start_services.sh' | awk '{print $2}') 2>/dev/null
sleep 1
kill $(ps aux | grep 'xtreamc+' | grep -v grep | grep -v 'start_services.sh' | awk '{print $2}') 2>/dev/null
