<?php
$pageTitle = 'Edit PO & Harga Penerimaan: ' . ($receipt['receipt_number'] ?? '');
include __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-between align-center mb-2">
  <div>
    <h1 style="font-size: 1.35rem; font-weight: 700; color: #0f172a;">
      <i class="bi bi-tag-fill text-primary me-1"></i> Input / Edit No. PO & Harga Barang
    </h1>
    <?= renderCompanyBadge($receipt['company']['code'] ?? '') ?>
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
          <label class="form-label text-muted" style="font-size: 0.8rem;">Tanggal Masuk (Terkunci)</label>
          <div class="input-group" style="display: flex;">
            <input type="text" class="form-control" value="<?= formatDate($receipt['received_date']) ?>" readonly style="background: #f1f5f9; font-weight: 500;">
            <span style="background: #e2e8f0; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-left: none; border-radius: 0 6px 6px 0; color: #64748b;" title="Tanggal terkunci">
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
          </i> Nomor Purchase Order (PO) <span class="text-danger">*</span>
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
              <td colspan="4" class="text-right fw-bold" style="font-size: 1rem; vertical-align: middle; white-space: nowrap; padding: 0.75rem 1rem;">
                Total Pembelian:
              </td>
              <td colspan="2" class="text-right fw-bold" style="font-family: monospace; font-size: 1.2rem; color: #0284c7; vertical-align: middle; white-space: nowrap; padding: 0.75rem 1rem;">
                <span id="grandTotalDisplay"><?= formatRupiah($initialGrandTotal) ?></span>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <!-- Form Action Footer -->
    <div class="card-footer d-flex justify-between align-center" style="background: #f8fafc; border-top: 1px solid var(--border); padding: 0.85rem 1.25rem; border-radius: 0 0 8px 8px;">
      <a href="<?= url('receipts/show') ?>&id=<?= $receipt['id'] ?>" class="btn btn-secondary">
        <i class="bi bi-x-circle me-1"></i> Batal
      </a>
      <button type="submit" class="btn btn-primary" style="background-color: #0284c7; border-color: #0284c7; padding: 0.55rem 1.4rem; font-weight: 600; font-size: 0.95rem; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.2);">
        <i class="bi bi-check2-circle me-1"></i> Simpan Nomor PO & Harga
      </button>
    </div>
  </div>
</form>

<!-- Histori Perubahan No. PO & Harga (Purchasing) -->
<div class="card mt-4 mb-4">
  <div class="card-header d-flex justify-between align-center">
    <span style="font-weight: 700; color: #0f172a;">
      <i class="bi bi-clock-history me-1 text-primary"></i> Riwayat Perubahan Sebelumnya Pada Dokumen Ini
    </span>
    <span class="badge" style="background: #e0f2fe; color: #0284c7; font-weight: 600; border: 1px solid #bae6fd;">
      <?= count($receipt['purchasing_logs'] ?? []) ?> Catatan Riwayat
    </span>
  </div>
  <div class="card-body" style="padding: <?= empty($receipt['purchasing_logs']) ? '1.5rem' : '0' ?>;">
    <?php if (empty($receipt['purchasing_logs'])): ?>
      <div class="text-center text-muted" style="font-size: 0.88rem;">
        <i class="bi bi-clock-history" style="font-size: 1.5rem; color: #cbd5e1; display: block; margin-bottom: 0.35rem;"></i>
        Belum pernah ada riwayat perubahan No. PO atau harga pada dokumen penerimaan ini.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table" style="vertical-align: middle; margin-bottom: 0;">
          <thead>
            <tr style="background: #f8fafc;">
              <th style="width: 18%;">Waktu & Tanggal</th>
              <th style="width: 25%;">Perubahan No. PO</th>
              <th style="width: 37%;">Perubahan Harga Item</th>
              <th style="width: 20%;">Petugas Purchasing</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($receipt['purchasing_logs'] as $pLog): ?>
              <?php
                $pDate = !empty($pLog['created_at']) ? date('d/m/Y H:i', strtotime($pLog['created_at'])) . ' WIB' : '-';
                $pUser = $pLog['user']['name'] ?? ($pLog['user']['username'] ?? 'Purchasing');
                $oldVals = $pLog['old_values'] ?? [];
                $newVals = $pLog['new_values'] ?? [];
                $oldPo = $oldVals['po_number'] ?? null;
                $newPo = $newVals['po_number'] ?? null;
                $oldItems = $oldVals['items'] ?? [];
                $newItems = $newVals['items'] ?? [];
              ?>
              <tr>
                <td>
                  <div style="font-weight: 600; font-size: 0.85rem; color: #334155;"><?= $pDate ?></div>
                  <?php if (!empty($pLog['ip_address'])): ?>
                    <div class="text-muted" style="font-size: 0.72rem;">IP: <?= htmlspecialchars($pLog['ip_address']) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($oldPo !== $newPo): ?>
                    <div style="font-size: 0.8rem; margin-bottom: 3px;">
                      <span class="text-muted" style="font-size: 0.72rem;">Lama:</span>
                      <?php if (!empty($oldPo)): ?>
                        <span style="font-family: monospace; text-decoration: line-through; color: #dc2626; font-weight: 600;"><?= htmlspecialchars($oldPo) ?></span>
                      <?php else: ?>
                        <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.7rem;">(Belum ada PO)</span>
                      <?php endif; ?>
                    </div>
                    <div>
                      <span class="text-muted" style="font-size: 0.72rem;">Baru:</span>
                      <?php if (!empty($newPo)): ?>
                        <span style="font-family: monospace; font-weight: 700; color: #0284c7; background: #f0f9ff; padding: 2px 6px; border-radius: 4px; border: 1px solid #bae6fd;">
                          <i class="bi bi-file-earmark-check me-1"></i><?= htmlspecialchars($newPo) ?>
                        </span>
                      <?php else: ?>
                        <span class="text-muted" style="font-style: italic;">(Dikosongkan)</span>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span style="font-family: monospace; font-size: 0.85rem; color: #475569;"><?= htmlspecialchars($newPo ?? '-') ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($newItems)): ?>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                      <?php foreach ($newItems as $iKey => $nItem): ?>
                        <?php 
                          $oItem = $oldItems[$iKey] ?? null;
                          $oldUPrice = (float)($oItem['unit_price'] ?? 0);
                          $newUPrice = (float)($nItem['unit_price'] ?? 0);
                          $diff = $newUPrice - $oldUPrice;
                        ?>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 4px 8px; font-size: 0.78rem;">
                          <div class="fw-bold" style="color: #1e293b; margin-bottom: 2px;">
                            <?= htmlspecialchars($nItem['item_name'] ?? 'Item') ?>
                          </div>
                          <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <span style="text-decoration: line-through; color: #94a3b8; font-family: monospace;">
                              <?= formatRupiah($oldUPrice) ?>
                            </span>
                            <i class="bi bi-arrow-right text-muted" style="font-size: 0.75rem;"></i>
                            <span style="font-weight: 700; color: #0284c7; font-family: monospace;">
                              <?= formatRupiah($newUPrice) ?>
                            </span>
                            <?php if ($diff > 0): ?>
                              <span class="badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; font-size: 0.68rem; padding: 1px 5px;">
                                +<?= formatRupiah($diff) ?>
                              </span>
                            <?php elseif ($diff < 0): ?>
                              <span class="badge" style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; font-size: 0.68rem; padding: 1px 5px;">
                                -<?= formatRupiah(abs($diff)) ?>
                              </span>
                            <?php endif; ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php else: ?>
                    <span class="text-muted" style="font-size: 0.8rem;">(Tidak ada perubahan harga item)</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="font-weight: 600; color: #1e293b; font-size: 0.85rem;"><?= htmlspecialchars($pUser) ?></div>
                  <span class="badge" style="background: #e0e7ff; color: #4338ca; font-size: 0.68rem;">Purchasing</span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

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
