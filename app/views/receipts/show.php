<?php
$pageTitle = 'Detail Penerimaan: ' . ($receipt['receipt_number'] ?? '');
include __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-between align-center mb-2">
  <div>
    <h1 style="font-size: 1.35rem; font-weight: 700; color: #0f172a;">
      Dokumen Penerimaan: <?= htmlspecialchars($receipt['receipt_number'] ?? '') ?>
    </h1>
    <span class="badge badge-primary"><?= htmlspecialchars($receipt['company']['code'] ?? '') ?></span>
    <span class="text-muted" style="margin-left: 0.5rem; font-size: 0.85rem;">
      Tanggal: <?= formatDate($receipt['received_date']) ?>
    </span>
  </div>
  <div class="d-flex gap-1">
    <button onclick="window.print()" class="btn btn-outline btn-sm">
      <i class="bi bi-printer"></i> Cetak Bukti
    </button>
    <a href="<?= url('receipts') ?>" class="btn btn-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <span>Informasi Dokumen Penerimaan</span>
    <span class="badge badge-success"><?= strtoupper($receipt['status'] ?? 'COMPLETED') ?></span>
  </div>
  <div class="card-body">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; background: #f8fafc; padding: 1rem; border-radius: 8px;">
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">PT Pemilik Stok</div>
        <div class="fw-bold"><?= htmlspecialchars($receipt['company']['name'] ?? '-') ?></div>
      </div>
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">Supplier</div>
        <div class="fw-bold"><?= htmlspecialchars($receipt['supplier']['name'] ?? $receipt['supplier_name'] ?? '-') ?></div>
      </div>
      <?php if (!empty($receipt['delivery_order_number'])): ?>
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">No. Surat Jalan Vendor</div>
        <div class="fw-bold" style="font-family: monospace;"><?= htmlspecialchars($receipt['delivery_order_number']) ?></div>
      </div>
      <?php endif; ?>
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">Petugas Gudang Penerima</div>
        <div class="fw-bold"><?= htmlspecialchars($receipt['received_by']['name'] ?? '-') ?></div>
      </div>
    </div>

    <?php if (!empty($receipt['notes'])): ?>
      <div style="margin-bottom: 1.25rem;">
        <span class="text-muted" style="font-size: 0.85rem;">Catatan:</span>
        <p><?= nl2br(htmlspecialchars($receipt['notes'])) ?></p>
      </div>
    <?php endif; ?>

    <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.75rem;">Daftar Barang Masuk:</h3>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 5%;">No</th>
            <th>ID Barang</th>
            <th>Nama Barang & Spesifikasi</th>
            <th>Lokasi Rak Gudang</th>
            <th class="text-right">Kuantitas</th>
            <th>Satuan</th>
            <th>Kondisi Fisik</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($receipt['items'] ?? [] as $idx => $itemRow): ?>
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
                +<?= formatQty($itemRow['qty']) ?>
              </td>
              <td><?= htmlspecialchars($itemRow['item']['unit']['code'] ?? '-') ?></td>
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

    <!-- Lampiran Dokumentasi -->
    <?php if (!empty($receipt['attachments'])): ?>
      <div style="margin-top: 1.5rem;">
        <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.75rem;">Dokumentasi Barang:</h3>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
          <?php foreach ($receipt['attachments'] as $att): ?>
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
