<?php
include 'config.php';


$connection =  mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
//EX: http://localhost:8081/myserver/download.php?rAppGroup=RAP1&downloadKey=downloadkey2
mysqli_set_charset($connection,'utf-8');
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
} else {
 print ("Connected successfully<br>");
}

if (array_key_exists("board", $_GET))
	$board = $_GET["board"]; 
else 
	echo "board missing from params. downloading default file for given rapp<br>";

if (array_key_exists("downloadKey", $_GET)) {
	$downloadKey = $_GET["downloadKey"];
} else {
	echo "downloadKey missing from params<br>";
}

if (array_key_exists("rAppGroup", $_GET)) {
	$rAppGroup = $_GET["rAppGroup"];
} else {
	echo "rAppGroup missing from params<br>";
}

echo $board . "<br>";
echo $downloadKey . "<br>";
echo $rAppGroup . "<br>";

if (!array_key_exists("board", $_GET)) {
	$query = "SELECT res.DATA FROM resources res, rappgroups r where 
	res.RAPPGROUP_ID = r.ID AND r.NAME = ? AND res.BOARD_ID IS NULL";
	$stmt = $connection->prepare($query);
	$stmt->bind_param("s", $rAppGroup);

} else {
	$query = "SELECT res.DATA FROM boards b, rappgroups r, resources res 
				WHERE b.NAME =? AND 
				b.DOWNLOAD_KEY =? AND 
				r.NAME =? 
				AND b.ID = res.BOARD_ID AND r.ID = res.RAPPGROUP_ID";
	$stmt = $connection->prepare($query);
	$stmt->bind_param("sss", $board, $downloadKey, $rAppGroup);
}

	
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($data);
	$stmt->fetch();


if (!isset($data)) {
	mysqli_close($connection);
	die('Invalid parameters. Nothing to download.');
} else {
	ob_clean();
	//header("Content-Disposition: attachment; filename=file.png");
	echo $data;
}
function getSignature() {
    return $serverSignature;
}
mysqli_close($connection);
?>