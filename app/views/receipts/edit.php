<?php
$companyCode = $receipt['company']['code'] ?? 'KJG';
$companyName = $receipt['company']['name'] ?? 'PT Karunia Jaya Global';
$companyId = $receipt['company_id'] ?? 5;
$isLNP = ($companyCode === 'LNP');
$themeColor = $isLNP ? '#0d9488' : '#2563eb';

$pageTitle = 'Koreksi Penerimaan: ' . ($receipt['receipt_number'] ?? '');
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
  <div class="card-header d-flex justify-between align-center" style="border-left: 5px solid #f59e0b;">
    <div class="d-flex align-center gap-1">
      <span><i class="bi bi-pencil-square text-warning me-1"></i> Form Koreksi Dokumen Penerimaan Barang</span>
      <span class="badge" style="background-color: <?= $themeColor ?>; color: #fff; font-size: 0.85rem; padding: 0.35rem 0.65rem;">
        <?= htmlspecialchars($companyCode) ?> - <?= htmlspecialchars($companyName) ?>
      </span>
      <span class="badge badge-secondary" style="font-family: monospace;">
        <?= htmlspecialchars($receipt['receipt_number'] ?? '') ?>
      </span>
    </div>
    <div class="d-flex gap-1 align-center">
      <a href="<?= url('receipts/show') ?>&id=<?= $receipt['id'] ?>" class="btn btn-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail
      </a>
    </div>
  </div>

  <div class="card-body">

    <!-- Kotak Tambah / Cari Barang Jika Ada Barang Tertinggal -->
    <div style="background: #ffffff; padding: 1.25rem; border-radius: 8px; border: 2px dashed #94a3b8; margin-bottom: 1.5rem;">
      <div>
        <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 0.2rem;">
          <i class="bi bi-search me-1" style="color: <?= $themeColor ?>;"></i> Tambah Barang Tertinggal ke Dokumen Ini
        </h3>
        <small class="text-muted">Ketik nama barang, kode item, atau scan barcode jika ada barang masuk yang belum terinput pada dokumen ini.</small>
      </div>

      <div style="position: relative; margin-top: 0.75rem;">
        <i class="bi bi-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.1rem; pointer-events: none;"></i>
        <input type="text" id="itemSearchQuery" class="form-control" 
               style="padding-left: 2.75rem; padding-right: 2.75rem; font-size: 0.95rem; height: 44px; border-radius: 8px; border: 1.5px solid #cbd5e1;" 
               placeholder="Cari nama barang atau kode item untuk menambahkan barang ke tabel..."
               autocomplete="off">
        <button type="button" id="btnClearItemSearch" 
                style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); display: none; background: transparent; border: none; font-size: 1.2rem; color: #94a3b8; cursor: pointer; padding: 0.25rem 0.5rem;"
                title="Hapus pencarian">&times;</button>
      </div>

      <div id="itemSearchResults" style="margin-top: 0.85rem; display: none;"></div>
    </div>

    <form action="<?= url('receipts/update') ?>" method="POST" id="form-receipt-edit" onsubmit="return confirmSaveCorrection(event);">
      <input type="hidden" name="receipt_id" value="<?= $receipt['id'] ?>">

      <!-- Informasi Header Dokumen -->
      <div style="background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
        <div class="d-flex justify-between align-center mb-1">
          <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 0; color: #1e293b;">
            <i class="bi bi-file-text me-1" style="color: #64748b;"></i> Informasi Header Dokumen
          </h3>
          <span class="badge" style="background-color: <?= $themeColor ?>; color: #fff; font-size: 0.75rem;">
            Kepemilikan Stok: PT <?= htmlspecialchars($companyCode) ?>
          </span>
        </div>

        <div class="form-row">
          <div class="form-group" style="flex: 1;">
            <label><i class="bi bi-lock-fill me-1 text-muted"></i> No. Penerimaan (Permanen)</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($receipt['receipt_number'] ?? '') ?>" disabled style="background: #f1f5f9; font-weight: 700; font-family: monospace;">
          </div>

          <div class="form-group" style="flex: 1;">
            <label><i class="bi bi-lock-fill me-1 text-muted"></i> Tanggal Terima Fisik</label>
            <input type="date" class="form-control" value="<?= htmlspecialchars($receipt['received_date'] ?? date('Y-m-d')) ?>" disabled style="background: #f1f5f9;">
          </div>

          <div class="form-group" style="flex: 2;">
            <label for="supplier_name"><i class="bi bi-truck me-1"></i> Supplier / Vendor Pengirim</label>
            <input type="text" name="supplier_name" id="supplier_name" class="form-control" 
                   value="<?= htmlspecialchars($receipt['supplier']['name'] ?? $receipt['supplier_name'] ?? '') ?>"
                   placeholder="Nama supplier atau vendor pengirim">
          </div>
        </div>

        <div class="form-row" style="margin-top: 0.75rem;">
          <div class="form-group" style="flex: 1;">
            <label for="delivery_order_number"><i class="bi bi-receipt me-1"></i> No. Surat Jalan Vendor</label>
            <input type="text" name="delivery_order_number" id="delivery_order_number" class="form-control" 
                   value="<?= htmlspecialchars($receipt['delivery_order_number'] ?? '') ?>" 
                   placeholder="Contoh: SJ-2026/09/001">
          </div>

          <div class="form-group" style="flex: 2;">
            <label for="notes"><i class="bi bi-chat-left-dots me-1"></i> Catatan Penerimaan</label>
            <input type="text" name="notes" id="notes" class="form-control" 
                   value="<?= htmlspecialchars($receipt['notes'] ?? '') ?>" 
                   placeholder="Alasan koreksi atau catatan pengiriman...">
          </div>
        </div>
      </div>

      <!-- Tabel Koreksi Barang -->
      <div style="margin-bottom: 1.5rem;" id="receipt-table-section">
        <div class="d-flex justify-between align-center mb-1">
          <div>
            <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 0;">
              <i class="bi bi-boxes me-1" style="color: #64748b;"></i> Daftar Barang & Kuantitas Masuk
            </h3>
            <small class="text-muted">Koreksi jumlah barang masuk (Qty), lokasi rak, atau kondisi fisik barang di bawah ini.</small>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table" id="receipt-items-table">
            <thead>
              <tr>
                <th style="width: 50%;">Barang (Kode & Nama)</th>
                <th style="width: 25%;">Qty Masuk (Koreksi)</th>
                <th style="width: 17%;">Kondisi</th>
                <th style="width: 8%; text-align: center;">Aksi</th>
              </tr>
            </thead>
            <tbody id="receipt-items-tbody">
              <?php if (empty($receipt['items'])): ?>
                <tr id="empty-receipt-row">
                  <td colspan="4" class="text-center text-muted" style="padding: 2rem;">
                    Tidak ada barang dalam dokumen penerimaan ini.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($receipt['items'] as $idx => $it): ?>
                  <?php
                    $itemData = $it['item'] ?? [];
                    $unitCode = $itemData['unit']['code'] ?? '-';
                    $u = strtoupper($unitCode);
                    $isDecimal = in_array($u, ['MTR','METER','M','LTR','LITER','L','KG','KILOGRAM','GR','GRAM']);
                    $stepVal = $isDecimal ? 'any' : '1';

                    $batch = $it['batch'] ?? null;
                    $qtyUsed = !empty($batch['qty_used']) ? (float) $batch['qty_used'] : 0.0;
                    $qtyInitial = !empty($batch['qty_initial']) ? (float) $batch['qty_initial'] : (float) $it['qty'];
                    $qtyRemaining = !empty($batch['qty_remaining']) ? (float) $batch['qty_remaining'] : (float) $it['qty'];

                    // Minimum allowed quantity: cannot decrease below what was already issued
                    $minVal = $qtyUsed > 0 ? $qtyUsed : ($isDecimal ? 0.01 : 1);
                  ?>
                  <tr class="item-row" data-index="<?= $idx ?>" data-item-id="<?= $it['item_id'] ?>">
                    <td>
                      <input type="hidden" name="items[<?= $idx ?>][id]" value="<?= $it['id'] ?>">
                      <input type="hidden" name="items[<?= $idx ?>][item_id]" value="<?= $it['item_id'] ?>">
                      <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <span class="badge" style="font-family: monospace; font-size: 0.85rem; background: #e2e8f0; color: #1e293b; font-weight: 700;">
                          <?= htmlspecialchars($itemData['item_code'] ?? '-') ?>
                        </span>
                        <strong style="color: #0f172a; font-size: 0.95rem;">
                          <?= htmlspecialchars($itemData['name'] ?? '-') ?>
                        </strong>
                      </div>

                      <div style="font-size: 0.8rem; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                        <span>Satuan: <strong style="color: #2563eb;"><?= htmlspecialchars($unitCode) ?></strong></span>
                        <?php if ($qtyUsed > 0): ?>
                          <span style="background: #fee2e2; color: #b91c1c; padding: 2px 6px; border-radius: 4px; font-weight: 600; font-size: 0.75rem;" title="Barang dari penerimaan ini sudah dikeluarkan sebagian di transaksi barang keluar">
                            <i class="bi bi-exclamation-diamond-fill me-1"></i> Terpakai di Keluar: <?= formatQty($qtyUsed, $unitCode) ?>
                          </span>
                        <?php else: ?>
                          <span style="background: #f0fdf4; color: #166534; padding: 2px 6px; border-radius: 4px; font-weight: 600; font-size: 0.75rem;">
                            <i class="bi bi-check-circle-fill me-1"></i> Belum terpakai di transaksi keluar
                          </span>
                        <?php endif; ?>
                      </div>
                      <input type="hidden" name="items[<?= $idx ?>][warehouse_location_id]" value="<?= $it['warehouse_location_id'] ?? 1 ?>">
                    </td>

                    <td>
                      <div class="input-group">
                        <input type="number" 
                               step="<?= $stepVal ?>" 
                               min="<?= $minVal ?>" 
                               name="items[<?= $idx ?>][qty]" 
                               class="form-control item-qty" 
                               value="<?= (float) $it['qty'] ?>" 
                               required 
                               data-min="<?= $minVal ?>"
                               data-qty-used="<?= $qtyUsed ?>"
                               data-original-qty="<?= (float) $it['qty'] ?>"
                               oninput="validateRowQty(this)">
                        <span class="unit-badge has-unit"><?= htmlspecialchars($unitCode) ?></span>
                      </div>
                      <?php if ($qtyUsed > 0): ?>
                        <small class="text-danger" style="font-size: 0.725rem; display: block; margin-top: 3px;">
                          Min: <?= formatQty($qtyUsed) ?> (karena sudah terpakai)
                        </small>
                      <?php endif; ?>
                    </td>

                    <td>
                      <select name="items[<?= $idx ?>][condition]" class="form-select" style="font-size: 0.85rem;">
                        <option value="good" <?= ($it['condition'] ?? 'good') === 'good' ? 'selected' : '' ?>>Baik</option>
                        <option value="damaged" <?= ($it['condition'] ?? '') === 'damaged' ? 'selected' : '' ?>>Rusak Fisik</option>
                        <option value="other" <?= ($it['condition'] ?? '') === 'other' ? 'selected' : '' ?>>Lainnya</option>
                      </select>
                    </td>

                    <td class="text-center">
                      <?php if ($qtyUsed > 0): ?>
                        <button type="button" class="btn btn-secondary btn-sm" disabled title="Tidak dapat dihapus karena sudah ada pemakaian di transaksi keluar" style="opacity: 0.5; cursor: not-allowed;">
                          <i class="bi bi-lock-fill"></i>
                        </button>
                      <?php else: ?>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeReceiptRow(this)" title="Hapus baris barang ini">
                          <i class="bi bi-trash"></i>
                        </button>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="d-flex justify-between align-center" style="border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">
        <a href="<?= url('receipts/show') ?>&id=<?= $receipt['id'] ?>" class="btn btn-outline">
          <i class="bi bi-x-circle me-1"></i> Batalkan Perubahan
        </a>
        <button type="submit" class="btn btn-primary" id="btnSubmitCorrection" style="background-color: #2563eb; border-color: #2563eb; font-weight: 700; padding: 0.6rem 1.75rem;">
          <i class="bi bi-check2-circle me-1"></i> Simpan Koreksi Penerimaan (Update Stok)
        </button>
      </div>
    </form>
  </div>
</div>

<script>
const currentTargetCompanyId = <?= (int) $companyId ?>;
const currentTargetCompanyCode = '<?= htmlspecialchars($companyCode, ENT_QUOTES) ?>';
let rowCount = <?= !empty($receipt['items']) ? count($receipt['items']) : 0 ?>;
const defaultLocations = <?= json_encode($locations ?? []) ?>;

// Search live for items to add
const searchInput = document.getElementById('itemSearchQuery');
const btnClearSearch = document.getElementById('btnClearItemSearch');
const searchResultsBox = document.getElementById('itemSearchResults');
let searchDebounceTimer = null;

if (searchInput) {
  searchInput.addEventListener('input', function() {
    const q = this.value.trim();
    if (btnClearSearch) {
      btnClearSearch.style.display = q ? 'block' : 'none';
    }

    clearTimeout(searchDebounceTimer);
    if (!q) {
      searchResultsBox.style.display = 'none';
      searchResultsBox.innerHTML = '';
      return;
    }

    searchDebounceTimer = setTimeout(() => {
      performWarehouseItemSearch(q);
    }, 200);
  });

  searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      const q = this.value.trim();
      if (q) {
        clearTimeout(searchDebounceTimer);
        performWarehouseItemSearch(q);
      }
    }
  });
}

if (btnClearSearch) {
  btnClearSearch.addEventListener('click', function() {
    searchInput.value = '';
    btnClearSearch.style.display = 'none';
    searchResultsBox.style.display = 'none';
    searchResultsBox.innerHTML = '';
    searchInput.focus();
  });
}

function performWarehouseItemSearch(query) {
  searchResultsBox.style.display = 'block';
  searchResultsBox.innerHTML = `
    <div style="padding: 1rem; text-align: center; color: #64748b;">
      <i class="bi bi-hourglass-split me-1"></i> Mencari barang di inventori gudang PT ${escapeHtml(currentTargetCompanyCode)}...
    </div>
  `;

  fetch(`index.php?r=items/search-ajax&company_id=${currentTargetCompanyId}&q=${encodeURIComponent(query)}`)
    .then(r => r.json())
    .then(res => {
      const items = res.data || [];
      renderWarehouseSearchResults(query, items);
    })
    .catch(err => {
      searchResultsBox.innerHTML = `
        <div style="padding: 0.75rem; color: #dc2626; font-size: 0.85rem;">
          <i class="bi bi-exclamation-triangle me-1"></i> Gagal melakukan pencarian: ${escapeHtml(err.message || err)}
        </div>
      `;
    });
}

function renderWarehouseSearchResults(query, items) {
  if (items.length === 0) {
    searchResultsBox.innerHTML = `
      <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 8px; padding: 1rem 1.25rem;">
        <div style="font-weight: 700; color: #92400e; font-size: 0.95rem;">
          <i class="bi bi-exclamation-circle-fill me-1" style="color: #d97706;"></i>
          Barang "<strong>${escapeHtml(query)}</strong>" tidak ditemukan di PT ${escapeHtml(currentTargetCompanyCode)}.
        </div>
      </div>
    `;
    return;
  }

  let html = `
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
      <div style="padding: 0.65rem 1rem; background: #e2e8f0; font-size: 0.85rem; font-weight: 700; color: #334155; display: flex; justify-content: space-between; align-items: center;">
        <span><i class="bi bi-check2-circle text-success me-1"></i> Ditemukan ${items.length} barang di gudang PT ${escapeHtml(currentTargetCompanyCode)}:</span>
        <small class="text-muted">Klik "Pilih & Tambahkan" untuk memasukkan barang ke dokumen ini</small>
      </div>
      <div style="max-height: 260px; overflow-y: auto;">
  `;

  items.forEach(it => {
    const itemCode = escapeHtml(it.item_code || '');
    const itemName = escapeHtml(it.name || '');
    const unitCode = escapeHtml(it.unit ? it.unit.code : '');

    const itemJsonStr = JSON.stringify({
      id: it.id,
      item_code: it.item_code,
      name: it.name,
      unit_code: unitCode,
    }).replace(/"/g, '&quot;');

    html += `
      <div style="padding: 0.65rem 1rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; gap: 1rem; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
        <div style="flex: 1; min-width: 0;">
          <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <span style="font-family: monospace; font-weight: 700; color: #1e293b; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 0.85rem;">
              ${itemCode}
            </span>
            <span style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">
              ${itemName}
            </span>
          </div>
          <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">
            Satuan: <strong style="color: #2563eb;">${unitCode || '-'}</strong>
          </div>
        </div>
        <div style="flex-shrink: 0;">
          <button type="button" class="btn btn-primary btn-sm" onclick="addItemToReceiptTable(${itemJsonStr})" style="font-weight: 600; padding: 0.35rem 0.85rem; font-size: 0.85rem;">
            <i class="bi bi-plus-lg me-1"></i> Pilih & Tambahkan
          </button>
        </div>
      </div>
    `;
  });

  html += `</div></div>`;
  searchResultsBox.innerHTML = html;
}

function addItemToReceiptTable(item) {
  // Check if item already exists in table
  const existingRows = document.querySelectorAll('#receipt-items-tbody .item-row');
  for (let r of existingRows) {
    if (r.dataset.itemId == item.id) {
      alert(`Barang "${item.name}" sudah ada di dalam tabel penerimaan ini. Silakan sesuaikan jumlah Qty pada baris tersebut.`);
      const qtyInput = r.querySelector('.item-qty');
      if (qtyInput) {
        qtyInput.focus();
        qtyInput.select();
      }
      return;
    }
  }

  const tbody = document.getElementById('receipt-items-tbody');
  const emptyRow = document.getElementById('empty-receipt-row');
  if (emptyRow) {
    emptyRow.remove();
  }

  const u = (item.unit_code || '').toUpperCase();
  const isDecimal = ['MTR','METER','M','LTR','LITER','L','KG','KILOGRAM','GR','GRAM'].includes(u);
  const stepVal = isDecimal ? 'any' : '1';
  const minVal = isDecimal ? '0.01' : '1';

  let locOptionsHtml = '';
  defaultLocations.forEach(loc => {
    const locLabel = escapeHtml(loc.code || '') + (loc.zone ? ' - ' + escapeHtml(loc.zone) : '') + (loc.rack ? ' (' + escapeHtml(loc.rack) + ')' : '');
    locOptionsHtml += `<option value="${loc.id}">${locLabel}</option>`;
  });

  const tr = document.createElement('tr');
  tr.className = 'item-row';
  tr.dataset.index = rowCount;
  tr.dataset.itemId = item.id;

  tr.innerHTML = `
    <td>
      <input type="hidden" name="items[${rowCount}][item_id]" value="${item.id}">
      <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
        <span class="badge" style="font-family: monospace; font-size: 0.85rem; background: #e2e8f0; color: #1e293b; font-weight: 700;">
          ${escapeHtml(item.item_code)}
        </span>
        <strong style="color: #0f172a; font-size: 0.95rem;">
          ${escapeHtml(item.name)}
        </strong>
      </div>
      <div style="font-size: 0.8rem; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 0.6rem;">
        <span>Satuan: <strong style="color: #2563eb;">${escapeHtml(item.unit_code || '-')}</strong></span>
        <span style="background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px; font-weight: 600; font-size: 0.75rem;">
          <i class="bi bi-plus-circle me-1"></i> Barang Baru Ditambahkan
        </span>
      </div>
      <input type="hidden" name="items[${rowCount}][warehouse_location_id]" value="1">
    </td>
    <td>
      <div class="input-group">
        <input type="number" step="${stepVal}" min="${minVal}" name="items[${rowCount}][qty]" class="form-control item-qty" required placeholder="0" data-min="${minVal}" oninput="validateRowQty(this)">
        <span class="unit-badge has-unit">${escapeHtml(item.unit_code || '-')}</span>
      </div>
    </td>
    <td>
      <select name="items[${rowCount}][condition]" class="form-select" style="font-size: 0.85rem;">
        <option value="good">Baik</option>
        <option value="damaged">Rusak Fisik</option>
        <option value="other">Lainnya</option>
      </select>
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-danger btn-sm" onclick="removeReceiptRow(this)" title="Hapus">
        <i class="bi bi-trash"></i>
      </button>
    </td>
  `;

  tbody.appendChild(tr);
  rowCount++;

  // Clear search and scroll
  if (searchInput) searchInput.value = '';
  if (btnClearSearch) btnClearSearch.style.display = 'none';
  if (searchResultsBox) {
    searchResultsBox.style.display = 'none';
    searchResultsBox.innerHTML = '';
  }

  const qtyInput = tr.querySelector('.item-qty');
  if (qtyInput) {
    setTimeout(() => { qtyInput.focus(); }, 150);
  }
}

function removeReceiptRow(btn) {
  const tr = btn.closest('tr');
  if (tr) {
    tr.remove();
  }
}

function validateRowQty(input) {
  const min = parseFloat(input.dataset.min || 0);
  const qtyUsed = parseFloat(input.dataset.qtyUsed || 0);
  const val = parseFloat(input.value || 0);

  if (qtyUsed > 0 && val < qtyUsed) {
    input.setCustomValidity(`Kuantitas tidak boleh kurang dari ${qtyUsed} karena sudah dikeluarkan di transaksi keluar.`);
  } else if (val <= 0) {
    input.setCustomValidity(`Kuantitas harus lebih dari 0.`);
  } else {
    input.setCustomValidity('');
  }
}

function confirmSaveCorrection(e) {
  const rows = document.querySelectorAll('#receipt-items-tbody .item-row');
  if (rows.length === 0) {
    alert('Dokumen penerimaan harus memiliki minimal 1 barang.');
    e.preventDefault();
    return false;
  }

  for (let r of rows) {
    const qtyInput = r.querySelector('.item-qty');
    if (qtyInput) {
      validateRowQty(qtyInput);
      if (!qtyInput.checkValidity()) {
        qtyInput.reportValidity();
        e.preventDefault();
        return false;
      }
    }
  }

  return confirm('Apakah Anda yakin ingin menyimpan koreksi penerimaan barang ini?\n\nPerubahan kuantitas akan langsung memperbarui saldo stok barang di gudang tanpa mengubah urutan antrean FIFO.');
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
