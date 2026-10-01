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
  <div class="d-flex gap-1 align-center">
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
            <?php $catMin = (float)($item['category']['minimum_stock'] ?? $item['category_minimum_stock'] ?? $item['minimum_stock'] ?? 0); ?>
            Batas Minimum Kategori: <strong><?= formatQty($catMin, $item['unit']['code'] ?? '') ?></strong>
          </div>
        </div>

        <table class="table" style="font-size: 0.85rem;">
          <tr>
            <td class="text-muted" style="width: 40%;">PT Pemilik</td>
            <td class="fw-bold"><?= htmlspecialchars($item['company']['code'] . ' - ' . $item['company']['name']) ?></td>
          </tr>
          <tr>
            <td class="text-muted">Harga Beli</td>
            <td class="fw-bold" style="font-family: monospace; color: #0284c7; font-size: 0.95rem;">
              <?= (float)($item['purchase_price'] ?? 0) > 0 ? formatRupiah($item['purchase_price']) : '<span class="text-muted" style="font-style: italic; font-weight: normal;">Belum diinput</span>' ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">No. PO</td>
            <td class="fw-bold" style="font-family: monospace; color: #0284c7; font-size: 0.95rem;">
              <?= !empty($item['po_number']) ? htmlspecialchars($item['po_number']) : '<span class="text-muted" style="font-style: italic; font-weight: normal;">Belum diinput</span>' ?>
            </td>
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
              <td class="text-muted">Riwayat Supplier</td>
              <td>
                <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.85rem; padding: 0.3rem 0.6rem;">
                  <?= htmlspecialchars($suppInfo) ?>
                </span>
              </td>
            </tr>
          <?php endif; ?>
          <?php if (!empty($item['description'])): ?>
            <tr>
              <td class="text-muted">Keterangan</td>
              <td><?= nl2br(htmlspecialchars($item['description'])) ?></td>
            </tr>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <!-- KARTU QR CODE SIAP SCAN & CETAK (Khusus Kepala Toko/Gudang & Karyawan) -->
    <?php if (canViewQrCode()): ?>
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
    <?php endif; ?>
  </div>

  <div>

    <!-- Histori Perubahan Harga Beli (Purchasing Log) -->
    <div class="card mb-3">
      <div class="card-header d-flex justify-between align-center">
        <span><i class="bi bi-clock-history" style="color: #0284c7;"></i> Riwayat Perubahan Harga Beli</span>
        <span class="badge" style="background: #e0f2fe; color: #0369a1;"><?= count($item['price_histories'] ?? []) ?> Catatan</span>
      </div>
      <div class="table-responsive">
        <table class="table" style="font-size: 0.85rem;">
          <thead>
            <tr>
              <th>Waktu</th>
              <th>Harga Sebelum</th>
              <th>Harga Sesudah</th>
              <th>Diubah Oleh</th>
              <th>Catatan</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($item['price_histories'])): ?>
              <tr><td colspan="5" class="text-center text-muted" style="padding: 1.5rem;">Belum ada riwayat perubahan harga untuk barang ini.</td></tr>
            <?php else: ?>
              <?php foreach ($item['price_histories'] as $ph): ?>
                <tr>
                  <td><?= formatDateTime($ph['created_at']) ?></td>
                  <td style="font-family: monospace; color: #64748b;">
                    <?= (float)($ph['old_price'] ?? 0) > 0 ? formatRupiah($ph['old_price']) : 'Rp 0' ?>
                  </td>
                  <td style="font-family: monospace; font-weight: 700; color: #0284c7;">
                    <?= formatRupiah($ph['new_price'] ?? 0) ?>
                  </td>
                  <td>
                    <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe;">
                      <?= htmlspecialchars($ph['user']['name'] ?? 'Purchasing') ?>
                    </span>
                  </td>
                  <td>
                    <?= htmlspecialchars($ph['notes'] ?? '-') ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

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

<?php if (canViewQrCode()): ?>
<script>
let qrGenerator = null;
document.addEventListener('DOMContentLoaded', function() {
  const qrContainer = document.getElementById('item-qrcode');
  if (qrContainer && typeof QRCode !== 'undefined') {
    qrGenerator = new QRCode(qrContainer, {
      text: "<?= addslashes($qrCodeValue ?? '') ?>",
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
<?php endif; ?>

<script>
// Purchasing Modal Functions (Khusus Purchasing)
function openPurchasingModal(itemData) {
  document.getElementById('modalItemId').value = itemData.id || '';
  document.getElementById('modalItemName').innerText = itemData.name || '';
  document.getElementById('modalItemCode').innerText = itemData.item_code || '';
  document.getElementById('modalCompanyCode').innerText = itemData.company_code || '';
  document.getElementById('modalItemStock').innerText = 'Stok: ' + (itemData.total_stock || 0) + ' ' + (itemData.unit || '');
  document.getElementById('modalPurchasePrice').value = (itemData.purchase_price && itemData.purchase_price > 0) ? itemData.purchase_price : '';
  const poEl = document.getElementById('modalPoNumber');
  if (poEl) poEl.value = itemData.po_number || '';
  const notesEl = document.getElementById('modalPriceNotes');
  if (notesEl) notesEl.value = '';
  
  const modal = document.getElementById('purchasingModal');
  if (modal) {
    modal.style.display = 'flex';
    setTimeout(() => {
      document.getElementById('modalPurchasePrice').focus();
    }, 100);
  }
}

function closePurchasingModal() {
  const modal = document.getElementById('purchasingModal');
  if (modal) modal.style.display = 'none';
}

// Edit Item Modal Functions (Khusus Role Kepala Gudang, Karyawan, Admin)
function openEditItemModal(param) {
  let itemData = param;
  if (param instanceof HTMLElement || (param && param.dataset && param.dataset.item)) {
    try {
      itemData = typeof param.dataset.item === 'string' ? JSON.parse(param.dataset.item) : param.dataset.item;
    } catch(e) {
      console.error("Failed to parse item data:", e);
      return;
    }
  }
  if (!itemData) return;

  document.getElementById('editItemId').value = itemData.id || '';
  document.getElementById('editItemCodeText').innerText = itemData.item_code || '';
  document.getElementById('editCompanyCodeText').innerText = itemData.company_code || '';
  document.getElementById('editItemName').value = itemData.name || '';
  document.getElementById('editCategoryId').value = itemData.category_id || '';
  document.getElementById('editUnitId').value = itemData.unit_id || '';
  document.getElementById('editMinStock').value = itemData.minimum_stock || 0;
  document.getElementById('editSpecification').value = itemData.specification || '';
  document.getElementById('editDescription').value = itemData.description || '';

  const modal = document.getElementById('editItemModal');
  if (modal) {
    modal.style.display = 'flex';
    setTimeout(() => {
      document.getElementById('editItemName').focus();
    }, 100);
  }
}

function closeEditItemModal() {
  const modal = document.getElementById('editItemModal');
  if (modal) modal.style.display = 'none';
}
</script>

<?php if (isPurchasing()): ?>
<!-- Modal Input / Edit No. PO & Harga (Purchasing) -->
<div id="purchasingModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
  <div style="background: #ffffff; border-radius: 12px; max-width: 520px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden;">
    <div style="padding: 1.2rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
      <div style="font-weight: 700; font-size: 1.1rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
        <i class="bi bi-receipt" style="color: #0284c7;"></i>
        <span>Input / Edit No. PO & Harga (Purchasing)</span>
      </div>
      <button type="button" onclick="closePurchasingModal()" style="background: none; border: none; font-size: 1.5rem; line-height: 1; color: #94a3b8; cursor: pointer;">&times;</button>
    </div>

    <form method="POST" action="<?= url('items/update-purchasing') ?>" id="formPurchasingModal">
      <input type="hidden" name="item_id" id="modalItemId" value="">
      <input type="hidden" name="return_to" value="show"> 
      <div style="padding: 1.25rem 1.5rem;">
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
          <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem; margin-bottom: 0.25rem;" id="modalItemName">-</div>
          <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <span class="badge" style="background: #e2e8f0; color: #1e293b; font-family: monospace;" id="modalItemCode">-</span>
            <span class="badge badge-primary" id="modalCompanyCode">-</span>
            <span class="badge badge-secondary" id="modalItemStock">-</span>
          </div>
        </div>

        <!-- Input No. PO -->
        <div class="form-group mb-3">
          <label class="form-label fw-bold" for="modalPoNumber" style="color: #0369a1; font-size: 0.9rem;">
            <i class="bi bi-receipt me-1"></i> Nomor PO (Purchase Order)
          </label>
          <input type="text" name="po_number" id="modalPoNumber" class="form-control" 
                 placeholder="Contoh: PO-KJG-2026-001" style="font-family: monospace; font-weight: 600; font-size: 0.95rem; border-color: #7dd3fc;" autocomplete="off">
          <small class="text-muted">Nomor PO pembelian barang dari supplier terkait.</small>
        </div>

        <!-- Input Harga Satuan -->
        <div class="form-group mb-3">
          <label class="form-label fw-bold" for="modalPurchasePrice" style="color: #0369a1; font-size: 0.9rem;">
            <i class="bi bi-cash-stack me-1"></i> Harga Beli Satuan
          </label>
          <div class="input-group" style="display: flex;">
            <span style="background: #f1f5f9; padding: 0.45rem 0.75rem; border: 1px solid var(--border); border-right: none; border-radius: 6px 0 0 6px; font-weight: 600; color: #64748b;">Rp</span>
            <input type="number" step="any" min="0" name="purchase_price" id="modalPurchasePrice" class="form-control" 
                   placeholder="0" style="border-radius: 0 6px 6px 0; font-family: monospace; font-weight: 600; font-size: 1rem; border-color: #7dd3fc;" autocomplete="off">
          </div>
        </div>

        <!-- Catatan Perubahan Data Purchasing -->
        <div class="form-group mb-1">
          <label class="form-label fw-bold" for="modalPriceNotes" style="color: #475569; font-size: 0.85rem;">
            <i class="bi bi-journal-text me-1"></i> Catatan (Opsional)
          </label>
          <input type="text" name="notes" id="modalPriceNotes" class="form-control" 
                 placeholder="Contoh: Penyesuaian supplier, update PO baru, dll." style="font-size: 0.85rem;">
          <small class="text-muted">Akan ditampilkan pada tabel riwayat perubahan harga di samping.</small>
        </div>
      </div>
      <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: right; align-items: right;">
        <div style="display: flex; gap: 0.5rem;">
          <button type="button" class="btn btn-secondary btn-sm" onclick="closePurchasingModal()">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm" style="background-color: #0284c7; border-color: #0284c7; font-weight: 600;">
            <i class="bi bi-check-circle me-1"></i> Simpan Data Purchasing
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if (canEditItem()): ?>
<!-- Modal Edit Isi Barang (Khusus Kepala Gudang, Karyawan, Admin) -->
<div id="editItemModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
  <div style="background: #ffffff; border-radius: 12px; max-width: 580px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
    <div style="padding: 1.1rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
      <div style="font-weight: 700; font-size: 1.1rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
        <i class="bi bi-pencil-square" style="color: #2563eb;"></i>
        <span>Koreksi / Edit Data Barang</span>
      </div>
      <button type="button" onclick="closeEditItemModal()" style="background: none; border: none; font-size: 1.5rem; line-height: 1; color: #94a3b8; cursor: pointer;">&times;</button>
    </div>

    <form method="POST" action="<?= url('items/update') ?>" id="formEditItemModal" style="display: flex; flex-direction: column; overflow: hidden; margin: 0;">
      <input type="hidden" name="item_id" id="editItemId" value="">
      <input type="hidden" name="return_to" value="show">
      
      <div style="padding: 1.25rem 1.5rem; overflow-y: auto; flex: 1;">
        <!-- Info Edit Barang -->
        <div style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.825rem; color: #334155; display: flex; gap: 0.75rem; align-items: flex-start;">
          <i class="bi bi-info-circle-fill" style="font-size: 1.2rem; color: #475569; flex-shrink: 0; margin-top: 1px;"></i>
          <div>
            Perbaiki nama atau atribut barang jika terdapat kesalahan penginputan. Penginputan harga beli hanya dapat dilakukan oleh role <strong>Purchasing</strong>.
          </div>
        </div>

        <!-- Profil Ringkas -->
        <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 1rem;">
          <span style="font-size: 0.85rem; color: #64748b;">Kode Barang:</span>
          <span class="badge" style="background: #e2e8f0; color: #1e293b; font-family: monospace; font-size: 0.85rem;" id="editItemCodeText">-</span>
          <span class="badge badge-primary" id="editCompanyCodeText">-</span>
        </div>

        <!-- Nama Barang -->
        <div class="form-group mb-2">
          <label class="form-label fw-bold" for="editItemName">
            Nama Barang <span class="text-danger">*</span>
          </label>
          <input type="text" name="name" id="editItemName" class="form-control" required placeholder="Nama lengkap barang">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.75rem;">
          <!-- Kategori -->
          <div class="form-group mb-0">
            <label class="form-label fw-bold" for="editCategoryId">Kategori</label>
            <select name="category_id" id="editCategoryId" class="form-select">
              <option value="">-- Pilih Kategori --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Satuan -->
          <div class="form-group mb-0">
            <label class="form-label fw-bold" for="editUnitId">Satuan Unit</label>
            <select name="unit_id" id="editUnitId" class="form-select">
              <option value="">-- Pilih Satuan --</option>
              <?php foreach ($units as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['code']) ?> (<?= htmlspecialchars($u['name']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <input type="hidden" name="minimum_stock" id="editMinStock" value="0">
        <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 0.6rem 0.85rem; margin-bottom: 0.75rem; font-size: 0.8rem; color: #64748b;">
          <i class="bi bi-info-circle me-1 text-primary"></i> Batasan stok peringatan (status MENIPIS / HABIS) diatur terpusat di menu <strong>Master Kategori</strong>.
        </div>

        <!-- Spesifikasi / Ukuran -->
        <div class="form-group mb-2">
          <label class="form-label fw-bold" for="editSpecification">Spesifikasi / Dimensi / Ukuran</label>
          <input type="text" name="specification" id="editSpecification" class="form-control" placeholder="Contoh: 1.2mm x 1200 x 2400">
        </div>

        <!-- Deskripsi / Keterangan -->
        <div class="form-group mb-1">
          <label class="form-label fw-bold" for="editDescription">Keterangan Tambahan</label>
          <textarea name="description" id="editDescription" class="form-control" rows="2" placeholder="Catatan opsional mengenai barang ini"></textarea>
        </div>
      </div>

      <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem; align-items: center;">
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeEditItemModal()">Batal</button>
        <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 600;">
          <i class="bi bi-check-circle me-1"></i> Simpan Perubahan Data Barang
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
