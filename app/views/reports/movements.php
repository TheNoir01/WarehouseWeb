<?php
$pageTitle = 'Laporan Mutasi & Pergerakan Stok';
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
  <div class="card-header d-flex justify-between align-center">
    <span><i class="bi bi-clock-history me-2"></i>Histori Lengkap Mutasi Stok</span>
    <a href="<?= url('reports/movements-export-excel') ?>" class="btn btn-outline btn-sm" style="font-weight: 600; color: #16a34a; border-color: #16a34a;" title="Ekspor seluruh riwayat mutasi stok ke 1 file Excel multi-sheet terpisah per PT & per Bulan">
      <i class="bi bi-file-earmark-excel me-1"></i>Ekspor Excel (.xlsx)
    </a>
  </div>

  <div class="card-body">
    <!-- Filter -->
    <form method="GET" action="index.php" class="filter-bar">
      <input type="hidden" name="r" value="reports/movements">

      <div style="flex: 1; min-width: 180px;">
        <input type="text" name="search" class="form-control" placeholder="Cari no referensi, nama barang..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      </div>

      <div style="min-width: 150px;">
        <select name="company_id" class="form-select">
          <option value="">Semua PT</option>
          <?php foreach ($companies as $comp): ?>
            <option value="<?= $comp['id'] ?>" <?= ($_GET['company_id'] ?? '') == $comp['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($comp['code'] . ' - ' . $comp['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="min-width: 140px;">
        <select name="movement_type" class="form-select">
          <option value="">-- Tipe Mutasi --</option>
          <option value="IN" <?= ($_GET['movement_type'] ?? '') === 'IN' ? 'selected' : '' ?>>MASUK</option>
          <option value="OUT" <?= ($_GET['movement_type'] ?? '') === 'OUT' ? 'selected' : '' ?>>KELUAR</option>
          <option value="RETURN" <?= ($_GET['movement_type'] ?? '') === 'RETURN' ? 'selected' : '' ?>>PENGEMBALIAN</option>
          <option value="ADJUSTMENT" <?= ($_GET['movement_type'] ?? '') === 'ADJUSTMENT' ? 'selected' : '' ?>>PENYESUAIAN</option>
          <option value="TRANSFER" <?= ($_GET['movement_type'] ?? '') === 'TRANSFER' ? 'selected' : '' ?>>TRANSFER</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
      <a href="<?= url('reports/movements') ?>" class="btn btn-outline btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
    </form>

    <div class="table-responsive">
      <table class="table" style="font-size: 0.85rem;">
        <thead>
          <tr>
            <th>Waktu Mutasi</th>
            <th>PT</th>
            <th>ID / Nama Barang</th>
            <th>Lokasi Rak</th>
            <th>Tipe Mutasi</th>
            <th>No. Referensi</th>
            <th class="text-right">Saldo Awal</th>
            <th class="text-right">Perubahan (Qty)</th>
            <th class="text-right">Saldo Akhir</th>
            <th>Petugas</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($movements)): ?>
            <tr><td colspan="11" class="text-center text-muted" style="padding: 2rem;">Tidak ada histori mutasi tercatat.</td></tr>
          <?php else: ?>
            <?php foreach ($movements as $mov): ?>
              <tr>
                <td class="text-muted"><?= formatDateTime($mov['created_at']) ?></td>
                <td><span class="badge badge-secondary"><?= htmlspecialchars($mov['company']['code'] ?? '-') ?></span></td>
                <td>
                  <a href="<?= url('items/show') ?>&id=<?= $mov['item']['id'] ?? '' ?>" class="fw-bold">
                    <?= htmlspecialchars($mov['item']['name'] ?? '-') ?>
                  </a>
                  <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($mov['item']['item_code'] ?? '') ?></div>
                </td>
                <td>
                  <span class="badge badge-secondary"><?= htmlspecialchars($mov['location']['code'] ?? '-') ?></span>
                </td>
                <td><?= renderBadge($mov['movement_type']) ?></td>
                <td style="font-family: monospace; font-weight: 600;">
                  <?= htmlspecialchars($mov['reference_number'] ?? '-') ?>
                </td>
                <td class="text-right text-muted"><?= formatQty($mov['balance_before']) ?></td>
                <td class="text-right fw-bold <?= (float)$mov['qty'] > 0 ? 'text-success' : 'text-danger' ?>">
                  <?= ((float)$mov['qty'] > 0 ? '+' : '') . formatQty($mov['qty']) ?>
                </td>
                <td class="text-right fw-bold" style="font-size: 0.95rem;">
                  <?= formatQty($mov['balance_after']) ?>
                </td>
                <td><?= htmlspecialchars($mov['user']['name'] ?? '-') ?></td>
                <td class="text-muted" style="font-size: 0.8rem;"><?= htmlspecialchars($mov['notes'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination Info -->
    <?php if (!empty($meta) && ($meta['last_page'] ?? 1) > 1): ?>
      <div class="d-flex justify-between align-center mt-2">
        <span class="text-muted" style="font-size: 0.85rem;">
          Halaman <?= $meta['current_page'] ?> dari <?= $meta['last_page'] ?>
        </span>
        <div class="pagination">
          <?php for ($p = 1; $p <= $meta['last_page']; $p++): ?>
            <a href="<?= url('reports/movements') ?>&page=<?= $p ?>&search=<?= urlencode($_GET['search'] ?? '') ?>&company_id=<?= urlencode($_GET['company_id'] ?? '') ?>&movement_type=<?= urlencode($_GET['movement_type'] ?? '') ?>" class="<?= ($meta['current_page'] == $p) ? 'active' : '' ?>">
              <span><?= $p ?></span>
            </a>
          <?php endfor; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
