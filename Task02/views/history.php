<?php /** @var array $games */ ?>
<div class="card">
  <div class="h1">История игр</div>
  <p class="muted">Последние 20 сессий из SQLite.</p>

  <?php if (empty($games)) : ?>
    <div class="alert">Пока нет сыгранных игр. Начните с <a href="index.php">главной</a>.</div>
  <?php else : ?>
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Игрок</th>
          <th>Статус</th>
          <th>Счёт</th>
          <th>Старт</th>
          <th>Ссылка</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($games as $g) : ?>
          <tr>
            <td><?= (int) $g['id'] ?></td>
            <td><?= h($g['player_name']) ?></td>
            <td><?= h($g['status']) ?></td>
            <td><?= (int) $g['correct_answers'] ?> / <?= (int) $g['total_rounds'] ?></td>
            <td><?= h($g['started_at']) ?></td>
            <td><a href="result.php?id=<?= (int) $g['id'] ?>">Открыть</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
