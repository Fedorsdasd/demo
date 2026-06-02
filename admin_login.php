<?php
require_once 'config.php';
$pageTitle = 'Вход для администратора';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($login === ADMIN_LOGIN && $password === ADMIN_PASSWORD) {
        $_SESSION['is_admin']    = true;
        $_SESSION['admin_login'] = $login;
        header('Location: ' . BASE_URL . '/admin/index.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}

include 'header.php';
?>
<div class="form-wrap">
  <div class="form-card fade-up" style="border-top:3px solid var(--primary);">
    <h2>🛡 Администратор</h2>
    <p class="form-sub">Доступ только для сотрудников портала</p>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="form-group">
        <label>Логин</label>
        <input type="text" name="login" placeholder="Admin26" required autofocus>
      </div>
      <div class="form-group">
        <label>Пароль</label>
        <input type="password" name="password" placeholder="Demo20" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:1rem;">Войти</button>
    </form>
    <div class="form-footer"><a href="/uchis-rf/index.php">← На главную</a></div>
  </div>
</div>
<?php include 'footer.php'; ?>
