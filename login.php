<?php
require_once 'config.php';
$pageTitle = 'Вход';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if (empty($login) || empty($password)) {
        $error = 'Введите логин и пароль';
    } else {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE login = ?');
        $stmt->execute([$login]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['full_name'];
            $_SESSION['user_login'] = $user['login'];
            redirect('cabinet.php');
        } else {
            $error = 'Неверный логин или пароль';
        }
    }
}

include 'header.php';
?>
<div class="form-wrap">
  <div class="form-card fade-up">
    <h2>Вход в систему</h2>
    <p class="form-sub">Введите логин и пароль для входа</p>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="form-group">
        <label>Логин</label>
        <input type="text" name="login" placeholder="Ваш логин" value="<?= h($_POST['login'] ?? '') ?>" required autofocus>
      </div>
      <div class="form-group">
        <label>Пароль</label>
        <input type="password" name="password" placeholder="Ваш пароль" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:1rem;">Войти</button>
    </form>
    <div class="form-footer">Ещё не зарегистрированы? <a href="/uchis-rf/register.php">Регистрация</a></div>
  </div>
</div>
<?php include 'footer.php'; ?>
