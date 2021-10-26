<?php 
  require_once("conn_hw9.php");

  function getUserFromUsername($username){
  	global $conn;
  	$sql = sprintf(
  		"select * from Anna3143_users where username = '%s'",
  		$username
  	);
  	$result = $conn->query($sql);
  	$row = $result->fetch_assoc();
  	return $row;
  }

  function escape($str) {
  	return htmlspecialchars($str, ENT_QUOTES);
  }
 
 function hasPermission($user, $action, $comment){
  if($user["role"] === "ADMIN"){
    return true;
  }
 
  
 if ($user["role"] === "NORMAL") {
    if($action ==='create') return true;
    return $comment["username"] === $user["username"];
      }
  }

  if ($user["role"] === 'BANNED') {
    return $action !== 'create';
  }


  function isAdmin($user){
    return $user["role"] === 'ADMIN';
  }
?>