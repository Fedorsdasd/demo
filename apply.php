<?php
require_once 'config.php';
requireLogin();
$pageTitle = 'Подать заявку';

$db      = getDB();
$userId  = $_SESSION['user_id'];
$success = false;
$errors  = [];

$courses = $db->query('SELECT * FROM courses ORDER BY type, title')->fetchAll();
$preSelectedCourse = (int)($_GET['course_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId      = (int)($_POST['course_id'] ?? 0);
    $startDate     = trim($_POST['start_date'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? '');

    $validCourseIds = array_column($courses, 'id');
    if (!in_array($courseId, $validCourseIds))
        $errors['course_id'] = 'Выберите курс из списка';

    $parsedDate = null;
    if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $startDate, $m))
        $parsedDate = "{$m[3]}-{$m[2]}-{$m[1]}";
    elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate))
        $parsedDate = $startDate;

    if (!$parsedDate || strtotime($parsedDate) < strtotime('today'))
        $errors['start_date'] = 'Укажите корректную дату (не раньше сегодняшнего дня), формат ДД.ММ.ГГГГ';

    if (!in_array($paymentMethod, ['card', 'cash', 'invoice']))
        $errors['payment_method'] = 'Выберите способ оплаты';

    if (empty($errors)) {
        $stmt = $db->prepare('INSERT INTO applications (user_id, course_id, start_date, payment_method) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $courseId, $parsedDate, $paymentMethod]);
        $success = true;
    }
}

$typeLabels = ['qualification' => 'Повышение квалификации', 'retraining' => 'Переподготовка', 'labor_safety' => 'Охрана труда'];
$typeGroups = [];
foreach ($courses as $c) $typeGroups[$c['type']][] = $c;

include 'header.php';
?>
<div class="container">
  <div class="form-card fade-up" style="max-width:560px;">
    <h2>📋 Подать заявку</h2>
    <?php if ($success): ?>
      <div class="alert alert-success">✅ Заявка успешно подана! Администратор рассмотрит её в ближайшее время.</div>
      <div style="text-align:center;margin-top:12px;">
        <a href="/uchis-rf/cabinet.php" class="btn btn-blue">Перейти в кабинет</a>
        <a href="/uchis-rf/apply.php" class="btn btn-filter" style="margin-left:8px;">Подать ещё одну</a>
      </div>
    <?php else: ?>
    <form method="post">
      <div class="form-group">
        <label>Курс *</label>
        <select name="course_id" class="<?= isset($errors['course_id']) ? 'is-invalid' : '' ?>" required>
          <option value="">— Выберите курс —</option>
          <?php foreach ($typeGroups as $type => $group): ?>
            <optgroup label="<?= $typeLabels[$type] ?>">
              <?php foreach ($group as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ((int)($_POST['course_id'] ?? $preSelectedCourse) === (int)$c['id']) ? 'selected' : '' ?>>
                  <?= h($c['title']) ?> (<?= h($c['duration']) ?>)
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['course_id'])): ?><div class="error"><?= h($errors['course_id']) ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Дата начала обучения * (ДД.ММ.ГГГГ)</label>
        <input type="text" name="start_date" placeholder="<?= date('d.m.Y', strtotime('+3 days')) ?>"
               value="<?= h($_POST['start_date'] ?? '') ?>"
               class="<?= isset($errors['start_date']) ? 'is-invalid' : '' ?>"
               maxlength="10" required>
        <?php if (isset($errors['start_date'])): ?><div class="error"><?= h($errors['start_date']) ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Способ оплаты *</label>
        <select name="payment_method" class="<?= isset($errors['payment_method']) ? 'is-invalid' : '' ?>" required>
          <option value="">— Выберите способ —</option>
          <option value="card"    <?= ($_POST['payment_method'] ?? '') === 'card'    ? 'selected' : '' ?>>💳 Банковская карта</option>
          <option value="cash"    <?= ($_POST['payment_method'] ?? '') === 'cash'    ? 'selected' : '' ?>>💵 Наличные</option>
          <option value="invoice" <?= ($_POST['payment_method'] ?? '') === 'invoice' ? 'selected' : '' ?>>🧾 Счёт для организации</option>
        </select>
        <?php if (isset($errors['payment_method'])): ?><div class="error"><?= h($errors['payment_method']) ?></div><?php endif; ?>
      </div>
      <button type="submit" class="btn btn-blue" style="width:100%;justify-content:center;padding:13px;">Отправить заявку</button>
    </form>
    <?php endif; ?>
    <div class="form-footer"><a href="/uchis-rf/cabinet.php">← Назад в кабинет</a></div>
  </div>
</div>
<script>
document.querySelector('[name="start_date"]').addEventListener('input', function(e) {
  let v = e.target.value.replace(/\D/g, '').substring(0, 8);
  if (v.length > 4) v = v.slice(0,2) + '.' + v.slice(2,4) + '.' + v.slice(4);
  else if (v.length > 2) v = v.slice(0,2) + '.' + v.slice(2);
  e.target.value = v;
});
</script>
<?php include 'footer.php'; ?>
