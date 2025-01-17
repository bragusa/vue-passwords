<?php require 'utility.php';?><?php

  $invalid = 'Please select a different username.';

  $username = $_POST['username'];

  $sql = 'SELECT * FROM users where username = \'' . $username . '\'';
  
  ?><?php require '_connectioninfo.php';?><?php

  try {
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
  }
  catch(Exception $e){
    failure('Could not connect: ' . mysqli_error($conn));
  }
 
  if(!$conn ) {
    failure('Could not connect');
  }

  $result = mysqli_query($conn, $sql);
  
  if(mysqli_num_rows($result)<1){
    success('unique username');
  }
  else{
    failure($invalid);
  }

  mysqli_close($conn);
?>