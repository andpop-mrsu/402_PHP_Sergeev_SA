<?php /** @var string|null $error */ ?>
<div class="card">
  <div class="h1">Игра: НОД</div>
  <p class="muted"><?= h(GcdGame::description()) ?></p>

  <?php if (!empty($error)) : ?>
    <div class="alert"><?= h($error) ?></div>
    <hr>
  <?php endif; ?>

  <form method="post" action="index.php">
    <label for="name">Ваше имя</label>
    <input id="name" name="name" type="text" autocomplete="name" required maxlength="40" placeholder="Например: Сергей">
    <div style="height: 10px"></div>
    <button type="submit">Начать игру</button>
  </form>

  <hr>

  <p class="muted">
    После старта создаётся запись игрока и сессия игры в SQLite. Затем игра идёт раундами (всего 3).
  </p>
</div>
