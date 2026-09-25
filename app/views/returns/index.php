<?php
$pageTitle = 'Pengembalian Barang Lapangan';
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
  <div class="card-header">
    <span><i class="bi bi-arrow-repeat me-2"></i>Riwayat Dokumen Pengembalian Barang</span>
    <a href="<?= url('returns/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>Input Pengembalian Barang</a>
  </div>

  <div class="card-body">
    <!-- Filter -->
    <form method="GET" action="index.php" class="filter-bar">
      <input type="hidden" name="r" value="returns">

      <div style="flex: 1; min-width: 180px;">
        <input type="text" name="search" class="form-control" placeholder="Cari no return, no OUT, pengembali..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
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

      <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
      <a href="<?= url('returns') ?>" class="btn btn-outline btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
    </form>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>No. Pengembalian</th>
            <th>Referensi No. OUT</th>
            <th>Tanggal Kembali</th>
            <th>PT Pemilik</th>
            <th>Nama Pengembali</th>
            <th>Petugas Penerima</th>
            <th>Jml Item</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($returns)): ?>
            <tr><td colspan="8" class="text-center text-muted" style="padding: 2rem;">Belum ada dokumen pengembalian barang.</td></tr>
          <?php else: ?>
            <?php foreach ($returns as $ret): ?>
              <tr>
                <td class="fw-bold" style="font-family: monospace; color: var(--primary);">
                  <a href="<?= url('returns/show') ?>&id=<?= $ret['id'] ?>">
                    <?= htmlspecialchars($ret['return_number']) ?>
                  </a>
                </td>
                <td style="font-family: monospace;">
                  <a href="<?= url('issues/show') ?>&id=<?= $ret['stock_issue']['id'] ?? '' ?>">
                    <?= htmlspecialchars($ret['stock_issue']['issue_number'] ?? '-') ?>
                  </a>
                </td>
                <td><?= formatDate($ret['returned_date']) ?></td>
                <td><span class="badge badge-primary"><?= htmlspecialchars($ret['company']['code'] ?? '-') ?></span></td>
                <td><?= htmlspecialchars($ret['returned_by_name']) ?></td>
                <td><?= htmlspecialchars($ret['received_by']['name'] ?? '-') ?></td>
                <td><span class="badge badge-secondary"><?= $ret['items_count'] ?? count($ret['items'] ?? []) ?> Item</span></td>
                <td class="text-right">
                  <a href="<?= url('returns/show') ?>&id=<?= $ret['id'] ?>" class="btn btn-outline btn-sm">Lihat Detail</a>
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
          Halaman <?= $meta['current_page'] ?> dari <?= $meta['last_page'] ?> (Total <?= $meta['total'] ?> pengembalian)
        </span>
        <div class="pagination">
          <?php for ($p = 1; $p <= $meta['last_page']; $p++): ?>
            <a href="<?= url('returns') ?>&page=<?= $p ?>&search=<?= urlencode($_GET['search'] ?? '') ?>" class="<?= ($meta['current_page'] == $p) ? 'active' : '' ?>">
              <span><?= $p ?></span>
            </a>
          <?php endfor; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
