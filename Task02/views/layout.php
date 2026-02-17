<?php /** @var string $title */ ?>
<?php /** @var string $content */ ?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?></title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <div class="nav">
      <a href="index.php">Главная</a>
      <a href="history.php">История</a>
      <a href="play.php">Играть</a>
    </div>

    <?= $content ?>

    <div class="footer">
      Task02 • PHP встроенный сервер • SQLite (файл БД в /db вне public)
    </div>
  </div>
</body>
</html>
