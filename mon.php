<?php


if( @isset($_GET["data"]) ){

class MyDB extends SQLite3
{
    function __construct()
    {
        $this->open('/var/lib/tcpmon/tcp_connections.db',SQLITE3_OPEN_READONLY);
    }
}

class MyDB2 extends SQLite3
{
    function __construct()
    {
        $this->open('/var/lib/tcpmon/dns_connections.db',SQLITE3_OPEN_READONLY);
    }
}

echo date("Ymd H:i:s")." , dns db size: ".(fs("/var/lib/tcpmon/dns_connections.db"))." , tcp db size: ".(fs("/var/lib/tcpmon/tcp_connections.db"))." <br>";

echo "<table width=100%><tr><td width=50% valign=top><b>TCP</B><hr size=1>";

$db = new MyDB();
//$db->exec('CREATE TABLE foo (bar STRING)');
//$db->exec("INSERT INTO foo (bar) VALUES ('This is a test')");
$result = $db->query('SELECT ts, * FROM tcp_newconnections order by ts desc limit 0,50');
echo "<table>";
while($a=$result->fetchArray()){
    
    echo "<tr><td><nobr><small>".date("Ymd H:i",$a["ts"]+3*60*60)."</small></td><td>".$a["pname"]."</td><td>".$a["remote_hostname"]."</td><td>".$a["remote_address"]."</td></tr>";
}
echo "</table>";

echo "</td><td valign=top><b>DNS</B><hr size=1>";

$db = new MyDB2();
$result = $db->query('SELECT ts, * FROM dns_monitor order by ts desc limit 0,50');
echo "<table>";
while($a=$result->fetchArray()){
    echo "<tr><td><nobr><small>".date("Ymd H:i",$a["ts"]+3*60*60)."</small></td><td>".$a["remote_address"]."</td><td>".$a["type1"]."</td><td>".$a["type2"]."</td><td>".$a["data"]."</td></tr>";
}
echo "</table>";

echo "</td></tr></table>";

return;
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mon</title></head>

<body  bgcolor=lightgrey style='font-family:sans-serif'>
<div id="data" ></div>
<script>
        // Функция для получения данных с сервера
        async function fetchData() {
            try {
                const response = await fetch('http://localhost/mon.php?data=1'); // Замените на Ваш URL
                if (!response.ok) {
                    throw new Error('Сеть ответила с ошибкой');
                }
                const data = await response.text();
                displayData(data);
            } catch (error) {
                console.error('Ошибка при получении данных:', error);
            }
        }

        // Функция для отображения данных на странице
        function displayData(data) {
            const dataDiv = document.getElementById('data');
            dataDiv.innerHTML = data;
        }

        // Устанавливаем интервал для периодического получения данных
        setInterval(fetchData, 5000); // Получаем данные каждые 5 секунд

        // Начальное получение данных
        fetchData();
    </script>
</body></html>
<?php
function fs($path)
{
    $size = filesize($path);
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}
?>
