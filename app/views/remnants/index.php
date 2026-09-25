<?php
$pageTitle = 'Pelacakan Sisa Material';
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
  <div class="card-header">
    <span><i class="bi bi-scissors me-2"></i>Daftar Material Sisa Potongan</span>
    <span class="text-muted" style="font-size: 0.8rem;">Terhubung langsung ke barang induk & transaksi keluar</span>
  </div>

  <div class="card-body">
    <!-- Filter -->
    <form method="GET" action="index.php" class="filter-bar">
      <input type="hidden" name="r" value="remnants">

      <div style="flex: 1; min-width: 180px;">
        <input type="text" name="search" class="form-control" placeholder="Cari kode sisa, dimensi, barang asal..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      </div>

      <div style="min-width: 160px;">
        <select name="company_id" class="form-select">
          <option value="">-- Semua PT --</option>
          <?php foreach ($companies as $comp): ?>
            <option value="<?= $comp['id'] ?>" <?= ($_GET['company_id'] ?? '') == $comp['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($comp['code'] . ' - ' . $comp['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="min-width: 140px;">
        <select name="status" class="form-select">
          <option value="">-- Semua Status --</option>
          <option value="available" <?= ($_GET['status'] ?? '') === 'available' ? 'selected' : '' ?>>Tersedia (Available)</option>
          <option value="used" <?= ($_GET['status'] ?? '') === 'used' ? 'selected' : '' ?>>Sudah Dipakai</option>
          <option value="scrapped" <?= ($_GET['status'] ?? '') === 'scrapped' ? 'selected' : '' ?>>Afval / Scrap</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
      <a href="<?= url('remnants') ?>" class="btn btn-outline btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
    </form>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Kode Sisa Material</th>
            <th>PT Pemilik</th>
            <th>Barang Asal (Induk)</th>
            <th>Kondisi Bentuk</th>
            <th>Ukuran / Dimensi</th>
            <th>Estimasi Luas / Berat</th>
            <th>Lokasi Simpan</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($remnants)): ?>
            <tr><td colspan="9" class="text-center text-muted" style="padding: 2rem;">Belum ada material sisa yang tercatat.</td></tr>
          <?php else: ?>
            <?php foreach ($remnants as $rem): ?>
              <tr>
                <td class="fw-bold" style="font-family: monospace; color: var(--primary);">
                  <a href="<?= url('remnants/show') ?>&id=<?= $rem['id'] ?>">
                    <?= htmlspecialchars($rem['remnant_code']) ?>
                  </a>
                </td>
                <td><span class="badge badge-primary"><?= htmlspecialchars($rem['company']['code'] ?? '-') ?></span></td>
                <td>
                  <a href="<?= url('items/show') ?>&id=<?= $rem['parent_item']['id'] ?? '' ?>" class="fw-bold">
                    <?= htmlspecialchars($rem['parent_item']['name'] ?? '-') ?>
                  </a>
                  <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($rem['parent_item']['item_code'] ?? '') ?></div>
                </td>
                <td><?= htmlspecialchars($rem['shape_condition']) ?></td>
                <td class="fw-bold"><?= htmlspecialchars($rem['dimension_description']) ?></td>
                <td style="font-size: 0.85rem;">
                  <?php if (!empty($rem['estimated_area'])): ?>
                    <div><?= $rem['estimated_area'] ?> m²</div>
                  <?php endif; ?>
                  <?php if (!empty($rem['estimated_weight'])): ?>
                    <div><?= $rem['estimated_weight'] ?> kg</div>
                  <?php endif; ?>
                  <?php if (empty($rem['estimated_area']) && empty($rem['estimated_weight'])): ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge badge-secondary">
                    <?= htmlspecialchars($rem['location']['code'] ?? '-') ?>
                  </span>
                </td>
                <td>
                  <span class="badge <?= ($rem['status'] ?? 'available') === 'available' ? 'badge-success' : 'badge-secondary' ?>">
                    <?= strtoupper($rem['status'] ?? 'available') ?>
                  </span>
                </td>
                <td class="text-right">
                  <a href="<?= url('remnants/show') ?>&id=<?= $rem['id'] ?>" class="btn btn-outline btn-sm">Trace Sisa</a>
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
          Halaman <?= $meta['current_page'] ?> dari <?= $meta['last_page'] ?> (Total <?= $meta['total'] ?> material sisa)
        </span>
        <div class="pagination">
          <?php for ($p = 1; $p <= $meta['last_page']; $p++): ?>
            <a href="<?= url('remnants') ?>&page=<?= $p ?>&search=<?= urlencode($_GET['search'] ?? '') ?>&company_id=<?= urlencode($_GET['company_id'] ?? '') ?>" class="<?= ($meta['current_page'] == $p) ? 'active' : '' ?>">
              <span><?= $p ?></span>
            </a>
          <?php endfor; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
