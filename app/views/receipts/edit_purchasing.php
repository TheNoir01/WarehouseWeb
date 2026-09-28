<?php
$pageTitle = 'Edit PO & Harga Penerimaan: ' . ($receipt['receipt_number'] ?? '');
include __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-between align-center mb-2">
  <div>
    <h1 style="font-size: 1.35rem; font-weight: 700; color: #0f172a;">
      <i class="bi bi-tag-fill text-primary me-1"></i> Input / Edit No. PO & Harga Barang
    </h1>
    <span class="badge badge-primary"><?= htmlspecialchars($receipt['company']['code'] ?? '') ?></span>
    <span class="text-muted" style="margin-left: 0.5rem; font-size: 0.85rem;">
      Dokumen Penerimaan: <strong style="font-family: monospace;"><?= htmlspecialchars($receipt['receipt_number'] ?? '') ?></strong>
    </span>
  </div>
  <div>
    <a href="<?= url('receipts/show') ?>&id=<?= $receipt['id'] ?>" class="btn btn-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> Kembali ke Detail
    </a>
  </div>
</div>

<!-- FIFO Protection Alert -->
<div class="alert alert-info d-flex align-center gap-1 mb-2" style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; border-radius: 8px; padding: 0.9rem 1.2rem;">
  <i class="bi bi-shield-lock-fill" style="font-size: 1.5rem; color: #2563eb; flex-shrink: 0;"></i>
  <div style="font-size: 0.875rem;">
    <strong>Perlindungan Integritas FIFO:</strong> Tanggal penerimaan barang (<strong><?= formatDate($receipt['received_date']) ?></strong>) dan batch inventori dikunci secara permanen. Pengubahan nomor PO dan harga barang <em>sama sekali tidak akan mengubah</em> tanggal masuk maupun urutan FIFO pengeluaran stok di gudang.
  </div>
</div>

<form method="POST" action="<?= url('receipts/update-purchasing') ?>" id="purchasingForm">
  <input type="hidden" name="receipt_id" value="<?= $receipt['id'] ?>">

  <div class="card mb-2">
    <div class="card-header">
      <span>Informasi Dokumen & Nomor PO</span>
    </div>
    <div class="card-body">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label class="form-label text-muted" style="font-size: 0.8rem;">No. Dokumen Penerimaan</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($receipt['receipt_number']) ?>" readonly style="background: #f1f5f9; font-family: monospace; font-weight: 600;">
        </div>

        <div>
          <label class="form-label text-muted" style="font-size: 0.8rem;">Tanggal Masuk (Terkunci FIFO)</label>
          <div class="input-group" style="display: flex;">
            <input type="text" class="form-control" value="<?= formatDate($receipt['received_date']) ?>" readonly style="background: #f1f5f9; font-weight: 500;">
            <span style="background: #e2e8f0; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-left: none; border-radius: 0 6px 6px 0; color: #64748b;" title="Tanggal terkunci untuk kepatuhan FIFO">
              <i class="bi bi-lock-fill"></i>
            </span>
          </div>
        </div>

        <div>
          <label class="form-label text-muted" style="font-size: 0.8rem;">PT Pemilik Stok</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars(($receipt['company']['code'] ?? '') . ' - ' . ($receipt['company']['name'] ?? '')) ?>" readonly style="background: #f1f5f9;">
        </div>

        <div>
          <label class="form-label text-muted" style="font-size: 0.8rem;">Supplier</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($receipt['supplier']['name'] ?? $receipt['supplier_name'] ?? '-') ?>" readonly style="background: #f1f5f9;">
        </div>
      </div>

      <div style="max-width: 500px;">
        <label class="form-label fw-bold" for="po_number" style="color: #0369a1;">
          <i class="bi bi-receipt me-1"></i> Nomor Purchase Order (PO) <span class="text-danger">*</span>
        </label>
        <input type="text" name="po_number" id="po_number" class="form-control" placeholder="Contoh: PO-KJG-2026-09-0012" value="<?= htmlspecialchars($receipt['po_number'] ?? '') ?>" style="font-family: monospace; font-size: 1rem; border-color: #38bdf8;" required autofocus>
        <div class="text-muted mt-1" style="font-size: 0.8rem;">Masukkan nomor referensi PO pembelian untuk dokumen ini.</div>
      </div>
    </div>
  </div>

  <div class="card mb-2">
    <div class="card-header d-flex justify-between align-center">
      <span>Daftar Barang & Input Harga Satuan</span>
      <span class="text-muted" style="font-size: 0.85rem;">Total <?= count($receipt['items'] ?? []) ?> Item</span>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table" id="itemsPricingTable">
          <thead>
            <tr>
              <th style="width: 4%;">No</th>
              <th>Kode & Nama Barang</th>
              <th>Lokasi Rak</th>
              <th class="text-right">Kuantitas</th>
              <th>Satuan</th>
              <th style="width: 200px;" class="text-right">Harga Satuan (Rp)</th>
              <th style="width: 200px;" class="text-right">Total Harga (Rp)</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $initialGrandTotal = 0;
            foreach ($receipt['items'] ?? [] as $idx => $itemRow): 
              $qty = (float) $itemRow['qty'];
              $unitPrice = (float) ($itemRow['unit_price'] ?? 0);
              $totalPrice = (float) ($itemRow['total_price'] ?? ($qty * $unitPrice));
              $initialGrandTotal += $totalPrice;
            ?>
              <tr>
                <td><?= $idx + 1 ?></td>
                <td>
                  <input type="hidden" name="items[<?= $idx ?>][id]" value="<?= $itemRow['id'] ?>">
                  <input type="hidden" name="items[<?= $idx ?>][qty]" class="item-qty" value="<?= $qty ?>">
                  <div class="fw-bold" style="color: #0f172a;"><?= htmlspecialchars($itemRow['item']['name'] ?? '-') ?></div>
                  <div style="font-family: monospace; font-size: 0.8rem; color: #64748b;">
                    <?= htmlspecialchars($itemRow['item']['item_code'] ?? '-') ?>
                  </div>
                </td>
                <td>
                  <span class="badge badge-secondary"><?= htmlspecialchars($itemRow['location']['code'] ?? '-') ?></span>
                </td>
                <td class="text-right fw-bold" style="color: #059669; font-size: 0.95rem;">
                  <?= formatQty($qty) ?>
                </td>
                <td><?= htmlspecialchars($itemRow['item']['unit']['code'] ?? '-') ?></td>
                <td>
                  <div class="input-group" style="display: flex;">
                    <span style="background: #f8fafc; padding: 0.4rem 0.6rem; border: 1px solid var(--border); border-right: none; border-radius: 6px 0 0 6px; font-size: 0.85rem; color: #64748b;">Rp</span>
                    <input type="number" step="any" min="0" name="items[<?= $idx ?>][unit_price]" 
                           class="form-control text-right item-unit-price" 
                           value="<?= $unitPrice > 0 ? $unitPrice : '' ?>" 
                           placeholder="0"
                           style="border-radius: 0 6px 6px 0; font-family: monospace; font-weight: 600;" 
                           data-idx="<?= $idx ?>"
                           oninput="calculateRow(<?= $idx ?>)" required>
                  </div>
                </td>
                <td class="text-right fw-bold" style="font-family: monospace; font-size: 1rem; color: #0284c7; vertical-align: middle;">
                  <span id="row-total-display-<?= $idx ?>">
                    <?= $totalPrice > 0 ? formatRupiah($totalPrice) : 'Rp 0' ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr style="background: #f8fafc; border-top: 2px solid var(--border);">
              <td colspan="5" class="text-right fw-bold" style="font-size: 1rem; vertical-align: middle;">
                Grand Total Pembelian:
              </td>
              <td colspan="2" class="text-right fw-bold" style="font-family: monospace; font-size: 1.2rem; color: #0284c7; vertical-align: middle;">
                <span id="grandTotalDisplay"><?= formatRupiah($initialGrandTotal) ?></span>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <div class="d-flex justify-between align-center mt-3 mb-4">
    <a href="<?= url('receipts/show') ?>&id=<?= $receipt['id'] ?>" class="btn btn-secondary">
      <i class="bi bi-x-circle me-1"></i> Batal
    </a>
    <button type="submit" class="btn btn-primary" style="background-color: #0284c7; border-color: #0284c7; padding: 0.6rem 1.5rem; font-weight: 600; font-size: 1rem;">
      <i class="bi bi-check2-circle me-1"></i> Simpan Nomor PO & Harga
    </button>
  </div>
</form>

<script>
function formatRupiahJs(number) {
  return 'Rp ' + Number(number).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function calculateRow(idx) {
  const row = document.querySelectorAll('#itemsPricingTable tbody tr')[idx];
  if (!row) return;

  const qtyInput = row.querySelector('.item-qty');
  const priceInput = row.querySelector('.item-unit-price');
  const displaySpan = document.getElementById('row-total-display-' + idx);

  const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
  const unitPrice = parseFloat(priceInput ? priceInput.value : 0) || 0;

  const subtotal = Math.round(qty * unitPrice * 100) / 100;
  if (displaySpan) {
    displaySpan.textContent = formatRupiahJs(subtotal);
  }

  calculateGrandTotal();
}

function calculateGrandTotal() {
  const rows = document.querySelectorAll('#itemsPricingTable tbody tr');
  let grandTotal = 0;

  rows.forEach(row => {
    const qtyInput = row.querySelector('.item-qty');
    const priceInput = row.querySelector('.item-unit-price');
    const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
    const unitPrice = parseFloat(priceInput ? priceInput.value : 0) || 0;
    grandTotal += (qty * unitPrice);
  });

  const grandTotalSpan = document.getElementById('grandTotalDisplay');
  if (grandTotalSpan) {
    grandTotalSpan.textContent = formatRupiahJs(Math.round(grandTotal * 100) / 100);
  }
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
