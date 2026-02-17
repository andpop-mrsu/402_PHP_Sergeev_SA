<?php /** @var array $game */ ?>
<?php /** @var array $rounds */ ?>
<div class="card">
  <div class="h1">Результат</div>
  <p class="muted">Игрок: <b><?= h($game['player_name']) ?></b></p>

  <?php if ($game['status'] === 'finished') : ?>
    <?php if ((int) $game['correct_answers'] === (int) $game['total_rounds']) : ?>
      <span class="badge good">Победа</span>
    <?php else : ?>
      <span class="badge bad">Есть ошибки</span>
    <?php endif; ?>
  <?php else : ?>
    <span class="badge">Игра не завершена</span>
  <?php endif; ?>

  <hr>

  <div class="row">
    <div>
      <div class="muted">Правильных ответов</div>
      <div style="font-size: 22px; font-weight: 800">
        <?= (int) $game['correct_answers'] ?> / <?= (int) $game['total_rounds'] ?>
      </div>
    </div>
    <div>
      <div class="muted">Старт</div>
      <div><?= h($game['started_at']) ?></div>
    </div>
    <div>
      <div class="muted">Финиш</div>
      <div><?= h($game['finished_at'] ?? '-') ?></div>
    </div>
  </div>

  <hr>

  <div class="muted" style="margin-bottom:8px">Раунды</div>
  <table class="table">
    <thead>
      <tr>
        <th>#</th>
        <th>Числа</th>
        <th>Ваш ответ</th>
        <th>Правильный</th>
        <th>Итог</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rounds as $r) : ?>
        <tr>
          <td><?= (int) $r['round_no'] ?></td>
          <td><?= (int) $r['a'] ?> и <?= (int) $r['b'] ?></td>
          <td><?= h($r['user_answer'] ?? '-') ?></td>
          <td><?= (int) $r['correct_answer'] ?></td>
          <td>
            <?php if ($r['answered_at'] === null) : ?>
              <span class="badge">не отвечено</span>
            <?php elseif ((int) $r['is_correct'] === 1) : ?>
              <span class="badge good">верно</span>
            <?php else : ?>
              <span class="badge bad">неверно</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <hr>

  <a href="index.php"><button type="button">Сыграть ещё раз</button></a>
  <a href="history.php" style="margin-left: 10px"><button type="button" class="secondary">История</button></a>
</div>
