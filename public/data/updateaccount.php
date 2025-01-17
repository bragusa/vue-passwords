<?php require 'dbconnection.php';?><?php require 'authentication.php';?><?php

$table_name = 'accounts';

$id = $_POST['id'];
$sitename = $_POST['sitename'];
$siteurl = $_POST['siteurl'];
$siteusername = $_POST['site_aaa'];
$sitepassword = $_POST['site_bbb'];

//make sure we have all the data that we require
if(!isset($table_name) || !isset($username) || !isset($id) || !isset($sitename) ||  !isset($siteurl) ||  !isset($siteusername) || !isset($sitepassword)){
  print json_encode([ 'status' => 'failed', 'reason' => 'Could not update. Must supply all column values. table_name ' . $table_name . ' username ' . $username . ' id ' . $id . ' sitename ' . $sitename . ' siteurl ' . $siteurl . ' siteusername ' . $siteusername . ' sitepasssword ' . $sitepassword]);
  die('');
}

function fetch($conn, $table_name, $username, $id, $sitename, $siteurl, $siteusername, $sitepassword ){

  $sql = 'UPDATE ' . $table_name . ' 
        SET 
            sitename = \'' . $sitename . '\', 
            siteurl = \'' . $siteurl . '\', 
            siteusername = \'' . $siteusername . '\', 
            sitepassword = \'' . $sitepassword . '\' 
        WHERE id = \'' . $id . '\'';  


//  error_log('SQL is ' . $sql);

  try{
    $retval = mysqli_query($conn, $sql);
  }
  catch(Exception $e){
    die('');
  }

  if(!$retval ) {
    die('Could update: ' . mysqli_error($conn));
  }


  $sql = "SELECT * FROM " . $table_name . " WHERE id = '" . $id . "'";

  try{
    $retval = mysqli_query($conn, $sql);
  }
  catch(Exception $e){

    die('');
  }

  if(! $retval ) {
    die('Could not get data: ' . mysqli_error($conn));
  }

  $response = array();

  while ($row = mysqli_fetch_array($retval, MYSQLI_ASSOC)) { //<-----------change this
    $response[] = $row;
  }

  return $response;

}


$response =  fetch($conn, $table_name, $username, $id, $sitename, $siteurl, $siteusername, $sitepassword);
    
print json_encode($response);

mysqli_close($conn);

?>