<?php /** @var array $game */ ?>
<?php /** @var array $round */ ?>
<?php /** @var string|null $flash */ ?>
<div class="card">
  <div class="h1">Раунд <?= (int) $round['round_no'] ?> из <?= (int) $game['total_rounds'] ?></div>
  <p class="muted">Игрок: <b><?= h($game['player_name']) ?></b></p>

  <?php if (!empty($flash)) : ?>
    <div class="alert"><?= h($flash) ?></div>
    <hr>
  <?php endif; ?>

  <div class="row">
    <div class="card" style="padding: 14px; border-radius: 16px">
      <div class="muted">Вопрос</div>
      <div style="font-size: 22px; font-weight: 800">
        <?= (int) $round['a'] ?> и <?= (int) $round['b'] ?>
      </div>
      <div class="muted" style="margin-top: 8px"><?= h(GcdGame::description()) ?></div>
    </div>

    <div class="card" style="padding: 14px; border-radius: 16px">
      <div class="muted">Ответ</div>
      <form method="post" action="play.php">
        <input type="hidden" name="round_no" value="<?= (int) $round['round_no'] ?>">
        <label for="answer">Введите НОД</label>
        <input id="answer" name="answer" type="number" inputmode="numeric" required>
        <div style="height: 10px"></div>
        <button type="submit">Проверить</button>
        <a class="secondary" style="margin-left:10px" href="index.php">Новая игра</a>
      </form>
    </div>
  </div>
</div>
