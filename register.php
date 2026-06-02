<?php
require_once 'config.php';
$pageTitle = 'Регистрация';
$errors = [];
$values = ['login' => '', 'full_name' => '', 'phone' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login     = trim($_POST['login'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $values    = compact('login', 'full_name', 'phone', 'email');

    if (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login))
        $errors['login'] = 'Минимум 6 символов, только латинские буквы и цифры';
    if (strlen($password) < 8)
        $errors['password'] = 'Пароль должен содержать минимум 8 символов';
    if (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]{3,}$/u', $full_name))
        $errors['full_name'] = 'Введите корректное ФИО';
    if (!preg_match('/^[\+\d][\d\s\-\(\)]{6,}$/', $phone))
        $errors['phone'] = 'Введите корректный номер телефона';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Введите корректный e-mail';

    if (empty($errors['login'])) {
        $db = getDB();
        $stmt = $db->prepare('SELECT id FROM users WHERE login = ?');
        $stmt->execute([$login]);
        if ($stmt->fetch()) $errors['login'] = 'Такой логин уже занят';
    }

    if (empty($errors)) {
        $db = getDB();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare('INSERT INTO users (login, password, full_name, phone, email) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$login, $hash, $full_name, $phone, $email]);
        $_SESSION['user_id']    = $db->lastInsertId();
        $_SESSION['user_name']  = $full_name;
        $_SESSION['user_login'] = $login;
        redirect('cabinet.php');
    }
}

include 'header.php';
?>
<div class="form-wrap">
  <div class="form-card fade-up">
    <h2>Регистрация</h2>
    <p class="form-sub">Создайте аккаунт для доступа к курсам</p>
    <form id="registerForm" method="post" novalidate>
      <div class="form-group">
        <label>Логин *</label>
        <input type="text" name="login" value="<?= h($values['login']) ?>" placeholder="Минимум 6 символов, латиница"
               class="<?= isset($errors['login']) ? 'is-invalid' : '' ?>" required>
        <div class="error" id="err_login"><?= h($errors['login'] ?? '') ?></div>
      </div>
      <div class="form-group">
        <label>Пароль *</label>
        <input type="password" name="password" placeholder="Минимум 8 символов"
               class="<?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
        <div class="error" id="err_password"><?= h($errors['password'] ?? '') ?></div>
      </div>
      <div class="form-group">
        <label>ФИО *</label>
        <input type="text" name="full_name" value="<?= h($values['full_name']) ?>" placeholder="Иванов Иван Иванович"
               class="<?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" required>
        <div class="error" id="err_full_name"><?= h($errors['full_name'] ?? '') ?></div>
      </div>
      <div class="form-group">
        <label>Телефон *</label>
        <input type="tel" name="phone" value="<?= h($values['phone']) ?>" placeholder="+7 (900) 000-00-00"
               class="<?= isset($errors['phone']) ? 'is-invalid' : '' ?>" required>
        <div class="error" id="err_phone"><?= h($errors['phone'] ?? '') ?></div>
      </div>
      <div class="form-group">
        <label>E-mail *</label>
        <input type="email" name="email" value="<?= h($values['email']) ?>" placeholder="example@mail.ru"
               class="<?= isset($errors['email']) ? 'is-invalid' : '' ?>" required>
        <div class="error" id="err_email"><?= h($errors['email'] ?? '') ?></div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:1rem;">Создать аккаунт</button>
    </form>
    <div class="form-footer">Уже есть аккаунт? <a href="/uchis-rf/login.php">Войти</a></div>
  </div>
</div>
<?php include 'footer.php'; ?>
