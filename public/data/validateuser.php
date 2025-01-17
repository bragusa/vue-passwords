<?php require 'dbconnection.php';?><?php

$user_key = $_GET['key'];


//make sure we have all the data that we require
if(!isset($user_key)){
  $message = 'Could not Validate user';
}
else {
  $sql = 'SELECT * FROM users WHERE validation = \'' . $user_key . '\'';

  try{
    $result = mysqli_query($conn, $sql);

    $row_count = $result->num_rows;

    if($row_count == 1){
      
      $sql = 'UPDATE users SET validation = \'validated\' WHERE validation = \'' . $user_key . '\'';

      $result = mysqli_query($conn, $sql);

      if($result){
        $message = 'User Validated';
        header("Location: https://southshoreweb.com/secure");
        exit();
      }
      else {
        $message = 'Could not Validate user';
      }

    }
    else {
      $message = 'Could not Validate user';
    }

  }
  catch(Exception $e){
    
  }


}




?>