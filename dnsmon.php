#!/usr/bin/php
<?php

// Команда для выполнения
$command = 'tcpdump -i any -n -s 1500 -l port 53'; // -l для линейного вывода

// Открываем процесс
$descriptorspec = [
    0 => ["pipe", "r"], // stdin
    1 => ["pipe", "w"], // stdout
    2 => ["pipe", "w"]  // stderr
];

if(@$argv[1]=="install"){
    exec("cp ./dnsmon.php /usr/bin/");
    file_put_contents("/etc/systemd/system/monitordns.service",
"[Unit]
Description=monitordns
After=network.target
[Service]
User=root
Restart=on-failure
ExecStart=/usr/bin/dnsmon.php > /var/lib/tcpmon/logdns

[Install]
WantedBy=network.target
");

    exec("systemctl stop monitordns");
    exec("systemctl daemon-reload");
    exec("systemctl enable monitordns");
    exec("systemctl start monitordns");
    return;
}

$sessionstart=date("YmdHi");

$dir="/var/lib/tcpmon";
@mkdir($dir);
$dbFile = $dir.'/dns_connections.db';

$db = new SQLite3($dbFile);

$hn=gethostname();
$previps=array();

$db->exec("CREATE TABLE IF NOT EXISTS dns_monitor (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    local_address TEXT,
    remote_address TEXT,
    direction TEXT,
    type1 TEXT,
    seqid integer,
    type2 TEXT,
    data TEXT,
    ts integer,
    d2 integer,
    sessionstart integer,
    raw TEXT
)");



$process = proc_open($command, $descriptorspec, $pipes);

if (is_resource($process)) {
    fclose($pipes[0]);

    while ($line = fgets($pipes[1])) {
        //echo "Получено: " . $line;
	$line=str_replace('  ',' ',$line);
	$a=explode(" ",$line);
//	if(!preg_match('/(\d{2}:\d{2}:\d{2}\.\d+)\s+\S+\s+(Out|In)\s+IP\s+(\d+\.\d+\.\d+\.\d+)\.(\d+)\s+>\s+(\d+\.\d+\.\d+\.\d+)\.(\w+):\s+(\d+)\+\s+(\w+)\?\s+(.+?)\s+\((\d+)\)/', $line, $a))
//	    continue;
	print_r($a);
	if(count($a)<5){
	    echo "! ".$line."\n";
	    continue;
	}




    
if($a[2]=="Out"){
/*
Array
(
    [0] => 13:14:11.231433
    [1] => tun0
    [2] => Out
    [3] => IP
    [4] => 10.8.0.9.56974
    [5] => >
    [6] => 8.8.8.8.domain:
    [7] => 46069+
    [8] => A?
    [9] => encrypted-tbn0.gstatic.com.
    [10] => (44)
)
debian
[0] => 19:46:57.243552
    [1] => enp1s0
    [2] => Out
    [3] => IP
    [4] => 192.168.1.61.48964
    [5] => >
    [6] => 8.8.8.8.53:
    [7] => 54096+
    [8] => A?
    [9] => mail.ru.
    [10] => (25)

*/

	    $ip1=eIP($a[4]);
	    $ip2=eIP($a[6]);

	    if($a[2]=="Out"){
		echo date("Ymd H:i")."\t".$a[9]."\t".$a[10]."\n";
	    }
	    
    	    $stmt = $db->prepare("INSERT INTO dns_monitor (local_address, remote_address, direction, type1, seqid, type2, data, ts,d2, sessionstart, raw) VALUES 
    	    (:local_address, :remote_address, :direction, :type1, :seqid, :type2, :data, :ts, :d2, :sessionstart, :raw)");
            $stmt->bindValue(':local_address', $ip1, SQLITE3_TEXT);
            $stmt->bindValue(':remote_address', $ip2, SQLITE3_TEXT);
            
            $stmt->bindValue(':direction', $a[2], SQLITE3_TEXT);
            $stmt->bindValue(':type1', $a[3], SQLITE3_TEXT);
            $stmt->bindValue(':type2', $a[8], SQLITE3_TEXT);
            
            $stmt->bindValue(':sessionstart', $sessionstart, SQLITE3_INTEGER);
            $stmt->bindValue(':seqid', str_replace("+","",$a[7]), SQLITE3_INTEGER);
            $stmt->bindValue(':data', $a[9], SQLITE3_TEXT);
            $stmt->bindValue(':ts', time(), SQLITE3_INTEGER);
            $stmt->bindValue(':d2', date("Ymd"), SQLITE3_INTEGER);
            $stmt->bindValue(':raw', $line, SQLITE3_TEXT);
	    $stmt->execute();	
}

if($a[2]=="In"){

/*
Array
(
    [0] => 13:14:11.249026
    [1] => tun0
    [2] => In
    [3] => IP
    [4] => 8.8.8.8.domain
    [5] => >
    [6] => 10.8.0.9.51598:
    [7] => 61559
    [8] => 6/0/0
    [9] => A
    [10] => 173.194.222.138,
    [11] => A
    [12] => 173.194.222.101,
    [13] => A
    [14] => 173.194.222.102,
    [15] => A
    [16] => 173.194.222.139,
    [17] => A
    [18] => 173.194.222.113,
    [19] => A
    [20] => 173.194.222.100
    [21] => (140)

)
*/
$a=explode(" ",$line,10);
	    $ip1=eIP($a[4]);
	    $ip2=eIP($a[6]);

	    if($a[3]=="In"){
		echo date("Ymd H:i")."\t".@$a[6]."\t".@$a[9]."\n";
	    }
    	    $stmt = $db->prepare("INSERT INTO dns_monitor (local_address, remote_address, direction, type1, seqid, type2, data, ts,d2, sessionstart, raw) VALUES 
    	    (:local_address, :remote_address, :direction, :type1, :seqid, :type2, :data, :ts, :d2, :sessionstart, :raw)");
            $stmt->bindValue(':local_address', $ip1, SQLITE3_TEXT);
            $stmt->bindValue(':remote_address', $ip2, SQLITE3_TEXT);
            
            $stmt->bindValue(':direction', $a[2], SQLITE3_TEXT);
            $stmt->bindValue(':type1', $a[3], SQLITE3_TEXT);
            $stmt->bindValue(':type2', $a[9], SQLITE3_TEXT);
            
            $stmt->bindValue(':sessionstart', $sessionstart, SQLITE3_INTEGER);
            $stmt->bindValue(':seqid', str_replace("+","",$a[7]), SQLITE3_INTEGER);
            $stmt->bindValue(':data', @$a[10], SQLITE3_TEXT);
            $stmt->bindValue(':ts', time(), SQLITE3_INTEGER);
            $stmt->bindValue(':d2', date("Ymd"), SQLITE3_INTEGER);
	    $stmt->bindValue(':raw', $line, SQLITE3_TEXT);
	    $stmt->execute();	
}



    }
    fclose($pipes[1]);
    fclose($pipes[2]);

    proc_close($process);
} else {
    echo "Не удалось запустить процесс.";
}


function eIP($input) {
    $pattern = '/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/';
    
    if (preg_match($pattern, $input, $matches)) {
        return $matches[1]; // Возвращаем найденный IP-адрес
    }
    
    return ""; // Если IP-адрес не найден, возвращаем null
}
?>