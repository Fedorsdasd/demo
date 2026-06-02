<?php
require_once 'config.php';
$pageTitle = 'Каталог курсов';
$filter = $_GET['type'] ?? '';
$db = getDB();
$sql = 'SELECT * FROM courses';
$params = [];
if ($filter) { $sql .= ' WHERE type = ?'; $params[] = $filter; }
$sql .= ' ORDER BY id';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();
$typeLabels = ['qualification' => 'Повышение квалификации', 'retraining' => 'Переподготовка', 'labor_safety' => 'Охрана труда'];
include 'header.php';
?>

<section class="hero">
  <div class="hero-bg">
    <div class="hero-blob1"></div>
    <div class="hero-blob2"></div>
    <div class="hero-blob3"></div>
  </div>
  <div class="container">
    <div class="hero-content">
      <div class="hero-tag fade-up">🎓 Онлайн-обучение с документом гос. образца</div>
      <h1 class="fade-up fade-up-1">Получите<br><em>новую профессию</em><br>не выходя из дома</h1>
      <p class="fade-up fade-up-2">Курсы повышения квалификации, переподготовки и охраны труда. Государственный документ по итогам обучения.</p>
      <div class="hero-btns fade-up fade-up-3">
        <?php if (isLoggedIn()): ?>
          <a href="/uchis-rf/apply.php" class="btn btn-primary">Подать заявку</a>
          <a href="/uchis-rf/cabinet.php" class="btn btn-outline">Личный кабинет</a>
        <?php else: ?>
          <a href="/uchis-rf/register.php" class="btn btn-primary">Начать бесплатно</a>
          <a href="/uchis-rf/login.php" class="btn btn-outline">Войти</a>
        <?php endif; ?>
      </div>
      <div class="hero-stats fade-up">
        <div class="hero-stat"><div class="hero-stat-num">6+</div><div class="hero-stat-lbl">Направлений</div></div>
        <div class="hero-stat"><div class="hero-stat-num">100%</div><div class="hero-stat-lbl">Онлайн</div></div>
        <div class="hero-stat"><div class="hero-stat-num">Гос.</div><div class="hero-stat-lbl">Документ</div></div>
      </div>
    </div>
  </div>
</section>

<section class="adv-section">
  <div class="container">
    <div class="adv-grid">
      <div class="adv-card"><span class="adv-icon">🏆</span><div class="adv-title">Документы гос. образца</div><div class="adv-desc">Удостоверения и дипломы, признанные работодателями по всей России</div></div>
      <div class="adv-card"><span class="adv-icon">💻</span><div class="adv-title">100% онлайн</div><div class="adv-desc">Учитесь в удобное время из любой точки мира без отрыва от работы</div></div>
      <div class="adv-card"><span class="adv-icon">⚡</span><div class="adv-title">Быстрый старт</div><div class="adv-desc">Выберите удобную дату — занятия начнутся в течение нескольких дней</div></div>
      <div class="adv-card"><span class="adv-icon">🔒</span><div class="adv-title">Лицензия Минобрнауки</div><div class="adv-desc">Официально лицензированная организация с многолетним опытом</div></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">Каталог</div>
      <div class="section-title">Все курсы</div>
      <div class="section-sub">Выберите направление и запишитесь онлайн за несколько минут</div>
    </div>
    <div class="filter-bar">
      <span class="filter-label">Фильтр:</span>
      <a href="/uchis-rf/index.php" class="btn btn-sm <?= !$filter ? 'btn-primary' : 'btn-filter' ?>">Все</a>
      <?php foreach ($typeLabels as $key => $label): ?>
        <a href="/uchis-rf/index.php?type=<?= $key ?>" class="btn btn-sm <?= $filter === $key ? 'btn-primary' : 'btn-filter' ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </div>
    <div class="courses-grid">
      <?php foreach ($courses as $i => $course): ?>
        <div class="course-card fade-up fade-up-<?= ($i % 3) + 1 ?>">
          <span class="course-type type-<?= h($course['type']) ?>"><?= $typeLabels[$course['type']] ?? '' ?></span>
          <div class="course-title"><?= h($course['title']) ?></div>
          <div class="course-desc"><?= h($course['description']) ?></div>
          <div class="course-footer">
            <div class="course-meta">⏱ <?= h($course['duration']) ?></div>
            <?php if (isLoggedIn()): ?>
              <a href="/uchis-rf/apply.php?course_id=<?= $course['id'] ?>" class="btn btn-sm btn-primary">Записаться →</a>
            <?php else: ?>
              <a href="/uchis-rf/register.php" class="btn btn-sm btn-ghost">Войти</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($courses)): ?><p style="color:var(--muted);grid-column:1/-1;">Курсы не найдены.</p><?php endif; ?>
    </div>
  </div>
</section>

<div class="cta-section">
  <div style="position:relative;">
    <h2>Готовы начать обучение?</h2>
    <p>Зарегистрируйтесь, выберите курс и подайте заявку. Всё просто — мы свяжемся в течение дня.</p>
    <?php if (!isLoggedIn()): ?>
      <a href="/uchis-rf/register.php" class="btn btn-accent">Зарегистрироваться бесплатно</a>
    <?php else: ?>
      <a href="/uchis-rf/apply.php" class="btn btn-accent">Подать заявку</a>
    <?php endif; ?>
  </div>
</div>

<?php include 'footer.php'; ?>
