<?php
$pageTitle = 'Penerimaan Barang Masuk';
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
  <div class="card-header d-flex justify-between align-center" style="flex-wrap: wrap; gap: 0.75rem;">
    <div class="d-flex align-center gap-1">
      <span><i class="bi bi-box-arrow-in-down me-1"></i>Riwayat Dokumen Barang Masuk</span>
    </div>
    <div class="d-flex gap-1 align-center" style="flex-wrap: wrap;">
      <a href="<?= url('receipts/export-excel') ?>" class="btn btn-outline btn-sm" style="font-weight: 600; color: #16a34a; border-color: #16a34a;" title="Ekspor seluruh data barang masuk ke 1 file Excel multi-sheet terpisah per PT & per Bulan">
        <i class="bi bi-file-earmark-excel me-1"></i>Ekspor Excel (.xlsx)
      </a>
      <?php if (canManageMaster()): ?>
        <a href="<?= url('receipts/create') ?>&company=KJG" class="btn btn-sm" style="font-weight: 600; background-color: #800020; border-color: #6b001b; color: #ffffff;">
          <i class="bi bi-box-arrow-in-down me-1"></i>Barang Masuk PT KJG
        </a>
        <a href="<?= url('receipts/create') ?>&company=LNP" class="btn btn-sm" style="font-weight: 600; background-color: #0284c7; border-color: #0369a1; color: #ffffff;">
          <i class="bi bi-box-arrow-in-down me-1"></i>Barang Masuk PT LNP
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="card-body">
    <!-- Filter Bar -->
    <form method="GET" action="<?= url('receipts') ?>" class="filter-bar" id="receiptFilterForm" style="flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
      <input type="hidden" name="r" value="receipts">
      
      <div style="flex: 2; min-width: 240px; position: relative;">
        <input type="text" id="receiptSearchInput" name="search" class="form-control" 
               placeholder="Cari nomor dokumen, surat jalan, supplier..." 
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" autocomplete="off"
               style="padding-left: 2.25rem; padding-right: 2rem;">
        <i class="bi bi-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
        <?php if (!empty($_GET['search'])): ?>
          <a href="<?= url('receipts') ?>&company_id=<?= urlencode($_GET['company_id'] ?? '') ?>&per_page=<?= urlencode($_GET['per_page'] ?? '25') ?>" 
             title="Hapus pencarian" 
             style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); color: #94a3b8; text-decoration: none; padding: 0.25rem;">
            <i class="bi bi-x-circle-fill"></i>
          </a>
        <?php endif; ?>
      </div>

      <div style="min-width: 150px;">
        <select name="company_id" class="form-select" onchange="this.form.submit()">
          <option value="">Semua PT</option>
          <?php foreach ($companies as $comp): ?>
            <option value="<?= $comp['id'] ?>" <?= (isset($_GET['company_id']) && $_GET['company_id'] == $comp['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($comp['code'] . ' - ' . $comp['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="min-width: 140px;">
        <select name="per_page" class="form-select" onchange="this.form.submit()" title="Jumlah data yang ditampilkan per halaman">
          <option value="10" <?= ($_GET['per_page'] ?? '25') == '10' ? 'selected' : '' ?>>10 / halaman</option>
          <option value="25" <?= ($_GET['per_page'] ?? '25') == '25' ? 'selected' : '' ?>>25 / halaman</option>
          <option value="50" <?= ($_GET['per_page'] ?? '25') == '50' ? 'selected' : '' ?>>50 / halaman</option>
          <option value="100" <?= ($_GET['per_page'] ?? '25') == '100' ? 'selected' : '' ?>>100 / halaman</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
    </form>

    <!-- Total Data Summary -->
    <div class="d-flex justify-between align-center mb-2" style="font-size: 0.85rem; color: #64748b;">
      <div>
        Menampilkan <strong style="color: #0f172a; font-weight: 700;"><?= number_format(!empty($meta['total']) ? $meta['total'] : count($receipts), 0, ',', '.') ?></strong> data dokumen penerimaan
        <?php if (!empty($meta) && ($meta['last_page'] ?? 1) > 1): ?>
          <span class="text-muted">(Halaman <?= $meta['current_page'] ?? 1 ?> dari <?= $meta['last_page'] ?? 1 ?>)</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table" style="vertical-align: middle;">
        <thead>
          <tr>
            <th>No. Dokumen</th>
            <th>Tanggal</th>
            <th style="text-align: center;">PT Pemilik</th>
            <th>Supplier</th>
            <th>Petugas Penerima</th>
            <th style="text-align: center;">Jml Item</th>
            <th class="text-center" style="white-space: nowrap; width: 1%;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($receipts)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted" style="padding: 2.5rem 1rem;">
                <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; color: #cbd5e1;"></i>
                Belum ada dokumen penerimaan barang yang sesuai dengan filter.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($receipts as $gr): ?>
              <?php
                $compCode = strtoupper($gr['company']['code'] ?? 'KJG');
                $isLNP = ($compCode === 'LNP');
                $supplierName = trim($gr['supplier']['name'] ?? $gr['supplier_name'] ?? '');
                $itemCount = $gr['items_count'] ?? count($gr['items'] ?? []);
                $petugas = trim($gr['received_by']['name'] ?? '-');
              ?>
              <tr>
                <td>
                  <a href="<?= url('receipts/show') ?>&id=<?= $gr['id'] ?>" class="fw-bold" style="font-family: monospace; color: #1d4ed8; text-decoration: none; font-size: 0.95rem;">
                    <?= htmlspecialchars($gr['receipt_number']) ?>
                  </a>
                  <?php if (!empty($gr['po_number'])): ?>
                    <div style="font-size: 0.78rem; margin-top: 3px; color: #0284c7; font-weight: 600;">
                      PO: <?= htmlspecialchars($gr['po_number']) ?>
                    </div>
                  <?php else: ?>
                    <div style="font-size: 0.75rem; margin-top: 3px;">
                      <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.7rem; font-weight: 600; padding: 2px 6px;">
                        Belum ada PO
                      </span>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($gr['delivery_order_number'])): ?>
                    <div class="text-muted" style="font-size: 0.75rem; margin-top: 2px;">
                      <i class="bi bi-receipt me-1"></i>SJ: <?= htmlspecialchars($gr['delivery_order_number']) ?>
                    </div>
                  <?php endif; ?>
                </td>

                <td>
                  <span style="color: #475569; font-size: 0.85rem; white-space: nowrap;">
                    <?= formatDate($gr['received_date']) ?>
                  </span>
                </td>

                <td class="text-center">
                  <?php if ($isLNP): ?>
                    <span class="badge badge-company-lnp" style="background-color: #0284c7; color: #ffffff; border: 1px solid #0369a1; font-weight: 700; font-size: 0.75rem; letter-spacing: 0.03em;">
                      LNP
                    </span>
                  <?php else: ?>
                    <span class="badge badge-company-kjg" style="background-color: #800020; color: #ffffff; border: 1px solid #6b001b; font-weight: 700; font-size: 0.75rem; letter-spacing: 0.03em;">
                      KJG
                    </span>
                  <?php endif; ?>
                </td>

                <td>
                  <?php if (!empty($supplierName) && $supplierName !== '-'): ?>
                    <span style="font-weight: 600; color: #334155;">
                      <?= htmlspecialchars($supplierName) ?>
                    </span>
                  <?php else: ?>
                    <span class="text-muted" style="font-style: italic;">-</span>
                  <?php endif; ?>
                </td>

                <td>
                  <span style="color: #475569; font-size: 0.85rem;">
                    <?= htmlspecialchars($petugas) ?>
                  </span>
                </td>

                <td class="text-center">
                  <span class="badge" style="background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; font-weight: 600; font-size: 0.75rem; padding: 0.3rem 0.65rem;">
                    <?= $itemCount ?> Item
                  </span>
                </td>

                <td class="text-right" style="white-space: nowrap;">
                  <div class="action-buttons">
                    <?php 
                      $grIsLocked = !empty($gr['is_purchasing_locked']) || (int)($gr['purchasing_edit_count'] ?? 0) >= 3;
                    ?>
                    <?php if (canManagePurchasing()): ?>
                      <?php if ($grIsLocked && isPurchasing()): ?>
                        <span class="badge badge-danger" style="font-size: 0.72rem; padding: 4px 7px;" title="Akses edit No. PO & Harga terkunci. Hubungi Admin.">
                          <i class="bi bi-lock-fill me-1"></i>Terkunci
                        </span>
                      <?php else: ?>
                        <a href="<?= url('receipts/edit-purchasing') ?>&id=<?= $gr['id'] ?>" class="btn-action-price" title="Input / Edit No. PO & Harga Beli">
                          <i class="bi bi-tag-fill me-1"></i>PO & Harga
                        </a>
                      <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($grIsLocked && (isAdmin() || isMaintenance())): ?>
                      <a href="<?= url('receipts/show') ?>&id=<?= $gr['id'] ?>" class="btn btn-outline btn-sm" style="color: #b45309; border-color: #fde68a; background: #fffbeb; font-size: 0.75rem; padding: 2px 7px;" title="Klik untuk membuka kunci akses di halaman detail">
                        <i class="bi bi-unlock-fill me-1"></i>Buka Kunci
                      </a>
                    <?php endif; ?>
                    <?php if (canManageMaster()): ?>
                      <a href="<?= url('receipts/edit') ?>&id=<?= $gr['id'] ?>" class="btn-action-edit" title="Koreksi Barang Masuk">
                        <i class="bi bi-pencil me-1"></i>Edit
                      </a>
                    <?php endif; ?>
                    <a href="<?= url('receipts/show') ?>&id=<?= $gr['id'] ?>" class="btn-action-view" title="Lihat Detail Penerimaan">
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

    <!-- Pagination -->
    <?php if (!empty($meta) && ($meta['last_page'] ?? 1) > 1): ?>
      <?php
        $cur = (int) ($meta['current_page'] ?? 1);
        $last = (int) ($meta['last_page'] ?? 1);
        $total = (int) ($meta['total'] ?? count($receipts));
        $perPage = (int) ($meta['per_page'] ?? 25);
        $from = ($cur - 1) * $perPage + 1;
        $to = min($total, $cur * $perPage);
        $baseParams = $_GET;
      ?>
      <div class="d-flex justify-between align-center mt-3 pt-3" style="border-top: 1px solid var(--border); flex-wrap: wrap; gap: 1rem;">
        <div class="text-muted" style="font-size: 0.85rem;">
          Menampilkan baris <strong><?= number_format($from, 0, ',', '.') ?></strong> - <strong><?= number_format($to, 0, ',', '.') ?></strong> dari total <strong><?= number_format($total, 0, ',', '.') ?></strong> transaksi (Halaman <strong><?= $cur ?></strong> dari <strong><?= $last ?></strong>)
        </div>

        <div class="pagination" style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
          <?php if ($cur > 1): ?>
            <?php $baseParams['page'] = $cur - 1; ?>
            <a href="<?= url('receipts') ?>&<?= http_build_query($baseParams) ?>" class="btn btn-outline btn-sm" style="padding: 0.25rem 0.6rem;">&laquo; Prev</a>
          <?php endif; ?>

          <?php for ($p = max(1, $cur - 2); $p <= min($last, $cur + 2); $p++): ?>
            <?php $baseParams['page'] = $p; ?>
            <a href="<?= url('receipts') ?>&<?= http_build_query($baseParams) ?>" 
               class="btn btn-sm <?= ($p === $cur) ? 'btn-primary' : 'btn-outline' ?>" 
               style="padding: 0.25rem 0.6rem; min-width: 32px; text-align: center;">
              <?= $p ?>
            </a>
          <?php endfor; ?>

          <?php if ($cur < $last): ?>
            <?php $baseParams['page'] = $cur + 1; ?>
            <a href="<?= url('receipts') ?>&<?= http_build_query($baseParams) ?>" class="btn btn-outline btn-sm" style="padding: 0.25rem 0.6rem;">Next &raquo;</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php if (canManagePurchasing()): ?>
<!-- Quick PO Modal for Purchasing -->
<div id="quickPoModal" class="modal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
  <div class="modal-dialog" style="max-width: 480px; width: 90%; background: #ffffff; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; animation: popIn 0.15s ease-out;">
    <div style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; padding: 1.1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
      <div style="display: flex; align-items: center; gap: 0.6rem;">
        <i class="bi bi-file-earmark-check-fill" style="font-size: 1.3rem;"></i>
        <div>
          <h5 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #fff;">Input / Edit No. PO</h5>
          <span style="font-size: 0.75rem; opacity: 0.9;">Role Purchasing</span>
        </div>
      </div>
      <button type="button" onclick="closeQuickPoModal()" style="background: transparent; border: none; color: #fff; font-size: 1.25rem; cursor: pointer; line-height: 1; padding: 0.25rem;">&times;</button>
    </div>

    <form method="POST" action="<?= url('receipts/update-purchasing') ?>" id="quickPoForm" style="padding: 1.25rem; margin: 0;">
      <input type="hidden" name="receipt_id" id="modalReceiptId" value="">
      <input type="hidden" name="return_to" value="index">

      <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem; margin-bottom: 1rem; font-size: 0.85rem;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem;">
          <span class="text-muted">No. Dokumen:</span>
          <span class="fw-bold" id="modalReceiptNumber" style="font-family: monospace; color: #0284c7;">-</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem;">
          <span class="text-muted">Tanggal Masuk:</span>
          <span class="fw-bold" id="modalReceiptDate">-</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
          <span class="text-muted">Supplier:</span>
          <span class="fw-bold" id="modalReceiptSupplier">-</span>
        </div>
      </div>

      <div class="mb-3">
        <label for="modalPoNumber" class="form-label fw-bold" style="color: #0f172a; font-size: 0.9rem;">
          Nomor Purchase Order (PO) <span class="text-danger">*</span>
        </label>
        <input type="text" name="po_number" id="modalPoNumber" class="form-control" placeholder="Contoh: PO-KJG-2026-09-0012" style="font-family: monospace; font-size: 0.95rem; border-color: #38bdf8;" required autofocus>
        <div class="text-muted mt-1" style="font-size: 0.75rem;">
          Purchasing dapat menginput atau memperbarui No. PO ini kapan saja.
        </div>
      </div>

      <div class="d-flex justify-end gap-1 mt-3" style="border-top: 1px solid #e2e8f0; padding-top: 1rem;">
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeQuickPoModal()">Batal</button>
        <button type="submit" class="btn btn-primary btn-sm" style="background-color: #0284c7; border-color: #0284c7; font-weight: 600;">
          <i class="bi bi-check-circle me-1"></i>Simpan No. PO
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openQuickPoModal(btn) {
  var id = btn.getAttribute('data-id');
  var receiptNumber = btn.getAttribute('data-receipt');
  var poNumber = btn.getAttribute('data-po') || '';
  var supplier = btn.getAttribute('data-supplier') || '-';
  var date = btn.getAttribute('data-date') || '-';

  document.getElementById('modalReceiptId').value = id;
  document.getElementById('modalReceiptNumber').textContent = receiptNumber;
  document.getElementById('modalReceiptDate').textContent = date;
  document.getElementById('modalReceiptSupplier').textContent = supplier;
  document.getElementById('modalPoNumber').value = poNumber;

  var modal = document.getElementById('quickPoModal');
  modal.style.display = 'flex';
  setTimeout(function() {
    document.getElementById('modalPoNumber').focus();
  }, 100);
}

function closeQuickPoModal() {
  document.getElementById('quickPoModal').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeQuickPoModal();
  }
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
