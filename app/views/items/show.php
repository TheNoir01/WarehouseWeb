<?php
$pageTitle = 'Detail Barang: ' . ($item['name'] ?? '');
include __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-between align-center mb-2">
  <div>
    <h1 style="font-size: 1.4rem; font-weight: 700; color: #0f172a;"><?= htmlspecialchars($item['name']) ?></h1>
    <span class="badge badge-primary"><?= htmlspecialchars($item['company']['name'] ?? '') ?></span>
    <span style="font-family: monospace; font-weight: bold; margin-left: 0.5rem;"><?= htmlspecialchars($item['item_code']) ?></span>
  </div>
  <div class="d-flex gap-1">
    <a href="<?= url('items') ?>" class="btn btn-outline btn-sm">Kembali</a>
  </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
  <!-- Kolom Kiri: Profil & QR Code -->
  <div>
    <!-- Info Profil Barang -->
    <div class="card">
      <div class="card-header">
        <span>Informasi Barang</span>
        <?= renderBadge($item['stock_status']) ?>
      </div>
      <div class="card-body">
        <div style="text-align: center; padding: 1rem 0; background: #f8fafc; border-radius: 8px; margin-bottom: 1.25rem;">
          <div class="text-muted" style="font-size: 0.85rem;">Total Saldo Stok Gudang</div>
          <div style="font-size: 2.2rem; font-weight: 800; color: var(--primary);">
            <?= formatQty($item['total_stock'], $item['unit']['code'] ?? '') ?>
          </div>
          <div style="font-size: 0.8rem; color: #64748b;">
            Batas Minimum: <?= formatQty($item['minimum_stock'], $item['unit']['code'] ?? '') ?>
          </div>
        </div>

        <table class="table" style="font-size: 0.85rem;">
          <tr>
            <td class="text-muted" style="width: 40%;">PT Pemilik</td>
            <td class="fw-bold"><?= htmlspecialchars($item['company']['code'] . ' - ' . $item['company']['name']) ?></td>
          </tr>
          <tr>
            <td class="text-muted">Kategori</td>
            <td><?= htmlspecialchars($item['category']['name'] ?? '-') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Satuan</td>
            <td><?= htmlspecialchars($item['unit']['code'] . ' (' . ($item['unit']['name'] ?? '') . ')') ?></td>
          </tr>
          <tr>
            <td class="text-muted">QR Identifier</td>
            <td>
              <?php if (!empty($item['qr_code'])): ?>
                <span style="font-family: monospace; font-size: 0.8rem; background: #e2e8f0; padding: 2px 6px; border-radius: 4px;">
                  <?= htmlspecialchars($item['qr_code']) ?>
                </span>
              <?php else: ?>
                <span style="font-family: monospace; font-size: 0.8rem; background: #e2e8f0; padding: 2px 6px; border-radius: 4px;">
                  <?= htmlspecialchars($item['item_code']) ?>
                </span>
                <span class="text-muted" style="font-size: 0.75rem;">(Default ID)</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php if (!empty($item['specification'])): ?>
            <tr>
              <td class="text-muted">Spesifikasi</td>
              <td><?= nl2br(htmlspecialchars($item['specification'])) ?></td>
            </tr>
          <?php endif; ?>
          <?php 
            $suppInfo = !empty($item['suppliers_summary']) && $item['suppliers_summary'] !== '-' 
              ? $item['suppliers_summary'] 
              : '';
            if (empty($suppInfo) && !empty($item['description']) && preg_match('/Supplier\s*:\s*([^|\n]+)/i', $item['description'], $sm)) {
              $suppInfo = trim($sm[1]);
            }
          ?>
          <?php if (!empty($suppInfo)): ?>
            <tr>
              <td class="text-muted"><i class="bi bi-truck me-1"></i> Riwayat Supplier</td>
              <td>
                <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.85rem; padding: 0.3rem 0.6rem;">
                  <?= htmlspecialchars($suppInfo) ?>
                </span>
              </td>
            </tr>
          <?php endif; ?>
          <?php if (!empty($item['description'])): ?>
            <tr>
              <td class="text-muted"><i class="bi bi-card-text me-1"></i> Keterangan</td>
              <td><?= nl2br(htmlspecialchars($item['description'])) ?></td>
            </tr>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <!-- KARTU QR CODE SIAP SCAN & CETAK -->
    <?php 
      $qrCodeValue = !empty($item['qr_code']) ? $item['qr_code'] : $item['item_code']; 
    ?>
    <div class="card" style="border-top: 4px solid #10b981;">
      <div class="card-header d-flex justify-between align-center">
        <span><i class="bi bi-qr-code"></i> QR Code Barang (Siap Scan)</span>
        <span class="badge badge-success">Aktif</span>
      </div>
      <div class="card-body text-center">
        <div id="printable-qr-area" style="display: inline-block; padding: 14px; background: #ffffff; border: 2px solid #cbd5e1; border-radius: 10px; margin-bottom: 0.75rem;">
          <div style="font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 2px;">
            [<?= htmlspecialchars($item['company']['code'] ?? '') ?>] <?= htmlspecialchars($item['company']['name'] ?? '') ?>
          </div>
          <div style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">
            <?= htmlspecialchars($item['name']) ?>
          </div>
          <div id="item-qrcode" style="display: flex; justify-content: center; margin: 8px auto;"></div>
          <div style="font-family: monospace; font-size: 0.95rem; font-weight: 800; color: #1e293b; letter-spacing: 0.05em; margin-top: 6px;">
            <?= htmlspecialchars($qrCodeValue) ?>
          </div>
          <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
            ID: <?= htmlspecialchars($item['item_code']) ?> | Satuan: <?= htmlspecialchars($item['unit']['code'] ?? '-') ?>
          </div>
        </div>

        <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 1rem;">
          Dapat di-scan langsung menggunakan kamera HP / Scanner barcode 2D untuk identifikasi barang dan transaksi penerimaan / pengeluaran.
        </div>

        <div class="d-flex justify-center gap-1">
          <button type="button" class="btn btn-primary btn-sm" onclick="printQrLabel()">
            <i class="bi bi-printer"></i> Cetak Stiker Label QR
          </button>
          <button type="button" class="btn btn-outline btn-sm" onclick="downloadQrImage()">
            <i class="bi bi-download"></i> Unduh Gambar QR
          </button>
        </div>
      </div>
    </div>
  </div>

  <div>

    <!-- Histori Mutasi / Pergerakan Barang -->
    <div class="card">
      <div class="card-header">
        <span><i class="bi bi-clock-history"></i> Histori Mutasi Barang (Audit Pergerakan)</span>
      </div>
      <div class="table-responsive">
        <table class="table" style="font-size: 0.85rem;">
          <thead>
            <tr>
              <th>Waktu</th>
              <th>Jenis</th>
              <th>No Referensi</th>
              <th>Lokasi</th>
              <th class="text-right">Perubahan</th>
              <th class="text-right">Saldo Akhir</th>
              <th>Operator</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($item['stock_movements'])): ?>
              <tr><td colspan="7" class="text-center text-muted">Belum ada riwayat mutasi untuk barang ini.</td></tr>
            <?php else: ?>
              <?php foreach ($item['stock_movements'] as $mov): ?>
                <tr>
                  <td><?= formatDateTime($mov['created_at']) ?></td>
                  <td><?= renderBadge($mov['movement_type']) ?></td>
                  <td style="font-family: monospace;"><?= htmlspecialchars($mov['reference_number'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($mov['warehouse_location_id'] ?? '') ?></td>
                  <td class="text-right fw-bold <?= (float)$mov['qty'] > 0 ? 'text-success' : 'text-danger' ?>">
                    <?= ((float)$mov['qty'] > 0 ? '+' : '') . formatQty($mov['qty']) ?>
                  </td>
                  <td class="text-right fw-bold"><?= formatQty($mov['balance_after']) ?></td>
                  <td><?= htmlspecialchars($mov['user']['name'] ?? 'User') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
let qrGenerator = null;
document.addEventListener('DOMContentLoaded', function() {
  const qrContainer = document.getElementById('item-qrcode');
  if (qrContainer && typeof QRCode !== 'undefined') {
    qrGenerator = new QRCode(qrContainer, {
      text: "<?= addslashes($qrCodeValue) ?>",
      width: 140,
      height: 140,
      colorDark : "#0f172a",
      colorLight : "#ffffff",
      correctLevel : QRCode.CorrectLevel.H
    });
  }
});

function printQrLabel() {
  window.print();
}

function downloadQrImage() {
  const img = document.querySelector('#item-qrcode img');
  const canvas = document.querySelector('#item-qrcode canvas');
  let dataUrl = '';
  if (img && img.src && img.src.startsWith('data:')) {
    dataUrl = img.src;
  } else if (canvas) {
    dataUrl = canvas.toDataURL('image/png');
  }
  if (!dataUrl) {
    alert('QR code belum selesai di-generate.');
    return;
  }
  const a = document.createElement('a');
  a.href = dataUrl;
  a.download = 'QR-<?= htmlspecialchars($item['item_code']) ?>.png';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
