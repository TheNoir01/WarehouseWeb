<?php
$pageTitle = 'Pengeluaran Barang Keluar (OUT)';
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
  <div class="card-header">
    <span><i class="bi bi-box-arrow-up"></i> Riwayat Dokumen Barang Keluar</span>
    <a href="<?= url('issues/create') ?>" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-circle"></i> Pengeluaran Barang Baru
    </a>
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
        <select name="status" class="form-select">
          <option value="">Semua Status</option>
          <option value="open" <?= ($_GET['status'] ?? '') === 'open' ? 'selected' : '' ?>>OPEN (Belum Kembali)</option>
          <option value="partially_returned" <?= ($_GET['status'] ?? '') === 'partially_returned' ? 'selected' : '' ?>>PARTIAL RETURN</option>
          <option value="fully_returned" <?= ($_GET['status'] ?? '') === 'fully_returned' ? 'selected' : '' ?>>SELESAI KEMBALI</option>
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
            <th class="text-right">Aksi</th>
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
                <td><span class="badge badge-primary"><?= htmlspecialchars($issue['company']['code'] ?? '-') ?></span></td>
                <td><strong style="color: #1e293b;"><?= htmlspecialchars($issue['project_name']) ?></strong></td>
                <td><?= htmlspecialchars($issue['requester_name']) ?></td>
                <td><?= htmlspecialchars($issue['recipient_name']) ?></td>
                <td><?= renderBadge($issue['status']) ?></td>
                <td><span class="badge badge-secondary"><?= $issue['items_count'] ?? count($issue['items'] ?? []) ?> Item</span></td>
                <td class="text-right">
                  <a href="<?= url('issues/show') ?>&id=<?= $issue['id'] ?>" class="btn btn-outline btn-sm">Detail & Tracking</a>
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
            <a href="<?= url('issues') ?>&page=<?= $p ?>&search=<?= urlencode($_GET['search'] ?? '') ?>&company_id=<?= urlencode($_GET['company_id'] ?? '') ?>" class="<?= ($meta['current_page'] == $p) ? 'active' : '' ?>">
              <span><?= $p ?></span>
            </a>
          <?php endfor; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
