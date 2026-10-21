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
    <form method="GET" action="index.php" class="filter-bar" id="filterMovementsForm">
      <input type="hidden" name="r" value="reports/movements">

      <div style="flex: 1; min-width: 180px;">
        <input type="text" name="search" class="form-control" placeholder="Cari no referensi, nama barang..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      </div>

      <div style="min-width: 150px;">
        <select name="company_id" class="form-select" onchange="this.form.submit()">
          <option value="">Semua PT</option>
          <?php foreach ($companies as $comp): ?>
            <option value="<?= $comp['id'] ?>" <?= ($_GET['company_id'] ?? '') == $comp['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($comp['code'] . ' - ' . $comp['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="min-width: 140px;">
        <select name="movement_type" class="form-select" onchange="this.form.submit()">
          <option value="">Tipe Mutasi</option>
          <option value="IN" <?= ($_GET['movement_type'] ?? '') === 'IN' ? 'selected' : '' ?>>MASUK</option>
          <option value="OUT" <?= ($_GET['movement_type'] ?? '') === 'OUT' ? 'selected' : '' ?>>KELUAR</option>
          <option value="RETURN" <?= ($_GET['movement_type'] ?? '') === 'RETURN' ? 'selected' : '' ?>>PENGEMBALIAN</option>
          <option value="ADJUSTMENT" <?= ($_GET['movement_type'] ?? '') === 'ADJUSTMENT' ? 'selected' : '' ?>>PENYESUAIAN</option>
          <option value="TRANSFER" <?= ($_GET['movement_type'] ?? '') === 'TRANSFER' ? 'selected' : '' ?>>TRANSFER</option>
        </select>
      </div>

      <!-- Pilihan Data Per Halaman -->
      <div style="min-width: 150px;">
        <select name="per_page" id="filterMovementPerPage" class="form-select" onchange="this.form.submit()" title="Atur berapa banyak data yang ingin ditampilkan per halaman">
          <option value="10" <?= ($_GET['per_page'] ?? '25') == '10' ? 'selected' : '' ?>>10 / halaman</option>
          <option value="25" <?= ($_GET['per_page'] ?? '25') == '25' ? 'selected' : '' ?>>25 / halaman</option>
          <option value="50" <?= ($_GET['per_page'] ?? '25') == '50' ? 'selected' : '' ?>>50 / halaman</option>
          <option value="100" <?= ($_GET['per_page'] ?? '25') == '100' ? 'selected' : '' ?>>100 / halaman</option>
          <option value="all" <?= ($_GET['per_page'] ?? '25') === 'all' ? 'selected' : '' ?>>Tampilkan Semua</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
      <a href="<?= url('reports/movements') ?>" class="btn btn-outline btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
    </form>

    <?php
      $cur = (int) ($meta['current_page'] ?? 1);
      $last = (int) ($meta['last_page'] ?? 1);
      $total = (int) ($meta['total'] ?? count($movements));
      $perPageVal = $_GET['per_page'] ?? '25';
      $perPageNum = (int) ($meta['per_page'] ?? ($perPageVal !== 'all' ? (int)$perPageVal : $total));
      $from = ($total > 0 && $perPageVal !== 'all') ? (($cur - 1) * $perPageNum + 1) : ($total > 0 ? 1 : 0);
      $to = ($perPageVal !== 'all') ? min($total, $cur * $perPageNum) : $total;

      $baseParams = [
          'search' => $_GET['search'] ?? '',
          'company_id' => $_GET['company_id'] ?? '',
          'movement_type' => $_GET['movement_type'] ?? '',
          'per_page' => $perPageVal,
      ];
      $buildPageUrl = function($pageNum) use ($baseParams) {
          $p = array_merge($baseParams, ['page' => $pageNum]);
          return url('reports/movements') . '&' . http_build_query(array_filter($p, fn($v) => $v !== '' && $v !== null));
      };
    ?>

    <div class="d-flex justify-between align-center mb-2" style="font-size: 0.85rem; color: #64748b; flex-wrap: wrap; gap: 0.5rem;">
      <div>
        Menampilkan <strong><?= number_format($total, 0, ',', '.') ?></strong> data mutasi
        <?php if ($last > 1 && $perPageVal !== 'all'): ?>
          <span class="text-muted">(Halaman <?= $cur ?> dari <?= $last ?>)</span>
        <?php endif; ?>
      </div>
      <?php if (!empty($_GET['search']) || !empty($_GET['company_id']) || !empty($_GET['movement_type'])): ?>
        <div style="font-size: 0.8rem; color: var(--primary);">
          <i class="bi bi-funnel-fill me-1"></i> Filter aktif
        </div>
      <?php endif; ?>
    </div>

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
                <td><?= renderCompanyBadge($mov['company']['code'] ?? '-') ?></td>
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

    <!-- Bottom Pagination & Summary Bar -->
    <div class="d-flex justify-between align-center mt-3 pt-3" style="border-top: 1px solid var(--border); flex-wrap: wrap; gap: 1rem;">
      <div class="text-muted" style="font-size: 0.85rem;">
        <?php if ($perPageVal === 'all'): ?>
          Menampilkan seluruh <strong><?= number_format($total, 0, ',', '.') ?></strong> riwayat mutasi stok.
        <?php else: ?>
          Menampilkan baris <strong><?= number_format($from, 0, ',', '.') ?></strong> - <strong><?= number_format($to, 0, ',', '.') ?></strong> dari total <strong><?= number_format($total, 0, ',', '.') ?></strong> data mutasi (Halaman <strong><?= $cur ?></strong> dari <strong><?= $last ?></strong>)
        <?php endif; ?>
      </div>

      <?php if ($last > 1 && $perPageVal !== 'all'): ?>
        <div class="pagination" style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
          <?php if ($cur > 1): ?>
            <a href="<?= $buildPageUrl(1) ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Halaman Pertama">&laquo;</a>
            <a href="<?= $buildPageUrl($cur - 1) ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Sebelumnya">&lsaquo;</a>
          <?php endif; ?>

          <?php
            $startPage = max(1, $cur - 2);
            $endPage = min($last, $cur + 2);
            if ($startPage > 1) {
              echo '<a href="' . $buildPageUrl(1) . '" class="btn btn-outline btn-sm" style="padding: 4px 10px;">1</a>';
              if ($startPage > 2) echo '<span class="text-muted" style="padding: 0 4px;">...</span>';
            }
            for ($p = $startPage; $p <= $endPage; $p++):
          ?>
            <a href="<?= $buildPageUrl($p) ?>" class="btn btn-sm <?= ($cur == $p) ? 'btn-primary' : 'btn-outline' ?>" style="padding: 4px 10px; <?= ($cur == $p) ? 'font-weight: 700;' : '' ?>">
              <?= $p ?>
            </a>
          <?php
            endfor;
            if ($endPage < $last) {
              if ($endPage < $last - 1) echo '<span class="text-muted" style="padding: 0 4px;">...</span>';
              echo '<a href="' . $buildPageUrl($last) . '" class="btn btn-outline btn-sm" style="padding: 4px 10px;">' . $last . '</a>';
            }
          ?>

          <?php if ($cur < $last): ?>
            <a href="<?= $buildPageUrl($cur + 1) ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Berikutnya">&rsaquo;</a>
            <a href="<?= $buildPageUrl($last) ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Halaman Terakhir">&raquo;</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
