<?php

include 'config.php';

$connection =  mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
mysqli_set_charset($connection,'utf-8');



if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
} else {
 //print ("Connected successfully\n");
}



if (array_key_exists("uploadKey", $_GET)) {
    $upKey = $_GET["uploadKey"];
} else {
    echo "rAppGroup missing from params<br>";
}

if (array_key_exists("rAppGroup", $_GET)) {
    $rAppGroup = $_GET["rAppGroup"];
} else {
    echo "rAppGroup missing from params<br>";
}

if (array_key_exists("board", $_GET))
    $board = $_GET["board"]; 
else { 
    echo "board missing from params. uploading with NULL board\n";
    $board = "NULL";
}
if (array_key_exists("downloadKey", $_GET)) {
    $downloadKey = $_GET["downloadKey"];
} else {
    echo "downloadKey missing from params<br>";
}


// check if database structure exist. if rappgropus dont exist, nothing exist.
$val = mysqli_query($connection, "SHOW TABLES LIKE 'rappgroups'");
if ($val->num_rows == 0) {
    echo "database empty. building it...\n";

    $sqlSource = file_get_contents('mydb.sql');    
    $i = 0;
    if (mysqli_multi_query($connection,$sqlSource)) {
        do {
          $i++;
          echo "Running query number $i\n";
        } while (mysqli_next_result($connection));
    }

    echo "database building complete.\n";
} 

//Insert in rappgroups, for rappgroup name given
$stmt = $connection->prepare("INSERT INTO rappgroups (NAME) VALUES(?)");
$stmt->bind_param("s", $rAppGroup);
$stmt->execute();

$rapId = $connection->insert_id;
if ($rapId == 0) {
    $stmt = $connection->prepare("SELECT ID FROM rappgroups WHERE NAME =?");
    $stmt->bind_param("s", $rAppGroup);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($rapId);
    $stmt->fetch();
}
//Insert board, for board name given
if ($board != "NULL") {
    $stmt = $connection->prepare("INSERT INTO boards (NAME, RAPPGROUP_ID, DOWNLOAD_KEY) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $board, $rapId, $downloadKey);
    $stmt->execute();

    $boardId = $connection->insert_id;
    if ($boardId == 0) {
        $stmt = $connection->prepare("UPDATE boards SET DOWNLOAD_KEY= ? WHERE NAME= ? AND RAPPGROUP_ID = ?");
        $stmt->bind_param("ssi", $downloadKey, $board, $rapId);
        $stmt->execute();

        $stmt = $connection->prepare("SELECT ID from boards WHERE NAME= ? AND RAPPGROUP_ID = ?");
        $stmt->bind_param("si", $board, $rapId);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($boardId);
        $stmt->fetch();
    }
} else {
    $boardId = NULL;
}

// Insert in resources 
if ($serverUploadKey == $upKey) {
    $imgData = file_get_contents($_FILES['userImage']['tmp_name']);
    // find id of NULL board if this operation is on it.
    if ($boardId == NULL) {
        $stmt = $connection->prepare("SELECT ID FROM resources WHERE BOARD_ID is NULL AND RAPPGROUP_ID = ?");
        $stmt->bind_param("i", $rapId);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($checkNull);
        $stmt->fetch();
    }
    // check if insert is needed
    if ($boardId != NULL || ( !is_numeric($checkNull) && $boardId == NULL )) {
        $stmt = $connection->prepare("INSERT INTO resources (BOARD_ID, RAPPGROUP_ID, DATA) VALUES (?, ?, ?)");
        $stmt->bind_param("iib", $boardId, $rapId, $imgData);
        $stmt->send_long_data(2, $imgData);
        $stmt->execute();
    }
    $resId = $connection->insert_id; 
    if ($resId == 0) {
        // trying to update entry with board_id = NULL
        if ($boardId == NULL && is_numeric($checkNull)) {
            $stmt = $connection->prepare("UPDATE resources SET DATA= ? WHERE BOARD_ID is NULL AND RAPPGROUP_ID = ?");
            $stmt->bind_param("bi",$imgData, $rapId);
        }
        else { // trying to update entry with board_id != NULL
            $stmt = $connection->prepare("UPDATE resources SET DATA= ? WHERE BOARD_ID = ? AND RAPPGROUP_ID = ?");
            $stmt->bind_param("bii",$imgData, $boardId, $rapId);
        }
        $stmt->send_long_data(0, $imgData);
        $stmt->execute();
    }
} else {
    mysqli_close($connection);
    echo "Wrong upload key. exiting";
}
mysqli_close($connection);

?>