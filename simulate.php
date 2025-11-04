<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "bluemetric";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

while (true) {
    $ph = mt_rand(60, 90) / 10;
    $temperature = mt_rand(200, 320) / 10;
    $turbidity = mt_rand(10, 150) / 10;

    $sql = "INSERT INTO sensor_readings (ph, temperature, turbidity) VALUES ($ph, $temperature, $turbidity)";
    $conn->query($sql);

    echo "Inserted — pH: $ph | Temp: $temperature | Turbidity: $turbidity (" . date('H:i:s') . ")<br>";
    ob_flush();
    flush();
    sleep(5); // 5-second interval
}
$conn->close();
?>
