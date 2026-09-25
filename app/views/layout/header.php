<?php
$user = currentUser();
$flash = getFlash();
$currentRoute = $_GET['r'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'GUDANG') ?> | <?= APP_NAME ?></title>
  <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<div class="app-container">
  <?php include __DIR__ . '/sidebar.php'; ?>
  
  <div class="main-content">
    <header class="topbar">
      <div class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></div>
      <div class="topbar-actions">
        <span class="badge badge-primary">
          <i class="bi bi-shield-check"></i> <?= htmlspecialchars($user['role_label'] ?? $user['role'] ?? 'User') ?>
        </span>
        <?php if (!empty($user['company'])): ?>
          <span class="badge badge-secondary">
            <i class="bi bi-building"></i> <?= htmlspecialchars($user['company']['code'] ?? '') ?>
          </span>
        <?php endif; ?>
        <a href="<?= url('auth/logout') ?>" class="btn btn-outline btn-sm">
          <i class="bi bi-box-arrow-right"></i> Keluar
        </a>
      </div>
    </header>

    <main class="content-body">
      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
          <span><?= htmlspecialchars($flash['message']) ?></span>
          <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-weight:bold;">&times;</button>
        </div>
      <?php endif; ?>
