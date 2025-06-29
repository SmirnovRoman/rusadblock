#!/usr/bin/php
<?php

/*
    @RSmirnov 
    2025.07.01
    simple tcpmonitor 
    
    todo dns monitor:
    tcpdump -l port 53
*/

if(@$argv[1]=="install"){
    exec("chmod +x ./tcpmon.php"):
    exec("cp ./tcpmon.php /usr/bin/");
    file_put_contents("/etc/systemd/system/monitor.service",
"[Unit]
Description=monitor
After=network.target
[Service]
User=root
Restart=on-failure
ExecStart=/usr/bin/tcpmon.php 1> /var/lib/tcpmon/log1 2> /var/lib/tcpmon/log2 

[Install]
WantedBy=network.target
");

    exec("systemctl daemon-reload");
    exec("systemctl enable monitor");
    exec("systemctl start monitor");
    return;
}

$dir="/var/lib/tcpmon";
@mkdir($dir);
$dbFile = $dir.'/tcp_connections.db';

$db = new SQLite3($dbFile);

$hn=gethostname();
$previps=array();

$db->exec("CREATE TABLE IF NOT EXISTS tcp_monitor (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    local_address TEXT,
    remote_address TEXT,
    local_hostname TEXT,
    remote_hostname TEXT,
    local_port INTEGER,
    remote_port INTEGER,
    pid INTEGER,
    pname TEXT,
    pcmd TEXT,
    ts INTEGER,
    d2 integer,
    host TEXT,
    tcpstatus TEXT
)");


$db->exec("CREATE TABLE IF NOT EXISTS tcp_newconnections (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    local_address TEXT,
    remote_address TEXT,
    local_hostname TEXT,
    remote_hostname TEXT,
    local_port INTEGER,
    remote_port INTEGER,
    pid INTEGER,
    pname TEXT,
    pcmd TEXT,
    ts INTEGER,
    d2 integer,
    host TEXT,
    tcpstatus TEXT
)");


$hostnameCache = [];

function getHostname2($ip) {
    global $hostnameCache, $hn;
    
    if (isset($hostnameCache[$ip])) {
        return $hostnameCache[$ip];
    }
    
    $hostname = gethostbyaddr($ip);
    $hostnameCache[$ip] = $hostname;
    return $hostname;
}

function hexToIp($hex) {
    if (strlen($hex) !== 8) {
        return false; 
    }

    $ipParts = [];
    for ($i = 0; $i < 8; $i += 2) {
        $ipParts[] = hexdec(substr($hex,$i, 2));
    }

    return implode('.', array_reverse($ipParts));
}




// Функция для парсинга файла /proc/net/tcp
function parseTcpFile($db) {
    global $hn,$previps;
    
    $previps1=array();
    
    $file = '/proc/net/tcp';
    $handle = fopen($file, 'r');
    
    if ($handle) {
        // Пропускаем первую строку (заголовки)
        fgets($handle);
        
        while (($line = fgets($handle)) !== false) {
            $fields = preg_split('/\s+/', trim($line));
            
            if (count($fields) < 9) continue;

            $localAddress = $fields[1];
            $remoteAddress = $fields[2];
            $pid = $fields[9];

            list($localIp, $localPortHex) = explode(':', $localAddress);
            list($remoteIp, $remotePortHex) = explode(':', $remoteAddress);
            $localPort = hexdec($localPortHex);
            $remotePort = hexdec($remotePortHex);

	    $tcpstatus=parseTcpState($fields[3]);

            $localIp=hexToIp($localIp);
            $remoteIp=hexToIp($remoteIp);
            
            if($remotePort=="80"){
            }
            $localHostname = getHostname2($localIp);
            $remoteHostname = getHostname2($remoteIp);
            $pname="";
            $pcmd="";
            
	    $pid2=findPidByInode($pid);
	    if($pid2!=null){
        	$pname = getProcessName($pid2);
            }

    	    $t=explode(" ",$pname);

	    if(in_array($remoteIp,$previps)==false){
                echo date("Ymd H:i")."\t".$localIp."\t".$remoteIp.":".$remotePort."\t".$pname."\t".$pid."\t".$pid2."\t".$tcpstatus."\n";
                $previps[]=$remoteIp;
                
                
                $stmt = $db->prepare("INSERT INTO tcp_newconnections (local_address, remote_address, local_hostname, remote_hostname, local_port, remote_port, pid, pname, pcmd, ts, d2, host, tcpstatus) VALUES (:local_address, :remote_address, :local_hostname, :remote_hostname, :local_port, :remote_port, :pid, :pname, :pcmd, :ts, :d2, :host, :tcpstatus)");
            $stmt->bindValue(':local_address', $localIp, SQLITE3_TEXT);
            $stmt->bindValue(':remote_address', $remoteIp, SQLITE3_TEXT);
            $stmt->bindValue(':local_hostname', $localHostname, SQLITE3_TEXT);
            $stmt->bindValue(':remote_hostname', $remoteHostname, SQLITE3_TEXT);
            $stmt->bindValue(':local_port', $localPort, SQLITE3_INTEGER);
            $stmt->bindValue(':remote_port', $remotePort, SQLITE3_INTEGER);
            $stmt->bindValue(':pid', $pid, SQLITE3_INTEGER);
            $stmt->bindValue(':pcmd', $pname, SQLITE3_TEXT);
            $stmt->bindValue(':pname', @$t[0], SQLITE3_TEXT);
            $stmt->bindValue(':ts', time(), SQLITE3_INTEGER);
            $stmt->bindValue(':d2', date("Ymd"), SQLITE3_INTEGER);
            $stmt->bindValue(':host', $hn, SQLITE3_TEXT);
    	    $stmt->bindValue(':tcpstatus', $tcpstatus, SQLITE3_TEXT);
            $stmt->execute();
            }
            



            $stmt = $db->prepare("INSERT INTO tcp_monitor (local_address, remote_address, local_hostname, remote_hostname, local_port, remote_port, pid, pname, pcmd, ts, d2, host, tcpstatus) VALUES (:local_address, :remote_address, :local_hostname, :remote_hostname, :local_port, :remote_port, :pid, :pname, :pcmd, :ts, :d2, :host, :tcpstatus)");
            $stmt->bindValue(':local_address', $localIp, SQLITE3_TEXT);
            $stmt->bindValue(':remote_address', $remoteIp, SQLITE3_TEXT);
            $stmt->bindValue(':local_hostname', $localHostname, SQLITE3_TEXT);
            $stmt->bindValue(':remote_hostname', $remoteHostname, SQLITE3_TEXT);
            $stmt->bindValue(':local_port', $localPort, SQLITE3_INTEGER);
            $stmt->bindValue(':remote_port', $remotePort, SQLITE3_INTEGER);
            $stmt->bindValue(':pid', $pid, SQLITE3_INTEGER);
            $stmt->bindValue(':pcmd', $pname, SQLITE3_TEXT);
            $stmt->bindValue(':pname', @$t[0], SQLITE3_TEXT);
            $stmt->bindValue(':ts', time(), SQLITE3_INTEGER);
            $stmt->bindValue(':d2', date("Ymd"), SQLITE3_INTEGER);
            $stmt->bindValue(':host', $hn, SQLITE3_TEXT);
    	    $stmt->bindValue(':tcpstatus', $tcpstatus, SQLITE3_TEXT);
            $stmt->execute();
        }
        
        fclose($handle);
    }
}


function getProcessName($pid) {
        $processName = 'unknown';
        $procFile = "/proc/$pid/cmdline";
        if (file_exists($procFile)) {
            $processName = trim(file_get_contents($procFile));
        }
        return $processName;
    }


function parseTcpState(string $hexStatus): string {
    $hexStatus = strtoupper($hexStatus);

    // Таблица соответствия из ядра Linux (include/net/tcp_states.h)
    static $states = [
        '01' => 'ESTABLISHED',
        '02' => 'SYN_SENT',
        '03' => 'SYN_RECV',
        '04' => 'FIN_WAIT1',
        '05' => 'FIN_WAIT2',
        '06' => 'TIME_WAIT',
        '07' => 'CLOSE',
        '08' => 'CLOSE_WAIT',
        '09' => 'LAST_ACK',
        '0A' => 'LISTEN',
        '0B' => 'CLOSING',
        '0C' => 'NEW_SYN_RECV',
    ];

    return $states[$hexStatus] ?? 'UNKNOWN';
}

function findPidByInode($inode) {
    $procDir = '/proc';

    foreach (scandir($procDir) as $pid) {
        if (!ctype_digit($pid)) {
            continue;
        }

        $fdDir = "$procDir/$pid/fd";
        if (!is_dir($fdDir)) {
            continue;
        }

        $a=@scandir($fdDir);
        if(is_array($a)==false)
        	continue;
        	
        foreach ($a as $fd) {
            if ($fd === '.' || $fd === '..') {
                continue;
            }

            $link = @readlink("$fdDir/$fd");
            if ($link === false) {
                continue;
            }

            if (strpos($link, "socket:[$inode]") !== false) {
                return (int)$pid;
            }
        }
    }

    return null;
}


while (true) {
    parseTcpFile($db);
    sleep(1); 
}

?>
