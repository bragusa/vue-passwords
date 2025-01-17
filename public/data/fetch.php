<?php require 'dbconnection.php';?><?php require 'authentication.php';?><?php

$table_name = $_GET['tablename'];
$order_by = $_GET['orderby'];
$columns = $_GET['columns'];
$offset = $_GET['offset'];
$pagesize = $_GET['pagesize'];
$search_string = $_GET['searchterm'];

// if(!isset($username)){ //for debugging if https is not used
//   $username = 'bragusa';
// }
//make sure we have all the data that we require
if(!isset($table_name) || !isset($username)){
  print json_encode([ 'status' => 'failed', 'reason' => 'Could not get data. Must supply tablename.']);
  die('');
}



function fetch($conn, $table_name, $username, $pagesize, $offset, $search_string, $order_by){

//  error_log(' in fetch fetching data for ' . $username . ' from table ' . $table_name);

  if($table_name==='users'){
    $sql = 'SELECT username FROM ' . $table_name . ' WHERE username = \'' . $username . '\'';
  }
  else {
    $sql = 'SELECT * FROM ' . $table_name . ' WHERE username = \'' . $username . '\'';
  }

  

  if(!empty($search_string)){
    $sql .= ' AND sitename like \'%' . $search_string . '%\'';
  }

  if(!empty($order_by)){
    $sql .= ' ORDER BY ' . $order_by;
  }

  if(isset($pagesize) && isset($offset)){
    $sql .= ' LIMIT ' . $pagesize . ' OFFSET ' . $offset;
  }

  //error_log($sql);

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

$response =  fetch($conn, $table_name, $username, $pagesize, $offset, $search_string, $order_by);
    
print json_encode($response);

mysqli_close($conn);

?>