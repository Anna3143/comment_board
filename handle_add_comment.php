<?php
  session_start();
  require_once('conn_hw9.php');
  require_once('utils.php');

  if (
    empty($_POST['content'])
  ) {
    header('Location: index_hw9.php?errCode=1');
    die('資料不齊全');
  }

  $username = $_SESSION['username'];
  $user = getUserFromUsername($username);

  if (!hasPermission($user, 'create', NULL)) {
    header("Location: index_hw9.php");
    exit;
  }

  $content = $_POST['content'];
  $sql = sprintf(
    "insert into Anna3143_comments (username, content) values(?, ?)");
   $stmt = $conn->prepare($sql);
   $stmt->bind_param('ss', $username, $content);
  $result = $stmt->execute();
  if (!$result) {
    die($conn->error);
  }

  header("Location: index_hw9.php");
?>