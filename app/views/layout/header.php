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
  <script>
    (function() {
      var z = localStorage.getItem('wh_zoom');
      if (z) document.documentElement.style.zoom = z + '%';
    })();
  </script>
</head>
<body>
<div class="app-container">
  <?php include __DIR__ . '/sidebar.php'; ?>
  
  <div class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <button type="button" class="btn-sidebar-toggle" id="btnToggleSidebar" title="Menu Pintasan (Garis Tiga)" aria-label="Menu Pintasan">
          <i class="bi bi-list"></i>
        </button>
        <div class="brand-shortcut d-flex align-center gap-1" style="cursor: pointer;" onclick="document.getElementById('btnToggleSidebar').click();" title="Buka Menu Pintasan">
          <div class="brand-mini-icon"><i class="bi bi-box-seam-fill"></i></div>
          <span class="brand-mini-text">GUDANG</span>
        </div>
        <span class="topbar-divider"></span>
        <div class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></div>
      </div>
      <div class="topbar-actions">
        <!-- Tombol Zoom In / Zoom Out -->
        <div class="zoom-controls" title="Pengaturan Zoom Tampilan Layar">
          <button type="button" class="btn-zoom" id="btnZoomOut" title="Zoom Out (Perkecil Layar)" aria-label="Zoom Out">
            <i class="bi bi-zoom-out"></i>
          </button>
          <span class="zoom-value" id="zoomLevelDisplay" title="Klik untuk Reset Zoom (100%)">100%</span>
          <button type="button" class="btn-zoom" id="btnZoomIn" title="Zoom In (Perbesar Layar)" aria-label="Zoom In">
            <i class="bi bi-zoom-in"></i>
          </button>
        </div>

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
