<?php
date_default_timezone_set('Europe/Moscow');

if (@isset($_GET["data"])) {

    class MyDB extends SQLite3
    {
        function __construct()
        {
            $this->open('/var/lib/tcpmon/tcp_connections.db', SQLITE3_OPEN_READONLY);
        }
    }

    class MyDB2 extends SQLite3
    {
        function __construct()
        {
            $this->open('/var/lib/tcpmon/dns_connections.db', SQLITE3_OPEN_READONLY);
        }
    }

    echo date("Ymd H:i:s") . " , dns db size: " . (fs("/var/lib/tcpmon/dns_connections.db")) . " , tcp db size: " . (fs("/var/lib/tcpmon/tcp_connections.db")) . " <br>";

    echo "<table width=100%><tr><td width=50% valign=top><b>TCP</b><hr size=1>";

    $db = new MyDB();
    $result = $db->query('SELECT ts, * FROM tcp_newconnections ORDER BY ts DESC LIMIT 0,50');
    echo "<table id='tcpTable'>";
    while ($a = $result->fetchArray()) {
        echo "<tr onclick='toggleRow(this)'><td><nobr><small>" . date("Ymd H:i", $a["ts"]) . "</small></td><td>" . $a["pname"] . "</td><td>" . $a["remote_hostname"] . "</td><td>" . $a["remote_address"] . "</td></tr>";
    }
    echo "</table>";

    echo "</td><td valign=top><b>DNS</b><hr size=1>";

    $db = new MyDB2();
    $result = $db->query('SELECT ts, * FROM dns_monitor ORDER BY ts DESC LIMIT 0,50');
    echo "<table id='dnsTable'>";
    while ($a = $result->fetchArray()) {
        echo "<tr onclick='toggleRow(this)'><td><nobr><small>" . date("Ymd H:i", $a["ts"]) . "</small></td><td>" . $a["remote_address"] . "</td><td>" . $a["type1"] . "</td><td>" . $a["type2"] . "</td><td>" . $a["data"] . "</td></tr>";
    }
    echo "</table>";

    echo "</td></tr></table>";

    return;
}

if (isset($_POST['save'])) {
    $selectedRows = $_POST['selectedRows'];
    $filename = 'selected_data_' . date('Ymd_His') . '.txt';
    file_put_contents($filename, $selectedRows);
    echo "Данные успешно сохранены в файл: <a href='$filename'>$filename</a>";
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon</title>
    <style>
        .selected {
            background-color: #d0e0f0;
        }
    </style>
</head>

<body bgcolor=lightgrey style='font-family:sans-serif'>
<div id="data"></div>
<button onclick="saveSelected()">Сохранить выделенные строки</button>

<script>
    let selectedRows = [];

    function toggleRow(row) {
        const rowIndex = selectedRows.indexOf(row);
        if (rowIndex > -1) {
            selectedRows.splice(rowIndex, 1);
            row.classList.remove('selected');
        } else {
            selectedRows.push(row);
            row.classList.add('selected');
        }
    }

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

    function displayData(data) {
        const dataDiv = document.getElementById('data');
        dataDiv.innerHTML = data;
    }

    function saveSelected() {
        const rowsData = selectedRows.map(row => Array.from(row.children).map(cell => cell.innerText).join('\t')).join('\n');
        if (rowsData) {
            const formData = new FormData();
            formData.append('save', true);
            formData.append('selectedRows', rowsData);

            fetch('http://localhost/mon.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                alert(text);
                selectedRows = [];
                document.querySelectorAll('.selected').forEach(r => r.classList.remove('selected'));
            })
            .catch(error => console.error('Ошибка при сохранении данных:', error));
        } else {
            alert('Нет выделенных строк для сохранения.');
        }
    }

    setInterval(fetchData, 5000); // Получаем данные каждые 5 секунд
    fetchData();
</script>
</body>
</html>

<?php
function fs($path)
{
    $size = filesize($path);
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}
?>
