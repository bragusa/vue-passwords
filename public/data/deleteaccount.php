<?php require 'dbconnection.php';?><?php require 'authentication.php';?><?php

$table_name = $_POST['tablename'];
$id = $_POST['id'];

//make sure we have all the data that we require
if(!isset($table_name) || !isset($username) || !isset($id)){
  print json_encode([ 'status' => 'failed', 'reason' => 'Could not get data. Must supply all ' . $table_name .' required parameters.']);
  die('');
}

function fetch($conn, $table_name, $username, $id){

  $sql = 'DELETE FROM ' . $table_name . ' WHERE username = \'' . $username . '\' AND id = \'' . $id . '\'';


  try{
    $retval = mysqli_query($conn, $sql);
  }
  catch(Exception $e){
    die('');
  }

  return $response;
}


$response =  fetch($conn, $table_name, $username, $id);
  
success('row has been deleted');
//print json_encode($response);

mysqli_close($conn);

?>