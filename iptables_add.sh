#apt-get install iptables ipset

#iptables -P INPUT ACCEPT
#iptables -P FORWARD ACCEPT
#iptables -P OUTPUT ACCEPT
#iptables -t nat -F
#iptables -t mangle -F
iptables -F
iptables -X

ipset create deny_list hash:ip

ipset flush
ipset flush deny_list
ipset add deny_list 152.53.133.166

#s3-1-w.amazonaws.com	54.231.229.249
#20250710 15:16		151.101.194.132	
ipset add deny_list 151.101.194.132
#20250710 15:15	/usr/lib/firefox-esr/firefox-esr	185x187x112x2.static-business.msk.ertelecom.ru	185.187.112.2
#20250710 15:15	/usr/lib/firefox-esr/firefox-esr	104.18.20.226	
ipset add deny_list 104.18.20.226
#20250710 14:05	/usr/lib/firefox-esr/firefox-esr	server-108-156-16-76.hel51.r.cloudfront.net	
ipset add deny_list 108.156.16.76
#20250710 14:05	/usr/lib/firefox-esr/firefox-esr	ec2-63-177-157-111.eu-central-1.compute.amazonaws.com	
ipset add deny_list 63.177.157.111
#20250710 14:05		server-18-165-122-60.hel51.r.cloudfront.net	
ipset add deny_list 18.165.122.60
#20250710 14:05	/usr/lib/firefox-esr/firefox-esr	s3-1-w.amazonaws.com	
ipset add deny_list 54.231.229.249
#20250710 14:05		141.193.213.10	
ipset add deny_list 141.193.213.10
#20250710 14:03	/usr/lib/firefox-esr/firefox-esr	localhost	127.0.0.1
#20250710 14:03	/usr/lib/firefox-esr/firefox-esr	151.101.65.91	
#151.101.65.91
#20250710 14:03	/usr/lib/firefox-esr/firefox-esr	
#82.221.107.34.bc.googleusercontent.com	
ipset add deny_list 34.107.221.82
#20250710 14:03	/usr/lib/firefox-esr/firefox-esr	191.144.160.34.bc.googleusercontent.com	
ipset add deny_list 34.160.144.191
#20250710 15:39	/usr/lib/firefox-esr/firefox-esr	lj-in-f147.1e100.net	
ipset add deny_list 64.233.163.147
#20250710 15:37	/usr/lib/firefox-esr/firefox-esr	lu-in-f155.1e100.net	
ipset add deny_list 74.125.131.155
#20250710 15:37	/usr/lib/firefox-esr/firefox-esr	104.18.87.42	104.18.87.42
#20250710 15:37	/usr/lib/firefox-esr/firefox-esr	8.6.112.6	8.6.112.6
#20250710 15:37		104.18.40.222	104.18.40.222
#20250710 15:37	/usr/lib/firefox-esr/firefox-esr	lt-in-f139.1e100.net	
ipset add deny_list 108.177.14.139
#20250710 15:37	/usr/lib/firefox-esr/firefox-esr	192.0.73.2	192.0.73.2
#20250710 15:37	/usr/lib/firefox-esr/firefox-esr	8.47.69.6	8.47.69.6
#20250710 15:37	/usr/lib/firefox-esr/firefox-esr	lh-in-f100.1e100.net	
ipset add deny_list 64.233.161.100
#20250710 15:36	/usr/lib/firefox-esr/firefox-esr	lh-in-f113.1e100.net	
ipset add deny_list 64.233.161.113
20250710 15:36	/usr/lib/firefox-esr/firefox-esr	xvm-20-29.dc0.ghst.net	92.243.20.29
#20250710 15:36	/usr/lib/firefox-esr/firefox-esr	le-in-f94.1e100.net	
ipset add deny_list 74.125.205.94
#20250710 15:36	/usr/lib/firefox-esr/firefox-esr	la-in-f94.1e100.net	
ipset add deny_list 142.250.150.94
#20250710 15:36	/usr/lib/firefox-esr/firefox-esr	lr-in-f94.1e100.net	
ipset add deny_list 209.85.233.94
#20250710 15:36	/usr/lib/firefox-esr/firefox-esr	lh-in-f84.1e100.net	
ipset add deny_list 64.233.161.84
#20250710 15:36	/usr/lib/firefox-esr/firefox-esr	lb-in-f141.1e100.net	
ipset add deny_list 142.251.1.141
#20250710 15:36	/usr/lib/firefox-esr/firefox-esr	lu-in-f95.1e100.net	
ipset add deny_list 74.125.131.95
#20250710 15:36		lh-in-f106.1e100.net	
ipset add deny_list 64.233.161.106

#20250710 15:49	/usr/lib/firefox-esr/firefox-esr	191.144.160.34.bc.googleusercontent.com	
ipset add deny_list 34.160.144.191
#20250710 15:49	/usr/lib/firefox-esr/firefox-esr	151.101.1.91	
ipset add deny_list 151.101.1.91
#20250710 15:49	/usr/lib/firefox-esr/firefox-esr	82.221.107.34.bc.googleusercontent.com	
ipset add deny_list 34.107.221.82
ipset add deny_list 151.101.1.91
ipset add deny_list 151.101.3.19

iptables -I INPUT 1 -m set --match-set deny_list src -j DROP
iptables-save

