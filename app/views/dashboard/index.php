<?php
$pageTitle = 'Dashboard Gudang';
include __DIR__ . '/../layout/header.php';

$overview = $dashboardData['overview'] ?? [];
$companiesStats = $dashboardData['companies_stats'] ?? [];
$alertItems = $dashboardData['alert_items'] ?? [];
$recentMovements = $dashboardData['recent_movements'] ?? [];
?>

<!-- Ringkasan Statistik Global -->
<div class="stats-grid">
  <div class="stat-card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
      <span class="stat-label">Total Jenis Barang</span>
      <i class="bi bi-boxes" style="font-size: 1.35rem; color: var(--primary);"></i>
    </div>
    <span class="stat-value"><?= number_format($overview['total_items'] ?? 0) ?></span>
    <span class="stat-sub">Katalog master kedua PT</span>
  </div>
  <div class="stat-card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
      <span class="stat-label">Barang Masuk</span>
      <i class="bi bi-box-arrow-in-down" style="font-size: 1.35rem; color: var(--success);"></i>
    </div>
    <span class="stat-value"><?= number_format($overview['total_receipts'] ?? 0) ?></span>
    <span class="stat-sub">Dokumen penerimaan</span>
  </div>
  <div class="stat-card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
      <span class="stat-label">Barang Keluar</span>
      <i class="bi bi-box-arrow-up" style="font-size: 1.35rem; color: var(--danger);"></i>
    </div>
    <span class="stat-value"><?= number_format($overview['total_issues'] ?? 0) ?></span>
    <span class="stat-sub">Pengeluaran ke proyek</span>
  </div>
  <div class="stat-card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
      <span class="stat-label">Sisa Material</span>
      <i class="bi bi-scissors" style="font-size: 1.35rem; color: #8b5cf6;"></i>
    </div>
    <span class="stat-value"><?= number_format($overview['total_remnants'] ?? 0) ?></span>
    <span class="stat-sub">Tercatat & terlacak</span>
  </div>
</div>

<!-- Pemisahan Stok Berdasarkan PT -->
<h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.85rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
  <i class="bi bi-buildings"></i> Pemisahan Stok Berdasarkan PT
</h2>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
  <?php foreach ($companiesStats as $comp): ?>
    <div class="card" style="border-top: 4px solid var(--primary);">
      <div class="card-header">
        <div>
          <span class="badge badge-primary"><?= htmlspecialchars($comp['company_code']) ?></span>
          <span style="font-weight: 700; margin-left: 0.5rem;"><?= htmlspecialchars($comp['company_name']) ?></span>
        </div>
        <a href="<?= url('items') ?>&company_id=<?= $comp['company_id'] ?>" class="btn btn-outline btn-sm">
          <i class="bi bi-box-seam"></i> Lihat Stok PT
        </a>
      </div>
      <div class="card-body">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1rem;">
          <div>
            <div class="text-muted" style="font-size: 0.8rem; font-weight: 600;">Total Jenis Barang</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--primary);">
              <?= number_format($comp['total_items']) ?> <span style="font-size: 0.85rem; font-weight: normal; color: #64748b;">Jenis / SKU</span>
            </div>
          </div>
          <div style="text-align: right;">
            <span class="badge badge-secondary" style="font-size: 0.75rem;"><i class="bi bi-shield-check"></i> Master PT Terpisah</span>
          </div>
        </div>

        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
          <span class="badge badge-success"><i class="bi bi-check-circle"></i> Tersedia: <?= $comp['status_counts']['tersedia'] ?></span>
          <span class="badge badge-warning"><i class="bi bi-exclamation-circle"></i> Menipis: <?= $comp['status_counts']['menipis'] ?></span>
          <span class="badge badge-danger"><i class="bi bi-x-circle"></i> Habis: <?= $comp['status_counts']['habis'] ?></span>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
  <!-- Peringatan Stok Menipis / Habis -->
  <div class="card">
    <div class="card-header">
      <span><i class="bi bi-exclamation-triangle-fill text-warning"></i> Peringatan Stok Menipis & Habis</span>
      <a href="<?= url('items') ?>&stock_status=MENIPIS" class="btn btn-outline btn-sm">Semua Alert</a>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>PT</th>
            <th>Kode / Barang</th>
            <th>Stok Saat Ini</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($alertItems)): ?>
            <tr><td colspan="5" class="text-center text-muted" style="padding: 1.5rem;">Seluruh persediaan dalam kondisi aman (Tersedia).</td></tr>
          <?php else: ?>
            <?php foreach ($alertItems as $item): ?>
              <tr>
                <td><span class="badge badge-secondary"><?= htmlspecialchars($item['company_code']) ?></span></td>
                <td>
                  <a href="<?= url('items/show') ?>&id=<?= $item['id'] ?>" class="fw-bold">
                    <?= htmlspecialchars($item['name']) ?>
                  </a>
                  <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($item['item_code']) ?></div>
                </td>
                <td class="fw-bold"><?= formatQty($item['total_stock'], $item['unit']) ?></td>
                <td><?= renderBadge($item['stock_status']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Log Mutasi Fisik Terkini (Audit Trail Mutasi) -->
  <div class="card">
    <div class="card-header">
      <span><i class="bi bi-clock-history text-primary"></i> Log Mutasi Stok Terkini</span>
      <?php if (canViewReports()): ?>
        <a href="<?= url('reports/movements') ?>" class="btn btn-outline btn-sm">Lihat Mutasi</a>
      <?php endif; ?>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Waktu</th>
            <th>PT</th>
            <th>Barang</th>
            <th>Tipe</th>
            <th>Perubahan</th>
            <th>Stock Akhir</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentMovements)): ?>
            <tr><td colspan="6" class="text-center text-muted" style="padding: 1.5rem;">Belum ada aktivitas mutasi barang.</td></tr>
          <?php else: ?>
            <?php foreach ($recentMovements as $mov): ?>
              <tr>
                <td style="font-size: 0.8rem; color: #64748b;"><?= formatDateTime($mov['created_at']) ?></td>
                <td><span class="badge badge-secondary"><?= htmlspecialchars($mov['company']['code'] ?? '-') ?></span></td>
                <td>
                  <div class="fw-bold"><?= htmlspecialchars($mov['item']['name'] ?? '-') ?></div>
                  <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($mov['location']['code'] ?? '') ?></div>
                </td>
                <td><?= renderBadge($mov['movement_type']) ?></td>
                <td class="fw-bold <?= (float)$mov['qty'] > 0 ? 'text-success' : 'text-danger' ?>">
                  <?= ((float)$mov['qty'] > 0 ? '+' : '') . formatQty($mov['qty']) ?>
                </td>
                <td class="fw-bold"><?= formatQty($mov['balance_after']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
