<?php
if (!isset($pageTitle)) $pageTitle = 'Учусь.РФ';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($pageTitle) ?> | Учусь.РФ</title>
  <link rel="stylesheet" href="/uchis-rf/css/style.css">
</head>
<body>
<header class="header">
  <div class="header-inner">
    <a href="/uchis-rf/index.php" class="logo">
      <div class="logo-icon">🎓</div>
      Учусь.<span>РФ</span>
    </a>
    <nav class="nav">
      <a href="/uchis-rf/index.php">Курсы</a>
      <?php if (isLoggedIn()): ?>
        <a href="/uchis-rf/cabinet.php">Кабинет</a>
        <a href="/uchis-rf/apply.php" class="btn btn-primary btn-sm" style="margin-left:4px;">Заявка</a>
        <a href="/uchis-rf/logout.php">Выйти</a>
      <?php else: ?>
        <a href="/uchis-rf/login.php">Войти</a>
        <a href="/uchis-rf/register.php" class="btn btn-primary btn-sm" style="margin-left:4px;">Регистрация</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="main">
