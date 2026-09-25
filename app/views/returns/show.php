<?php
$pageTitle = 'Detail Pengembalian: ' . ($return['return_number'] ?? '');
include __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-between align-center mb-2">
  <div>
    <h1 style="font-size: 1.35rem; font-weight: 700; color: #0f172a;">
      Dokumen Pengembalian: <?= htmlspecialchars($return['return_number'] ?? '') ?>
    </h1>
    <span class="badge badge-primary"><?= htmlspecialchars($return['company']['code'] ?? '') ?></span>
    <span class="text-muted" style="margin-left: 0.5rem; font-size: 0.85rem;">
      Tanggal: <?= formatDate($return['returned_date']) ?>
    </span>
  </div>
  <div class="d-flex gap-1">
    <button onclick="window.print()" class="btn btn-outline btn-sm"><i class="bi bi-printer me-1"></i>Cetak</button>
    <a href="<?= url('returns') ?>" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <span>Informasi Pengembalian Barang (Stock Return)</span>
    <span class="badge badge-success">DITERIMA GUDANG</span>
  </div>
  <div class="card-body">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; background: #f8fafc; padding: 1rem; border-radius: 8px;">
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">Referensi Transaksi OUT</div>
        <div class="fw-bold" style="font-family: monospace;">
          <a href="<?= url('issues/show') ?>&id=<?= $return['stock_issue']['id'] ?? '' ?>">
            <?= htmlspecialchars($return['stock_issue']['issue_number'] ?? '-') ?>
          </a>
        </div>
      </div>
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">Proyek Asal</div>
        <div class="fw-bold"><?= htmlspecialchars($return['stock_issue']['project_name'] ?? '-') ?></div>
      </div>
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">Dikembalikan Oleh</div>
        <div class="fw-bold"><?= htmlspecialchars($return['returned_by_name'] ?? '-') ?></div>
      </div>
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">Petugas Gudang Penerima</div>
        <div class="fw-bold"><?= htmlspecialchars($return['received_by']['name'] ?? '-') ?></div>
      </div>
    </div>

    <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.75rem;">Daftar Barang Kembali:</h3>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 5%;">No</th>
            <th>ID Barang</th>
            <th>Nama Barang & Spesifikasi</th>
            <th>Lokasi Simpan Rak</th>
            <th class="text-right">Qty Kembali</th>
            <th>Satuan</th>
            <th>Status Pengembalian</th>
            <th>Kondisi Fisik</th>
            <th>Catatan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($return['items'] ?? [] as $idx => $itemRow): ?>
            <tr>
              <td><?= $idx + 1 ?></td>
              <td style="font-family: monospace; font-weight: 600; color: var(--primary);">
                <?= htmlspecialchars($itemRow['item']['item_code'] ?? '-') ?>
              </td>
              <td>
                <a href="<?= url('items/show') ?>&id=<?= $itemRow['item']['id'] ?? '' ?>" class="fw-bold">
                  <?= htmlspecialchars($itemRow['item']['name'] ?? '-') ?>
                </a>
              </td>
              <td>
                <span class="badge badge-secondary">
                  <?= htmlspecialchars($itemRow['location']['code'] ?? '-') ?>
                </span>
              </td>
              <td class="text-right fw-bold" style="font-size: 1rem; color: var(--success);">
                +<?= formatQty($itemRow['qty_returned']) ?>
              </td>
              <td><?= htmlspecialchars($itemRow['item']['unit']['code'] ?? '-') ?></td>
              <td>
                <span class="badge badge-primary">
                  <?= strtoupper(str_replace('_', ' ', $itemRow['return_status'] ?? 'sisa')) ?>
                </span>
              </td>
              <td>
                <span class="badge <?= ($itemRow['condition'] ?? 'good') === 'good' ? 'badge-success' : 'badge-danger' ?>">
                  <?= strtoupper($itemRow['condition'] ?? 'good') ?>
                </span>
              </td>
              <td class="text-muted"><?= htmlspecialchars($itemRow['notes'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Lampiran Foto Pengembalian -->
    <?php if (!empty($return['attachments'])): ?>
      <div style="margin-top: 1.5rem;">
        <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.75rem;">Foto Bukti Fisik Pengembalian:</h3>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
          <?php foreach ($return['attachments'] as $att): ?>
            <div style="border: 1px solid var(--border); padding: 0.5rem; border-radius: 6px; background: #fff;">
              <a href="<?= htmlspecialchars($att['url']) ?>" target="_blank">
                <img src="<?= htmlspecialchars($att['url']) ?>" style="max-width: 180px; max-height: 140px; object-fit: cover; border-radius: 4px;" alt="Foto">
              </a>
              <div style="font-size: 0.75rem; margin-top: 0.35rem;" class="text-muted">
                <?= htmlspecialchars($att['file_name']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
