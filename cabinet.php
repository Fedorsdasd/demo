<?php
require_once 'config.php';
requireLogin();
$pageTitle = 'Личный кабинет';

$db     = getDB();
$userId = $_SESSION['user_id'];

$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

$stmt = $db->prepare('
    SELECT a.*, c.title AS course_title, c.type AS course_type,
           r.rating, r.comment AS review_comment, r.id AS review_id
    FROM applications a
    JOIN courses c ON a.course_id = c.id
    LEFT JOIN reviews r ON r.application_id = a.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
');
$stmt->execute([$userId]);
$applications = $stmt->fetchAll();

$reviewError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    $appId   = (int)($_POST['application_id'] ?? 0);
    $rating  = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    $stmt = $db->prepare('SELECT * FROM applications WHERE id = ? AND user_id = ? AND status != "new"');
    $stmt->execute([$appId, $userId]);
    $app = $stmt->fetch();

    if (!$app) {
        $reviewError = 'Оставить отзыв можно только после изменения статуса заявки администратором.';
    } elseif ($rating < 1 || $rating > 5) {
        $reviewError = 'Выберите оценку от 1 до 5.';
    } else {
        $stmt = $db->prepare('
            INSERT INTO reviews (application_id, user_id, rating, comment)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)
        ');
        $stmt->execute([$appId, $userId, $rating, $comment]);
        redirect('cabinet.php?review=1');
    }
}

$statusLabels = [
    'new'         => ['label' => 'Новая',              'badge' => 'badge-new'],
    'in_progress' => ['label' => 'Идёт обучение',      'badge' => 'badge-in_progress'],
    'completed'   => ['label' => 'Обучение завершено', 'badge' => 'badge-completed'],
];
$typeLabels = ['qualification' => 'Повышение квалификации', 'retraining' => 'Переподготовка', 'labor_safety' => 'Охрана труда'];
$paymentLabels = ['card' => 'Банковская карта', 'cash' => 'Наличные', 'invoice' => 'Счёт для организации'];

$countNew    = count(array_filter($applications, fn($a) => $a['status'] === 'new'));
$countActive = count(array_filter($applications, fn($a) => $a['status'] === 'in_progress'));
$countDone   = count(array_filter($applications, fn($a) => $a['status'] === 'completed'));

include 'header.php';
?>

<div class="cabinet-header">
  <div class="container">
    <h1>👤 Личный кабинет</h1>
    <p>Добро пожаловать, <?= h($user['full_name']) ?>!</p>
  </div>
</div>

<div class="container">
  <div class="cabinet-grid">
    <aside class="cabinet-sidebar">
      <div class="card fade-up">
        <h4>Мои данные</h4>
        <p><strong><?= h($user['full_name']) ?></strong></p>
        <p style="color:var(--text-muted);font-size:.85rem;margin-top:4px;">🔑 <?= h($user['login']) ?></p>
        <p style="color:var(--text-muted);font-size:.85rem;">📞 <?= h($user['phone']) ?></p>
        <p style="color:var(--text-muted);font-size:.85rem;">✉️ <?= h($user['email']) ?></p>
      </div>
      <div class="card fade-up fade-up-1">
        <h4>Статистика</h4>
        <div><span class="stat-number"><?= count($applications) ?></span><br><small style="color:var(--text-muted)">Всего заявок</small></div>
        <div style="display:flex;gap:16px;margin-top:12px;">
          <div><div style="font-weight:700;color:#1d4ed8"><?= $countNew ?></div><small>Новых</small></div>
          <div><div style="font-weight:700;color:#92400e"><?= $countActive ?></div><small>В обучении</small></div>
          <div><div style="font-weight:700;color:#065f46"><?= $countDone ?></div><small>Завершено</small></div>
        </div>
      </div>
      <a href="/uchis-rf/apply.php" class="btn btn-blue" style="justify-content:center;padding:13px;">+ Новая заявка</a>
    </aside>

    <div>
      <div class="slider fade-up" id="mainSlider">
        <div class="slider-track">
          <div class="slide slide-1">🎓 Повышение квалификации — развивайте профессиональные навыки</div>
          <div class="slide slide-2">📚 Переподготовка — получите новую специальность</div>
          <div class="slide slide-3">⛑️ Охрана труда — обязательное обучение для специалистов</div>
          <div class="slide slide-4">🏆 Документы государственного образца по итогам обучения</div>
        </div>
        <button class="slider-btn prev">&#8249;</button>
        <button class="slider-btn next">&#8250;</button>
        <div class="slider-dots">
          <button class="slider-dot active"></button>
          <button class="slider-dot"></button>
          <button class="slider-dot"></button>
          <button class="slider-dot"></button>
        </div>
      </div>

      <?php if (isset($_GET['review'])): ?>
        <div class="alert alert-success">✅ Отзыв успешно сохранён!</div>
      <?php endif; ?>
      <?php if ($reviewError): ?>
        <div class="alert alert-danger"><?= h($reviewError) ?></div>
      <?php endif; ?>

      <h3 style="font-family:'Unbounded',sans-serif;margin-bottom:16px;font-size:1rem;">История заявок</h3>

      <?php if (empty($applications)): ?>
        <div class="alert alert-info">У вас ещё нет заявок. <a href="/uchis-rf/apply.php">Подайте первую заявку</a>.</div>
      <?php else: ?>
        <?php foreach ($applications as $app): ?>
          <div class="course-card" style="margin-bottom:14px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
              <div>
                <span class="course-type type-<?= h($app['course_type']) ?>"><?= $typeLabels[$app['course_type']] ?? '' ?></span>
                <div class="course-title" style="margin-top:8px;"><?= h($app['course_title']) ?></div>
                <div style="color:var(--text-muted);font-size:.82rem;margin-top:6px;">
                  📅 <?= date('d.m.Y', strtotime($app['start_date'])) ?> &nbsp;|&nbsp;
                  💳 <?= $paymentLabels[$app['payment_method']] ?? '' ?> &nbsp;|&nbsp;
                  🗓 <?= date('d.m.Y', strtotime($app['created_at'])) ?>
                </div>
              </div>
              <span class="badge <?= $statusLabels[$app['status']]['badge'] ?>"><?= $statusLabels[$app['status']]['label'] ?></span>
            </div>

            <?php if ($app['status'] !== 'new'): ?>
              <?php if ($app['review_id']): ?>
                <div style="background:#f0fdf4;border-radius:8px;padding:12px;margin-top:12px;">
                  <div style="font-weight:600;color:#065f46;margin-bottom:4px;">Ваш отзыв: <?= str_repeat('⭐', $app['rating']) ?></div>
                  <div style="font-size:.88rem;color:var(--text-muted);"><?= h($app['review_comment']) ?></div>
                  <button class="btn btn-sm" style="margin-top:8px;background:#e0f2fe;color:#0369a1;" onclick="openModal('reviewModal<?= $app['id'] ?>')">Редактировать</button>
                </div>
              <?php else: ?>
                <button class="btn btn-sm btn-success" style="margin-top:10px;" onclick="openModal('reviewModal<?= $app['id'] ?>')">✍ Оставить отзыв</button>
              <?php endif; ?>

              <div class="modal-overlay" id="reviewModal<?= $app['id'] ?>">
                <div class="modal">
                  <h3>Отзыв о курсе</h3>
                  <p style="color:var(--text-muted);font-size:.88rem;margin-bottom:14px;"><?= h($app['course_title']) ?></p>
                  <form method="post">
                    <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                    <input type="hidden" name="review_submit" value="1">
                    <div class="form-group">
                      <label>Оценка</label>
                      <select name="rating" required>
                        <?php for ($r = 5; $r >= 1; $r--): ?>
                          <option value="<?= $r ?>" <?= $app['rating'] == $r ? 'selected' : '' ?>><?= str_repeat('⭐', $r) ?> (<?= $r ?> из 5)</option>
                        <?php endfor; ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Комментарий</label>
                      <textarea name="comment" rows="3" placeholder="Ваш отзыв о курсе..."><?= h($app['review_comment'] ?? '') ?></textarea>
                    </div>
                    <div class="modal-actions">
                      <button type="button" class="btn btn-filter" onclick="closeModal('reviewModal<?= $app['id'] ?>')">Отмена</button>
                      <button type="submit" class="btn btn-blue">Сохранить</button>
                    </div>
                  </form>
                </div>
              </div>
            <?php else: ?>
              <div style="font-size:.8rem;color:var(--text-muted);margin-top:8px;">ℹ️ Отзыв можно оставить после изменения статуса администратором</div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
