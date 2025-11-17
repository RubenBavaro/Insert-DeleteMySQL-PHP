<?php
$servername = 'db';
$username = 'myuser';
$password = 'mypassword';
$database = 'myapp_db';

echo $servername . "<br />";
echo $username . "<br />";
echo $password . "<br />";
echo $database . "<br />";

$conn = new mysqli($servername, $username, $password, $database);

if($conn->connect_error){
    die("Connesione fallita :" . $conn->connect_error);
}

echo "<h1>Connesione riuscita a MySQL!</h1>";

$result = $conn->query("Show TABLES;");
echo "<pre>";
while ($row = $result->fetch_array()){
    print_r($row);
}
echo "</pre>";
$conn->close();

?>