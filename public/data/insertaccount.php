<?php require 'dbconnection.php';?><?php require 'authentication.php';?><?php

$table_name = 'accounts';

//$id = $_POST['id'];
$sitename = $_POST['sitename'];
$siteurl = $_POST['siteurl'];
$siteusername = $_POST['site_aaa'];
$sitepassword = $_POST['site_bbb'];

//make sure we have all the data that we require
if(!isset($table_name) || !isset($username) || !isset($sitename) ||  !isset($siteurl) ||  !isset($siteusername) || !isset($sitepassword)){
  print json_encode([ 'status' => 'failed', 'reason' => 'Could not update. Must supply all column values. table_name ' . $table_name . ' username ' . $username . ' id ' . $id . ' sitename ' . $sitename . ' siteurl ' . $siteurl . ' siteusername ' . $siteusername . ' sitepasssword ' . $sitepassword]);
  die('');
}

function fetch($conn, $dbname, $dbuser, $dbpass, $table_name, $username, $id, $sitename, $siteurl, $siteusername, $sitepassword ){

  // $sql = 'UPDATE ' . $table_name . ' 
  //       SET 
  //           sitename = \'' . $sitename . '\', 
  //           siteurl = \'' . $siteurl . '\', 
  //           siteusername = \'' . $siteusername . '\', 
  //           sitepassword = \'' . $sitepassword . '\' 
  //       WHERE id = \'' . $id . '\'';  
//   $dbhost = 'localhost';
//   $dbuser = 'southsho_admin';
//   $dbpass = 'SouthShoreWeb_Admin';
//   $dbname = 'southsho_information';
//



  try {
    $pdo = new PDO('mysql:host=localhost;dbname=' . $dbname, $dbuser, $dbpass);
    // Set error mode to exception to catch any connection issues
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;  // Stop further execution if the connection fails
  }


  $sql = 'INSERT INTO ' . $table_name . ' (sitename, siteurl, siteusername, sitepassword, username)  VALUES (:sitename, :url, :siteusername, :password, :username)';
  $stmt = $pdo->prepare($sql);

  $stmt->execute([
    ':siteusername' => $siteusername,
    ':password' => $sitepassword,
    ':url' => $siteurl,
    ':sitename' => $sitename,
    ':username' => $username
  ]);

  // $sql = 'INSERT INTO ' . $table_name . ' 
  // (sitename, siteurl, siteusername, sitepassword) 
  // VALUES 
  // (\'' . $sitename . '\', 
  //  \'' . $siteurl . '\', 
  //  \'' . $siteusername . '\', 
  //  \'' . $sitepassword . '\')';


  if ($stmt->rowCount() > 0) {
    $lastInsertId = $pdo->lastInsertId();

    if(!empty($lastInsertId)){

      $sql = 'SELECT * FROM ' . $table_name . ' WHERE id = \'' . $lastInsertId . '\'';
  
  
  
      try{
        $retval = mysqli_query($conn, $sql);
      }
      catch(Exception $e){
  
        die('');
      }
  
      if(!$retval ) {
        die('Could not get data: ' . mysqli_error($conn));
      }
  
      $response = array();
  
      while ($row = mysqli_fetch_array($retval, MYSQLI_ASSOC)) { //<-----------change this
        $response[] = $row;
      }
  
      return $response;
    }

  }
  


}


$response =  fetch($conn, $dbname, $dbuser, $dbpass, $table_name, $username, $id, $sitename, $siteurl, $siteusername, $sitepassword);
    
print json_encode($response);

mysqli_close($conn);

?>