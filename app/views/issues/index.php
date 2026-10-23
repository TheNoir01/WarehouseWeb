<?php
$pageTitle = 'Pengeluaran Barang Keluar';
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
  <div class="card-header d-flex justify-between align-center" style="flex-wrap: wrap; gap: 0.75rem;">
    <span><i class="bi bi-box-arrow-up"></i> Riwayat Dokumen Barang Keluar</span>
    <div class="d-flex gap-1 align-center" style="flex-wrap: wrap;">
      <a href="<?= url('issues/export-excel') ?>" class="btn btn-outline btn-sm" style="font-weight: 600; color: #16a34a; border-color: #16a34a;" title="Ekspor seluruh data barang keluar ke 1 file Excel multi-sheet terpisah per PT & per Bulan">
        <i class="bi bi-file-earmark-excel me-1"></i>Ekspor Excel (.xlsx)
      </a>
      <a href="<?= url('issues/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> Pengeluaran Barang Baru
      </a>
    </div>
  </div>

  <div class="card-body">
    <!-- Filter -->
    <form method="GET" action="index.php" class="filter-bar">
      <input type="hidden" name="r" value="issues">

      <div style="flex: 1; min-width: 180px;">
        <input type="text" name="search" class="form-control" placeholder="Cari no OUT, proyek, penerima..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      </div>

      <div style="min-width: 160px;">
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
        <select name="status" class="form-select" onchange="this.form.submit()">
          <option value="">Semua Status</option>
          <option value="Belum Kembali" <?= in_array($_GET['status'] ?? '', ['Belum Kembali', 'open']) ? 'selected' : '' ?>>Belum Kembali</option>
          <option value="Sebagian Kembali" <?= in_array($_GET['status'] ?? '', ['Sebagian Kembali', 'partially_returned']) ? 'selected' : '' ?>>Sebagian Kembali</option>
          <option value="Sudah Kembali" <?= in_array($_GET['status'] ?? '', ['Sudah Kembali', 'fully_returned']) ? 'selected' : '' ?>>Sudah Kembali</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-sm">Filter</button>
      <a href="<?= url('issues') ?>" class="btn btn-outline btn-sm">Reset</a>
    </form>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>No. Dokumen</th>
            <th>Tanggal</th>
            <th>PT Pemilik</th>
            <th>Keperluan / Proyek</th>
            <th>Divisi</th>
            <th>Nama Pengambil</th>
            <th>Status Lapangan</th>
            <th>Jml Item</th>
            <th class="text-right" style="white-space: nowrap; width: 1%;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($issues)): ?>
            <tr><td colspan="9" class="text-center text-muted" style="padding: 2rem;">Belum ada dokumen pengeluaran barang.</td></tr>
          <?php else: ?>
            <?php foreach ($issues as $issue): ?>
              <tr>
                <td class="fw-bold" style="font-family: monospace; color: var(--primary);">
                  <a href="<?= url('issues/show') ?>&id=<?= $issue['id'] ?>">
                    <?= htmlspecialchars($issue['issue_number']) ?>
                  </a>
                </td>
                <td><?= formatDate($issue['issued_date']) ?></td>
                <td><?= renderCompanyBadge($issue['company']['code'] ?? '-') ?></td>
                <td><strong style="color: #1e293b;"><?= htmlspecialchars($issue['project_name']) ?></strong></td>
                <td><?= htmlspecialchars($issue['requester_name']) ?></td>
                <td><?= htmlspecialchars($issue['recipient_name']) ?></td>
                <td><?= renderBadge($issue['status']) ?></td>
                <td><span class="badge badge-secondary"><?= $issue['items_count'] ?? count($issue['items'] ?? []) ?> Item</span></td>
                <td class="text-right" style="white-space: nowrap;">
                  <div class="action-buttons">
                    <a href="<?= url('issues/show') ?>&id=<?= $issue['id'] ?>" class="btn-action-view" title="Lihat Detail & Tracking Pengeluaran">
                      <i class="bi bi-eye me-1"></i>Detail
                    </a>
                  </div>
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
            <a href="<?= url('issues') ?>&page=<?= $p ?>&search=<?= urlencode($_GET['search'] ?? '') ?>&company_id=<?= urlencode($_GET['company_id'] ?? '') ?>&status=<?= urlencode($_GET['status'] ?? '') ?>" class="<?= ($meta['current_page'] == $p) ? 'active' : '' ?>">
              <span><?= $p ?></span>
            </a>
          <?php endfor; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
