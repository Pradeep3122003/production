echo "Enter website url:"
read url
domain=$(echo $url | sed -e 's#^[^:]*://##' -e 's#^www\.##' -e 's#/.*##')
ip=$(dig @8.8.8.8 +short $domain)

timeout 1m nikto -h $url -o nikto.html -Format html
echo "----------------------------------------"
echo "nikto.html report is ready to view"
echo "----------------------------------------"

nmap -oX nmap.xml -A $ip
xsltproc nmap.xml -o nmap1.html
rm nmap.xml
echo "----------------------------------------"
echo "nmap1.html report is ready to view"
echo "----------------------------------------"

nmap -oX nmap.xml --top-ports 20 $domain
xsltproc nmap.xml -o nmap2.html
rm nmap.xml
echo "----------------------------------------"
echo "nmap2.html report is ready to view"
echo "----------------------------------------"

curl -I $url > headers.txt
curl -c cookies.txt $url > cookies.txt

cat headers.txt cookies.txt > curl.txt
rm headers.txt cookies.txt

echo "----------------------------------------"
echo "curl.txt report is ready to view"
echo "----------------------------------------"

/mnt/d/shreshta/websecure/testssl.sh/testssl.sh --htmlfile testssl.html --wide --quiet $domain
echo "----------------------------------------"
echo "testssl.html report is ready to view"
echo "----------------------------------------"


dirb $url -o dirb.txt
echo "----------------------------------------"
echo "dirb.txt report is ready to view"
echo "----------------------------------------"

echo "Wapiti scan will get started soon. How many minutes do you want it to scan:"
read min

wapiti -u $url --max-scan-time $min --max-attack-time $(expr $min / 10) -o ./wapiti.html -f html
#wapiti -u https://shreshtait.com -d 2 --max-scan-time 60 --max-attack-time 6 --flush-attacks --flush-session -o ./output.html -f html
echo "----------------------------------------"
echo "wapiti.html report is ready to view"
echo "----------------------------------------"
