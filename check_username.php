<?php
require_once 'includes/database_config.php';

if (!isset($_GET["q"])) {
    echo "Errore";
    exit;
}
header('Content-Type: application/json');


$conn = mysqli_connect($database_config['host'], $database_config['username'], $database_config['password'], $database_config['database']) or die(mysqli_error($conn));

$username = mysqli_real_escape_string($conn, $_GET["q"]);

$query = "SELECT username FROM utenti WHERE username = '".$username."'";

$res = mysqli_query($conn, $query) or die(mysqli_error($conn));

if(mysqli_num_rows($res) > 0) {
    echo json_encode(array('exists' => true));
} else {
    echo json_encode(array('exists' => false));
}
mysqli_close($conn);


?>