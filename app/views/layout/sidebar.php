<?php
$currentRoute = $_GET['r'] ?? 'dashboard';
$user = currentUser();
?>
<aside class="sidebar">
  <div class="sidebar-header">
    <div class="brand-icon"><i class="bi bi-box-seam-fill"></i></div>
    <div>
      <div class="brand-name">GUDANG</div>
      <div class="brand-sub">1 Fisik • Multi-Company</div>
    </div>
  </div>

  <ul class="sidebar-menu">
    <li class="<?= $currentRoute === 'dashboard' ? 'active' : '' ?>">
      <a href="<?= url('dashboard') ?>">
        <i class="bi bi-grid-1x2-fill"></i> <span>Dashboard</span>
      </a>
    </li>

    <li class="menu-category">Inventori & Transaksi</li>

    <li class="<?= str_starts_with($currentRoute, 'items') ? 'active' : '' ?>">
      <a href="<?= url('items') ?>">
        <i class="bi bi-boxes"></i> <span>Daftar Barang</span>
      </a>
    </li>

    <li class="<?= str_starts_with($currentRoute, 'receipts') ? 'active' : '' ?>">
      <a href="<?= url('receipts') ?>">
        <i class="bi bi-box-arrow-in-down"></i> <span>Barang Masuk</span>
      </a>
    </li>

    <li class="<?= str_starts_with($currentRoute, 'issues') ? 'active' : '' ?>">
      <a href="<?= url('issues') ?>">
        <i class="bi bi-box-arrow-up"></i> <span>Barang Keluar</span>
      </a>
    </li>

    <li class="<?= str_starts_with($currentRoute, 'returns') ? 'active' : '' ?>">
      <a href="<?= url('returns') ?>">
        <i class="bi bi-arrow-repeat"></i> <span>Pengembalian</span>
      </a>
    </li>

    <li class="<?= str_starts_with($currentRoute, 'remnants') ? 'active' : '' ?>">
      <a href="<?= url('remnants') ?>">
        <i class="bi bi-scissors"></i> <span>Sisa Material</span>
      </a>
    </li>

    <?php if (canManageMaster()): ?>
      <li class="menu-category">Master Data</li>

      <li class="<?= $currentRoute === 'masters/categories' ? 'active' : '' ?>">
        <a href="<?= url('masters/categories') ?>">
          <i class="bi bi-tags"></i> <span>Kategori</span>
        </a>
      </li>

      <li class="<?= $currentRoute === 'masters/units' ? 'active' : '' ?>">
        <a href="<?= url('masters/units') ?>">
          <i class="bi bi-rulers"></i> <span>Satuan Ukuran</span>
        </a>
      </li>
    <?php endif; ?>

    <?php if (canViewReports()): ?>
      <li class="menu-category">Laporan & Audit</li>

      <li class="<?= $currentRoute === 'reports/stock' ? 'active' : '' ?>">
        <a href="<?= url('reports/stock') ?>">
          <i class="bi bi-graph-up"></i> <span>Laporan Stok per PT</span>
        </a>
      </li>

      <li class="<?= $currentRoute === 'reports/movements' ? 'active' : '' ?>">
        <a href="<?= url('reports/movements') ?>">
          <i class="bi bi-clock-history"></i> <span>Mutasi Stok (Histori)</span>
        </a>
      </li>
    <?php endif; ?>

    <?php if (canManageUsers()): ?>
      <li class="menu-category">Pengaturan</li>
      <li class="<?= $currentRoute === 'users' ? 'active' : '' ?>">
        <a href="<?= url('users') ?>">
          <i class="bi bi-people"></i> <span>Pengguna & Hak Akses</span>
        </a>
      </li>
    <?php endif; ?>
  </ul>

  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar">
        <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
      </div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'User') ?></div>
        <div class="user-role"><?= htmlspecialchars($user['role_label'] ?? $user['role'] ?? '') ?></div>
      </div>
    </div>
  </div>
</aside>
