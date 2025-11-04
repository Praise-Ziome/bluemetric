<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "bluemetric"; // adjust if needed

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$query = "SELECT ph, temperature, turbidity, timestamp 
          FROM sensor_readings 
          ORDER BY timestamp DESC 
          LIMIT 20"; // latest 20 records
$result = $conn->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// reverse to show oldest first (chronological)
echo json_encode(array_reverse($data));

$conn->close();
?>
