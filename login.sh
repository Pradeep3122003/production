#!/bin/bash

# MySQL credentials
DB_HOST="localhost"
DB_USER="pradeep"
DB_PASS="pradeep"
DB_NAME="kaali"

echo "==== Menu ===="
read -p "Enter Password: " password
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m'
# Query to verify login credentials
QUERY="SELECT COUNT(*) FROM user WHERE password='$password';"
ip=$(dig +short myip.opendns.com @resolver1.opendns.com)
if [ "$ip" == "103.108.57.11" ]; then
  LOGIN=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -se "$QUERY")
else
  LOGIN=0
fi

if [ "1234" == "$password" ]; then   #ps -fp $(pgrep openvpn) | grep pts
    if timeout 3 ping -c 1 -W 5 8.8.8.8; then
       wifi="Wifi : connected"
    else
       wifi="Wifi : disconnected"
    fi
    clear
    echo -e "${BLUE}----------------------------------------------------------------------------${NC}"
    echo "Welcome back.............."
    date
    echo "Host: $(hostname)"
    echo "IP: $ip"
    echo "User: $(whoami)"
    echo "$wifi"
    echo -e "${BLUE}----------------------------------------------------------------------------${NC}"


    while true; do
      echo -e "${YELLOW}1. storage  2. Wifi speed  3. Network  4. Websecurity  5. VPN  6. gpg  7. cache  8. VM${NC}"
      read -p "$(echo -e "${RED}menu: ${NC}")" op

      if [ "$op" == "exit" ]; then
        echo "See you"
        break
      elif [ "$op" == "1" ]; then
        df -h
      elif [ "$op" == "2" ]; then
        python3 speedtest.py
      elif [ "$op" == "3" ]; then
        ifconfig
      elif [ "$op" == "4" ]; then
        echo "1. websecure.sh  2. websecure2.sh"
        read sop
        if [ "$sop" == "1" ]; then
          ./websecure/websecure.sh
        elif [ "$sop" == "2" ]; then
          ./websecure/websecure2.sh
        fi
      elif [ "$op" == "5" ]; then
         if ps -fp $(pgrep openvpn) | grep S+ > /dev/null; then
            read -p "Turn off VPN? (n/y)" sop
            if [ "$sop" == "y" ]; then
               sudo kill $(pgrep openvpn)
               sleep 10
               ip=$(dig +short myip.opendns.com @resolver1.opendns.com)
               echo "vpn disconnected"
               exec "$0"
            fi
         else
            echo "Key credentials"
            gpg -d ./keys/proton.gpg > auth.txt
            sudo openvpn --config jp-free-1.protonvpn.tcp.ovpn --auth-user-pass auth.txt > /dev/null &
            sleep 15
            if ps -fp $(pgrep openvpn) | grep S+ > /dev/null; then
               echo "vpn connected."
               echo "close this window and open new"
            fi
        fi
      elif [ "$op" == "6" ]; then
         echo "1. Create and Encrypt  2. Encrypt  3. Decrypt"
         read sop
         if [ "$sop" == "1" ]; then
            read -p "Enter filename: " file
            nano $file
            read -p "Enter recipent name: " re
            gpg -r $re -e $file
         elif [ "$sop" == "2" ]; then
            ls
            read -p "Enter a file to encrypt: " file
            read -p "Enter recipent name: " re
            gpg -r $re -e $file
         elif [ "$sop" == "3" ]; then
            ls
            read -p "Enter a file to decrypt: " file
            gpg -d $file
         fi
      elif [ "$op" == "7" ]; then
         free -h
         read -p "Clear cache?(y/n)" sop
         if [ "$sop" == "y" ]; then
            sudo sync; sudo sysctl -w vm.drop_caches=3
         fi
      elif [ "$op" == "8" ]; then
         echo "1. Shreshta  2. New"
         read sop
         if [ "$sop" == "1" ]; then
            echo "key: nGr5RwSAYCqMW1JcXj0%eHz8*"
            ssh shreshta@192.168.2.133
            exit 0
         elif [ "$sop" == "2" ]; then
            echo "Enter user@vm"
         fi
      fi

          echo -e "${BLUE}----------------------------------------------------------------------------${NC}"
    done

else
    echo "Invalid password, exit."
fi
