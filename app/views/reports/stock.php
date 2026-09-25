<?php
$pageTitle = 'Laporan Saldo Stok Inventori per PT';
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
  <div class="card-header d-flex justify-between align-center">
    <span><i class="bi bi-graph-up me-2"></i>Laporan Saldo Stok Fisik per PT</span>
    <a href="<?= url('reports/stock-export-excel') ?>" class="btn btn-outline btn-sm" style="font-weight: 600; color: #16a34a; border-color: #16a34a;" title="Ekspor seluruh data saldo stok ke 1 file Excel multi-sheet terpisah per PT & per Bulan">
      <i class="bi bi-file-earmark-excel me-1"></i>Ekspor Excel (.xlsx)
    </a>
  </div>

  <div class="card-body">
    <!-- Filter -->
    <form method="GET" action="index.php" class="filter-bar">
      <input type="hidden" name="r" value="reports/stock">

      <div style="flex: 1; min-width: 180px;">
        <input type="text" name="search" class="form-control" placeholder="Cari nama barang atau kode..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      </div>

      <div style="min-width: 180px;">
        <select name="company_id" class="form-select">
          <option value="">-- Semua PT Pemilik --</option>
          <?php foreach ($companies as $comp): ?>
            <option value="<?= $comp['id'] ?>" <?= ($_GET['company_id'] ?? '') == $comp['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($comp['code'] . ' - ' . $comp['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
      <a href="<?= url('reports/stock') ?>" class="btn btn-outline btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
    </form>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>PT Pemilik</th>
            <th>ID Barang</th>
            <th>Nama Barang & Spesifikasi</th>
            <th>Kategori</th>
            <th>Satuan</th>
            <th>Total Stok</th>
            <th>Status</th>
            <th>Terakhir Dimutasi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($balances)): ?>
            <tr><td colspan="8" class="text-center text-muted" style="padding: 2rem;">Tidak ada data stok.</td></tr>
          <?php else: ?>
            <?php foreach ($balances as $b): ?>
              <?php
                $item = $b['item'] ?? [];
                $stock = (float) ($b['qty'] ?? 0);
                $min = (float) ($item['minimum_stock'] ?? 0);
                $stockStatus = $b['stock_status'] ?? ($stock <= 0 ? 'HABIS' : (($min > 0 && $stock <= $min) ? 'MENIPIS' : 'TERSEDIA'));
              ?>
              <tr>
                <td><span class="badge badge-primary"><?= htmlspecialchars($b['company']['code'] ?? 'N/A') ?></span></td>
                <td>
                  <span style="font-family: monospace; font-weight: 600; color: #1e40af;">
                    <?= htmlspecialchars($item['item_code'] ?? '-') ?>
                  </span>
                </td>
                <td>
                  <a href="<?= url('items/show') ?>&id=<?= $item['id'] ?? '' ?>" class="fw-bold" style="color: #1e40af; text-decoration: none;">
                    <?= htmlspecialchars($item['name'] ?? '-') ?>
                  </a>
                  <?php if (!empty($item['specification'])): ?>
                    <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($item['specification']) ?></div>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($item['category']['name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($item['unit']['code'] ?? '-') ?></td>
                <td class="fw-bold" style="font-size: 1rem;">
                  <?= formatQty($stock, $item['unit']['code'] ?? '') ?>
                </td>
                <td><?= renderBadge($stockStatus) ?></td>
                <td class="text-muted" style="font-size: 0.85rem;"><?= formatDateTime($b['last_movement_at'] ?? $b['updated_at'] ?? null) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination Info -->
    <?php if (!empty($meta) && ($meta['last_page'] ?? 1) > 1): ?>
      <?php
        $cur = (int) ($meta['current_page'] ?? 1);
        $last = (int) ($meta['last_page'] ?? 1);
        $queryParams = $_GET;
      ?>
      <div class="d-flex justify-between align-center mt-3" style="flex-wrap: wrap; gap: 1rem;">
        <span class="text-muted" style="font-size: 0.85rem;">
          Halaman <strong><?= $cur ?></strong> dari <strong><?= $last ?></strong> (Total <strong><?= number_format($meta['total'] ?? 0, 0, ',', '.') ?></strong> data barang)
        </span>
        <div class="pagination" style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
          <?php if ($cur > 1): ?>
            <?php $queryParams['page'] = 1; ?>
            <a href="index.php?<?= http_build_query($queryParams) ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Halaman Pertama">&laquo;</a>
            <?php $queryParams['page'] = $cur - 1; ?>
            <a href="index.php?<?= http_build_query($queryParams) ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Sebelumnya">&lsaquo;</a>
          <?php endif; ?>

          <?php
            $startPage = max(1, $cur - 3);
            $endPage = min($last, $cur + 3);
            if ($startPage > 1) {
              $queryParams['page'] = 1;
              echo '<a href="index.php?' . http_build_query($queryParams) . '" class="btn btn-outline btn-sm" style="padding: 4px 10px;">1</a>';
              if ($startPage > 2) echo '<span class="text-muted" style="padding: 0 4px;">...</span>';
            }
            for ($p = $startPage; $p <= $endPage; $p++) {
              $queryParams['page'] = $p;
              $activeStyle = ($p == $cur) ? 'background-color: var(--primary); color: #fff; border-color: var(--primary); font-weight: bold;' : '';
              echo '<a href="index.php?' . http_build_query($queryParams) . '" class="btn btn-outline btn-sm" style="padding: 4px 10px; ' . $activeStyle . '">' . $p . '</a>';
            }
            if ($endPage < $last) {
              if ($endPage < $last - 1) echo '<span class="text-muted" style="padding: 0 4px;">...</span>';
              $queryParams['page'] = $last;
              echo '<a href="index.php?' . http_build_query($queryParams) . '" class="btn btn-outline btn-sm" style="padding: 4px 10px;">' . $last . '</a>';
            }
          ?>

          <?php if ($cur < $last): ?>
            <?php $queryParams['page'] = $cur + 1; ?>
            <a href="index.php?<?= http_build_query($queryParams) ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Selanjutnya">&rsaquo;</a>
            <?php $queryParams['page'] = $last; ?>
            <a href="index.php?<?= http_build_query($queryParams) ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Halaman Terakhir">&raquo;</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
