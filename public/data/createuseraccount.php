<?php require 'dbconnection.php';?><?php

$table_name = 'users';

$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];

//make sure we have all the data that we require
if(!isset($table_name) || !isset($username) || !isset($password) || !isset($email) ){
  print json_encode([ 'status' => 'failed', 'reason' => 'Could not update. Must supply all values. ']);
  die('');
}

function fetch($conn, $dbname, $dbuser, $dbpass, $table_name, $username, $password ){

  $sql = 'INSERT into ' . $table_name . ' (username, password, validation) VALUES (:username, :password, :validation)';

  try {
    $pdo = new PDO('mysql:host=localhost;dbname=' . $dbname, $dbuser, $dbpass);
    // Set error mode to exception to catch any connection issues
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;  // Stop further execution if the connection fails
  }


  //$sql = 'INSERT INTO ' . $table_name . ' (sitename, siteurl, siteusername, sitepassword, username)  VALUES (:sitename, :url, :siteusername, :password, :username)';
  $stmt = $pdo->prepare($sql);
  $validation = base64_encode($username . $password);

  $stmt->execute([
    ':username' => $username,
    ':password' => $password,
    'validation' => $validation
  ]);

  if ($stmt->rowCount() > 0) {

    $sql = 'SELECT * FROM ' . $table_name . ' WHERE username = \'' . $username . '\'';
    
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


  //   $email = 'bragusa@southshoreweb.com';
  //   $link = 'https://southshoreweb.com/secure/data/validateuser.php';
  //   $subject = 'Secure Password - new user';
  //   $message = 'Welcome to Secure Password. Please use the following link to validate your email address.' . "<br><br><a href='" . $link . "?key=" . $validation ."'>Validate New User</a>";
  //   $headers =  'From: noreply@southshoreweb.com'       . "\r\n" .
  //               'Reply-To: noreply@southshoreweb.com' . "\r\n" .
  //               'Content-Type: text/html; charset=UTF-8' . "\r\n" . 
  //               'X-Mailer: PHP/' . phpversion();
  
  // // Send
  //  mail($email, $subject, $message, $headers);

    return $validation;
  }
}


$validation = fetch($conn, $dbname, $dbuser, $dbpass, $table_name, $username, $password, $email);

mysqli_close($conn);

$link = 'https://southshoreweb.com/secure/data/validateuser.php';

$subject = 'Secure Password - new user';
//$message = 'Welcome to Secure Password. Please use the following link to validate your email address. \r\n' . $link . '?key=' . $validation;
$message = 'Welcome to Secure Password. Please use the following link to validate your email address.' . "<br><br><a href='" . $link . "?key=" . $validation ."'>Validate New User</a>";
$headers =  'From: no-reply@southshoreweb.com'       . "\r\n" .
            'Reply-To: no-reply@southshoreweb.com' . "\r\n" .
            'Content-Type: text/html; charset=UTF-8' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

$mailed = mail($email, $subject, $message, $headers);

// error_log("mailed=$mailed");

print json_encode([ 'status' => 'success', 'result' => 'New user ' . $username . ' created successfully']);


// $link = 'https://southshoreweb.com/secure/data/validateuser.php';

//  // The message
//  //$message = "This is our test message with no-reply and headers";
//  $message = 'Welcome to Secure Password. Please use the following link to validate your email address. \r\n' . $link . '?key=' . $validation;
//  $subject = 'Secure Password - new user';
//  $headers = 'From: no-reply@southshoreweb.com' . "\r\n" .
//  'Reply-To: no-reply@southshoreweb.com' . "\r\n" .
//  'X-Mailer: PHP/' . phpversion();

// // Send
//  mail($email, $subject, $message, $headers);
 //echo($message);




?>