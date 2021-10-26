<?php
  session_start();
  require_once("conn_hw9.php");
  require_once("utils.php");

  $username = NULL;
  $user = NULL;
  if(!empty($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $user = getUserFromUsername($username);
  }
 
 $page = 1;
  if (!empty($_GET['page'])) {
    $page = intval($_GET['page']);
  }
  $items_per_page = 10;
  $offset = ($page - 1) * $items_per_page;

  $stmt = $conn->prepare(
      'select '.
        'C.id as id, C.content as content, '.
        'C.created_at as created_at, U.nickname as nickname, U.username as username '.
        'from Anna3143_comments as C ' .
        'left join Anna3143_users as U on C.username = U.username '.
        'where C.is_deleted IS NULL '.
        'order by C.id desc '.
        'limit ? offset ? '
  );
  $stmt->bind_param('ii', $items_per_page, $offset);
  $result = $stmt->execute();
  if (!$result) {
    die('Error:' . $conn->error);
  }
  $result = $stmt->get_result();
?>
  
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>留言板</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="warning">
    <strong>注意！本站為練習用網站，因教學用途刻意忽略資安的實作，註冊時請勿使用任何真實的帳號或密碼。</strong>
  </header>
  <main class="board">
      <div>
        <?php if (!$username) { ?>
          <a class="board__btn" href="register.php">註冊</a>
          <a class="board__btn" href="login.php">登入</a>
        <?php } else { ?>
          <a class="board__btn" href="handle_logout.php">登出</a>
          <span class="board__btn update-nickname">編輯暱稱</span>
          <?php if ($user && $user['role'] === 'ADMIN') { ?>
            <a class="board__btn" href="admin_hw9.php">管理後臺</a>
          <?php } ?>          
          <form class="hide board__nickname-form board__new-comment-form" method="POST" action="update_user.php">
            <div class="board__nickname">
              <span>新的暱稱：</span>
              <input type="text" name="nickname" />
            </div>
            <input class="board__submit-btn" type="submit" />
          </form>      
          <h4>你好！<?php echo $user['nickname']; ?>與我們一同分享本書的想法吧!!</h4>
        <?php } ?>
         <section class="book">       
       <img src="https://i.imgur.com/J7p0l7S.jpg">
         <div class="book-title">原子習慣：細微改變帶來巨大成就的實證法則</div>
        <div class="descr">
   
　　每天都進步1%，一年後，你會進步37倍！<br>
　　每天都退步1%，一年後，你會弱化到趨近於0！<br>
　　你的一點小改變、一個好習慣，將會產生複利效應，<br>
　　如滾雪球般，為你帶來豐碩的人生成果！<br>

　　一出版立刻風行全球，<br>
　　企業界、運動界、教育界、瘦身界都在用的習慣養成實作指南！</div>
      </section>      
     
      </div>
      
      <h1 class="board__title">Comments</h1>
      <?php
        if (!empty($_GET['errCode'])) {
          $code = $_GET['errCode'];
          $msg = 'Error';
          if ($code === '1') {
            $msg = '資料不齊全';
          }
          echo '<h2 class="error">錯誤：' . $msg . '</h2>';
        }
      ?>
        <form class="board__new-comment-form" method="POST" action="handle_add_comment.php">
          <textarea name="content" rows="5"></textarea>
          <?php if ($username && !hasPermission($user, 'create', NULL )) { ?>
            <h3>你已被停權</h3>
          <?php } else if ($username) { ?>
            <input class="board__submit-btn" type="submit" />
          <?php } else { ?>
            <h3>請登入發布留言</h3>
          <?php } ?>  
        </form>
      <div class="board__hr"></div>
      <section>
     <?php
          while($row = $result->fetch_assoc()) {
        ?>
          <div class="card">
            <div class="card__avatar"></div>
            <div class="card__body">
                <div class="card__info">
                  <span class="card__author">
                    <?php echo escape($row['nickname']); ?>
                    (@<?php echo escape($row['username']); ?>)
                  </span>
                  <span class="card__time">
                    <?php echo escape($row['created_at']); ?>
                  </span>
                  <?php if (hasPermission($user, 'update', $row)) { ?>
                    <a href="update_comment.php?id=<?php echo $row['id'] ?>">編輯</a>
                    <a href="delete_comment.php?id=<?php echo $row['id'] ?>">刪除</a>
                  <? } ?>
                </div>
                <p class="card__content"><?php echo escape($row['content']); ?></p>
            </div>
          </div>
        <?php } ?>
      </section>

      <div class="board__hr"></div>
      
      <?php
         $stmt = $conn->prepare(
          'select count(id) as count from Anna3143_comments where is_deleted IS NULL'
        );
        $result = $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = $row['count'];
        $total_page = ceil($count / $items_per_page);
      ?>
      <div class="page-info">
        <span>總共有 <?php echo $count ?> 筆留言，頁數：</span>
        <span><?php echo $page ?> / <?php echo $total_page ?></span>
      </div>
      <div class="paginator">
        <?php if ($page != 1) { ?> 
          <a href="index_hw9.php?page=1">首頁</a>
          <a href="index_hw9.php?page=<?php echo $page - 1 ?>">上一頁</a>
        <?php } ?>
        <?php if ($page != $total_page) { ?>
          <a href="index_hw9.php?page=<?php echo $page + 1 ?>">下一頁</a>
          <a href="index_hw9.php?page=<?php echo $total_page ?>">最後一頁</a> 
        <?php } ?>
      </div>
  </main>
  <script>
    var btn = document.querySelector('.update-nickname')
    btn.addEventListener('click', function() {
      var form = document.querySelector('.board__nickname-form')
      form.classList.toggle('hide')
    })
  </script>
</body>
</html>