<?php
$pageTitle = 'Penerimaan Barang Masuk';
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
  <div class="card-header d-flex justify-between align-center">
    <span><i class="bi bi-box-arrow-in-down me-1"></i>Riwayat Dokumen Barang Masuk</span>
    <div class="d-flex gap-1 align-center">
      <a href="<?= url('receipts/export-excel') ?>" class="btn btn-outline btn-sm" style="font-weight: 600; color: #16a34a; border-color: #16a34a;" title="Ekspor seluruh data barang masuk ke 1 file Excel multi-sheet terpisah per PT & per Bulan">
        <i class="bi bi-file-earmark-excel me-1"></i>Ekspor Excel (.xlsx)
      </a>
      <?php if (canManageMaster()): ?>
        <a href="<?= url('receipts/create') ?>&company=KJG" class="btn btn-primary btn-sm" style="font-weight: 600;">
          <i class="bi bi-box-arrow-in-down me-1"></i>Barang Masuk PT KJG
        </a>
        <a href="<?= url('receipts/create') ?>&company=LNP" class="btn btn-success btn-sm" style="font-weight: 600; background-color: #0d9488; border-color: #0d9488;">
          <i class="bi bi-box-arrow-in-down me-1"></i>Barang Masuk PT LNP
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="card-body">
    <!-- Filter -->
    <form method="GET" action="<?= url('receipts') ?>" class="filter-bar">
      <input type="hidden" name="r" value="receipts">
      <div style="flex: 2;">
        <input type="text" name="search" class="form-control" placeholder="Cari no penerimaan, supplier..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      </div>
      <div style="flex: 1;">
        <select name="company_id" class="form-select">
          <option value="">Semua PT</option>
          <?php foreach ($companies as $comp): ?>
            <option value="<?= $comp['id'] ?>" <?= (isset($_GET['company_id']) && $_GET['company_id'] == $comp['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($comp['code'] . ' - ' . $comp['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-sm">Filter</button>
      <a href="<?= url('receipts') ?>" class="btn btn-outline btn-sm">Reset</a>
    </form>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>No. Dokumen</th>
            <th>Tanggal</th>
            <th>PT Pemilik</th>
            <th>Supplier</th>
            <th>Petugas Penerima</th>
            <th>Jml Item</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($receipts)): ?>
            <tr><td colspan="7" class="text-center text-muted" style="padding: 2rem;">Belum ada dokumen penerimaan barang.</td></tr>
          <?php else: ?>
            <?php foreach ($receipts as $gr): ?>
              <tr>
                <td class="fw-bold" style="font-family: monospace; color: var(--primary);">
                  <a href="<?= url('receipts/show') ?>&id=<?= $gr['id'] ?>">
                    <?= htmlspecialchars($gr['receipt_number']) ?>
                  </a>
                </td>
                <td><?= formatDate($gr['received_date']) ?></td>
                <td><span class="badge badge-primary"><?= htmlspecialchars($gr['company']['code'] ?? '-') ?></span></td>
                <td><?= htmlspecialchars($gr['supplier']['name'] ?? $gr['supplier_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($gr['received_by']['name'] ?? '-') ?></td>
                <td><span class="badge badge-secondary"><?= $gr['items_count'] ?? count($gr['items'] ?? []) ?> Item</span></td>
                <td class="text-right">
                  <a href="<?= url('receipts/show') ?>&id=<?= $gr['id'] ?>" class="btn btn-outline btn-sm">Lihat Detail</a>
                </td>
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
          Halaman <?= $meta['current_page'] ?> dari <?= $meta['last_page'] ?> (Total <?= $meta['total'] ?> transaksi)
        </span>
        <div class="pagination">
          <?php for ($p = 1; $p <= $meta['last_page']; $p++): ?>
            <a href="<?= url('receipts') ?>&page=<?= $p ?>&search=<?= urlencode($_GET['search'] ?? '') ?>&company_id=<?= urlencode($_GET['company_id'] ?? '') ?>" class="<?= ($meta['current_page'] == $p) ? 'active' : '' ?>">
              <span><?= $p ?></span>
            </a>
          <?php endfor; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
