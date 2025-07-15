# rusadblock
Block list for russian ad networks to be added to /etc/hosts, 127.0.0.1 to keep monitoring activity through local http server


2025.06.30

Added simple script to monitor inbound/outbound connections.

tcpmon - reads /proc/net/tcp and writes to sqlite db in /var/lib/netmon
dnsmon - runs tcpdump -l 53 and writes output to sqlite db /var/lib/netmon

web out two tables - mon.php ( with js autorefresh )

todo: 
add buttons to send to iptables block list and to hosts 

--------

DNS servers

https://github.com/hagezi/dns-blocklists?tab=readme-ov-file
https://curatedhub.github.io/CuratedHub/lists/domain_blacklists/
https://github.com/xRuffKez?tab=repositories