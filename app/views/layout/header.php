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
  <title><?= htmlspecialchars($pageTitle ?? 'GUDANG') ?></title>
  <link rel="icon" type="image/png" href="<?= asset('img/kjg.png') ?>">
  <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  <script>
    (function() {
      var z = localStorage.getItem('wh_zoom');
      if (z) document.documentElement.style.zoom = z + '%';

      window.openSidebar = function() {
        var s = document.getElementById('sidebarMenu');
        var b = document.getElementById('sidebarBackdrop');
        if (s) s.classList.add('show');
        if (b) b.classList.add('show');
        document.body.style.overflow = 'hidden';
      };

      window.closeSidebar = function() {
        var s = document.getElementById('sidebarMenu');
        var b = document.getElementById('sidebarBackdrop');
        if (s) s.classList.remove('show');
        if (b) b.classList.remove('show');
        document.body.style.overflow = '';
      };

      var lastToggle = 0;
      window.toggleSidebar = function() {
        var now = Date.now();
        if (now - lastToggle < 200) return;
        lastToggle = now;

        var s = document.getElementById('sidebarMenu');
        if (s && s.classList.contains('show')) {
          window.closeSidebar();
        } else {
          window.openSidebar();
        }
      };
    })();
  </script>
</head>
<body>
<div class="app-container">
  <?php include __DIR__ . '/sidebar.php'; ?>
  
  <div class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <button type="button" class="btn-sidebar-toggle" id="btnToggleSidebar" onclick="window.toggleSidebar();" title="Menu Pintasan (Garis Tiga)" aria-label="Menu Pintasan">
          <i class="bi bi-list"></i>
        </button>
        <div class="brand-shortcut d-flex align-center gap-1" style="cursor: pointer;" onclick="window.toggleSidebar();" title="Buka Menu Pintasan">
          <div class="brand-mini-icon"><img src="<?= asset('img/kjg.png') ?>" alt="Logo KJG"></div>
        </div>
        <span class="topbar-divider"></span>
        <div class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></div>
      </div>
      <div class="topbar-actions">
        <!-- Dropdown Zoom Skala Layar -->
        <div class="zoom-dropdown-container" title="Pilih Skala Zoom Layar">
          <i class="bi bi-zoom-in"></i>
          <select id="zoomSelect" class="zoom-select" aria-label="Pilih Skala Zoom Layar">
            <option value="70">70%</option>
            <option value="80">80%</option>
            <option value="85">85%</option>
            <option value="90">90%</option>
            <option value="95">95%</option>
            <option value="100" selected>100%</option>
            <option value="110">110%</option>
            <option value="120">120%</option>
            <option value="125">125%</option>
            <option value="130">130%</option>
            <option value="140">140%</option>
            <option value="150">150%</option>
          </select>
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
        <div class="alert alert-<?= $flash['type'] ?>" id="flash-alert" style="transition: opacity 0.5s ease, transform 0.5s ease;">
          <span><?= htmlspecialchars($flash['message']) ?></span>
          <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-weight:bold;font-size:1.1rem;line-height:1;">&times;</button>
        </div>
        <?php if (in_array($flash['type'], ['warning', 'success', 'sukses'])): ?>
          <script>
            setTimeout(function() {
              const el = document.getElementById('flash-alert');
              if (el) {
                el.style.opacity = '0';
                el.style.transform = 'translateY(-6px)';
                setTimeout(function() { if (el) el.remove(); }, 500);
              }
            }, 10000);
          </script>
        <?php endif; ?>
      <?php endif; ?>
