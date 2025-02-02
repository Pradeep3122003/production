#!/bin/bash

echo "Select an option:"
echo "1. URL"
echo "2. IP"

read -p "Enter your choice (1/2): " choice

if [ "$choice" == "1" ]; then
  read -p "Enter url: " url
  domain=$(echo $url | sed -e 's#^[^:]*://##' -e 's#^www\.##' -e 's#/.*##')
  ip=$(dig @8.8.8.8 +short $domain)
  echo "URL: "$url
  echo "Domain: "$domain
  echo "IP: "$ip
  if ping -c 1 -W 5 $ip > /dev/null 2>&1; then
    echo "Server is up."
    echo "------------Nmap scanning started-----------"
    nmap -oX nmap1.xml -A $ip
    sudo nmap -oX nmap2.xml -O $ip
    sudo nmap -oX nmap3.xml -sU $ip
    xsltproc nmap1.xml -o nmap1.html
    echo "<a href='nmap2.html'>next</a>" >> nmap1.html
    xsltproc nmap2.xml -o nmap2.html
    echo "<a href='nmap3.html'>next</a>" >> nmap2.html
    xsltproc nmap3.xml -o nmap3.html
    echo "------------------------------"
    echo "Fetching details of "$domain
    echo "------------------------------"
    echo "<html><body><pre>" > whois.html
    whois $domain >> whois.html
    echo "</pre></body></html>" >> whois.html
    echo "<a href='whois.html'>next</a>" >> nmap3.html
    echo "Primary scanning done, nmap1.html report is ready to view."
    echo "------------------------------"
    echo "Scanning for vulnerabilities."
    echo "------------------------------"
    nikto -h $domain -o nikto.html -Format html
    echo "nikto.html report is ready to view"
    echo "------------------------------"

    echo "Which directory search do you want to use:(Enter option)"
    echo "1. dirb -> 4016+ keywords"
    echo "1. dirsearch -> 12266+ keywords"
    echo "3. gobuster ->  151265+ keywords"
    read choice
    if [ "$choice" == "1" ]; then
       echo "---------------dirb tool running---------------"
       dirb $url -o dirb.txt
    elif [ "$choice" == "2" ]; then
       echo "---------------dirsearch tool running---------------"
       python3 /mnt/d/shreshta/websecure/dirsearch/dirsearch.py -u $url -o dirsearch.html -O html
    elif [ "$choice" == "3" ]; then
       echo "---------------gobuster tool running---------------"
    else
       echo "wrong option, quitting the directory search"
    fi
  else
    echo "Server is down!"
  fi
  
elif [ "$choice" == "2" ]; then
  read -p "Enter IP: " ip
  read -p "Enter port: " port 
  host $ip
  if ping -c 1 -W 5 $ip > /dev/null 2>&1; then
    echo "Server is up."
    echo "------------Nmap scanning started-----------"
    nmap -oX nmap1.xml -A $ip
    sudo nmap -oX nmap2.xml -O $ip
    sudo nmap -oX nmap3.xml -sU $ip
    xsltproc nmap1.xml -o nmap1.html
    echo "<a href='nmap2.html'>next</a>" >> nmap1.html
    xsltproc nmap2.xml -o nmap2.html
    echo "<a href='nmap3.html'>next</a>" >> nmap2.html
    xsltproc nmap3.xml -o nmap3.html
    echo "------------------------------"
    echo "Fetching details of "$ip
    echo "------------------------------"
    echo "<html><body><pre>" > whois.html
    whois $ip >> whois.html
    echo "</pre></body></html>" >> whois.html
    echo "<a href='whois.html'>next</a>" >> nmap3.html
    echo "Primary scanning done, nmap1.html report is ready to view."
    echo "------------------------------"
    echo "Scanning for vulnerabilities."
    echo "------------------------------"
    nikto -h $ip -p $port -o nikto.html -Format html
    echo "nikto.html report is ready to view"
    echo "------------------------------"
    
    echo "Which directory search do you want to use:(Enter option)"
    echo "1. dirb -> 4016+ keywords"
    echo "1. dirsearch -> 12266+ keywords"
    echo "3. gobuster ->  151265+ keywords"
    read choice
    if [ "$choice" == "1" ]; then
       echo "---------------dirb tool running---------------"
       dirb "http://"$ip":"$port -o dirb.txt
    elif [ "$choice" == "2" ]; then
       echo "---------------dirsearch tool running---------------"
       python3 /mnt/d/shreshta/websecure/dirsearch/dirsearch.py -u "http://"$ip":"$port -o dirsearch.html -O html
    elif [ "$choice" == "3" ]; then
       echo "---------------gobuster tool running---------------"
    else
       echo "wrong option, quitting the directory search"
    fi
    echo "-----------------------------------------------------"
    echo "-----------Testing ssl ------------------------------"
    /mnt/d/shreshta/websecure/testssl.sh/testssl.sh --htmlfile testssl.html --wide --quiet $ip":"$port
    echo "-----------------------------------------------------"
    echo "-----------Performing Wapiti Test ------------------------------"
    wapiti -u http://165.232.188.219:80 -o wapiti_report
  else
    echo "Server is down!"
  fi
  
else
  echo "Invalid choice. Exiting."
  exit 1
fi

rm nmap1.xml nmap2.xml nmap3.xml
