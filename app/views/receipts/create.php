<?php
$targetCompanyCode = $targetCompany['code'] ?? 'KJG';
$targetCompanyName = $targetCompany['name'] ?? 'PT Karunia Jaya Global';
$targetCompanyId = $targetCompany['id'] ?? 5;
$isLNP = ($targetCompanyCode === 'LNP');
$themeColor = $isLNP ? '#0d9488' : '#2563eb';
$otherCompanyCode = $isLNP ? 'KJG' : 'LNP';

$pageTitle = 'Input Barang Masuk - ' . $targetCompanyName . ' (' . $targetCompanyCode . ')';
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
  <div class="card-header d-flex justify-between align-center" style="border-left: 5px solid <?= $themeColor ?>;">
    <div class="d-flex align-center gap-1">
      <span><i class="bi bi-box-arrow-in-down me-1"></i> Form Penerimaan Barang Masuk</span>
      <span class="badge" style="background-color: <?= $themeColor ?>; color: #fff; font-size: 0.85rem; padding: 0.35rem 0.65rem;">
        <?= htmlspecialchars($targetCompanyCode) ?> - <?= htmlspecialchars($targetCompanyName) ?>
      </span>
    </div>
    <div class="d-flex gap-1 align-center">
      <a href="<?= url('receipts/create') ?>&company=<?= $otherCompanyCode ?>" class="btn btn-outline btn-sm" style="font-size: 0.8rem;">
        <i class="bi bi-arrow-left-right me-1"></i> Beralih ke Form PT <?= $otherCompanyCode ?>
      </a>
      <a href="<?= url('receipts') ?>" class="btn btn-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
      </a>
    </div>
  </div>

  <div class="card-body">
    <!-- Success Banner if redirected from items/create -->
    <?php if (!empty($newItemId) && !empty($newItem)): ?>
      <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 8px; padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
        <div class="d-flex align-center gap-1">
          <i class="bi bi-check-circle-fill text-success" style="font-size: 1.35rem;"></i>
          <div>
            <span style="font-weight: 700; color: #166534;">Barang Baru Siap Diinput:</span>
            <span style="color: #14532d; margin-left: 0.25rem;">
              [<strong><?= htmlspecialchars($newItem['item_code']) ?></strong>] <?= htmlspecialchars($newItem['name']) ?>
            </span>
            <div style="font-size: 0.8rem; color: #15803d; margin-top: 2px;">
              Barang telah otomatis disiapkan pada baris penerimaan pertama di bawah. Silakan masukkan kuantitas dan lokasi rak.
            </div>
          </div>
        </div>
        <span class="badge" style="background: #16a34a; color: #fff; font-size: 0.75rem;">Barang Baru Terdaftar</span>
      </div>
    <?php endif; ?>

    <form action="<?= url('receipts/store') ?>" method="POST" enctype="multipart/form-data" id="form-receipt">
      <!-- 1. KOTAK PENCARIAN & CEK KETERSEDIAAN BARANG DI GUDANG (Diletakkan di bagian paling atas) -->
      <div style="background: #ffffff; padding: 1.25rem; border-radius: 8px; border: 2px dashed <?= $themeColor ?>; margin-bottom: 1.5rem;">
        <div>
          <h3 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 0.2rem;">
            <i class="bi bi-search me-1" style="color: <?= $themeColor ?>;"></i> 1. Cek / Cari Ketersediaan Barang di Gudang PT <?= htmlspecialchars($targetCompanyCode) ?>
          </h3>
          <small class="text-muted">Ketik nama barang, kode item, spesifikasi, atau scan barcode untuk memeriksa apakah barang sudah ada di gudang.</small>
        </div>

        <!-- Kolom Input Pencarian Live -->
        <div style="position: relative; margin-top: 0.75rem;">
          <i class="bi bi-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.1rem; pointer-events: none;"></i>
          <input type="text" id="itemSearchQuery" class="form-control" 
                 style="padding-left: 2.75rem; padding-right: 2.75rem; font-size: 0.95rem; height: 46px; border-radius: 8px; border: 1.5px solid #cbd5e1;" 
                 placeholder="Ketik nama barang (cth: Plat SS400, Baut M12), kode item (cth: <?= htmlspecialchars($targetCompanyCode) ?>-0001), spesifikasi, atau scan barcode..."
                 autocomplete="off">
          <button type="button" id="btnClearItemSearch" 
                  style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); display: none; background: transparent; border: none; font-size: 1.2rem; color: #94a3b8; cursor: pointer; padding: 0.25rem 0.5rem;"
                  title="Hapus pencarian">&times;</button>
        </div>

        <!-- Wadah Hasil Pencarian Barang -->
        <div id="itemSearchResults" style="margin-top: 1rem; display: none;"></div>
      </div>

      <!-- 2. DATA HEADER DOKUMEN PENERIMAAN -->
      <div style="background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
        <div class="d-flex justify-between align-center mb-1">
          <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 0; color: #1e293b;">
            <i class="bi bi-file-text me-1" style="color: <?= $themeColor ?>;"></i> 2. Data Header Dokumen Penerimaan
          </h3>
          <span class="badge" style="background-color: <?= $themeColor ?>; color: #fff; font-size: 0.75rem;">
            Kepemilikan Stok: PT <?= htmlspecialchars($targetCompanyCode) ?>
          </span>
        </div>
        
        <input type="hidden" name="warehouse_id" value="<?= $warehouses[0]['id'] ?? 1 ?>">
        <input type="hidden" name="company_id" id="receipt_company_id" value="<?= $targetCompanyId ?>">

        <div class="form-row">
          <div class="form-group" style="flex: 2;">
            <label for="supplier_name"><i class="bi bi-truck me-1"></i> Supplier / Vendor Pengirim (Opsional)</label>
            <input type="text" name="supplier_name" id="supplier_name" class="form-control" 
                   placeholder="Contoh: PT Krakatau Steel, Toko Besi Citra, CV Sumber Logam">
          </div>

          <div class="form-group" style="flex: 1;">
            <label for="received_date"><i class="bi bi-calendar3 me-1"></i> Tanggal Terima <span class="text-danger">*</span></label>
            <input type="date" name="received_date" id="received_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>

        <div class="form-row" style="margin-top: 0.75rem;">
          <div class="form-group" style="flex: 1;">
            <label for="delivery_order_number"><i class="bi bi-receipt me-1"></i> No. Surat Jalan / No. PO / Referensi (Opsional)</label>
            <input type="text" name="delivery_order_number" id="delivery_order_number" class="form-control" placeholder="Contoh: SJ-2026/09/001 atau PO-1029">
          </div>
          <div class="form-group" style="flex: 1;">
            <label for="notes"><i class="bi bi-chat-left-dots me-1"></i> Catatan Penerimaan (Opsional)</label>
            <input type="text" name="notes" id="notes" class="form-control" placeholder="Keterangan pengiriman, ekspedisi, kondisi paket, dsb.">
          </div>
        </div>
      </div>

      <!-- 3. DAFTAR BARANG MASUK (DRAFT PENERIMAAN) -->
      <div style="margin-bottom: 1.5rem;" id="receipt-table-section">
        <div class="d-flex justify-between align-center mb-1">
          <div>
            <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 0;">
              <i class="bi bi-boxes me-1" style="color: <?= $themeColor ?>;"></i> 3. Daftar Barang Masuk (Khusus PT <?= htmlspecialchars($targetCompanyCode) ?>)
            </h3>
            <small class="text-muted">Barang yang dipilih dari pencarian di atas otomatis masuk ke tabel ini untuk ditentukan kuantitas masuknya.</small>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table" id="receipt-items-table">
            <thead>
              <tr>
                <th style="width: 48%;">Barang (Kode & Nama)</th>
                <th style="width: 20%;">Qty Masuk</th>
                <th style="width: 14%;">Kondisi</th>
                <th style="width: 13%;">Keterangan</th>
                <th style="width: 5%; text-align: center;">Hapus</th>
              </tr>
            </thead>
            <tbody id="receipt-items-tbody">
              <?php if (!empty($newItemId) && !empty($newItem)): ?>
                <?php
                  $u = strtoupper($newItem['unit']['code'] ?? '');
                  $isDec = in_array($u, ['MTR','METER','M','LTR','LITER','L','KG','KILOGRAM','GR','GRAM']);
                  $suppText = $newItem['suppliers_summary'] ?? '';
                  if (empty($suppText) && !empty($newItem['description']) && preg_match('/Supplier\s*:\s*([^|\n]+)/i', $newItem['description'], $sm)) {
                    $suppText = trim($sm[1]);
                  }
                ?>
                <tr class="item-row" data-index="0" data-item-id="<?= $newItem['id'] ?>">
                  <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                      <span class="badge" style="font-family: monospace; font-size: 0.85rem; background: #e2e8f0; color: #1e293b; font-weight: 700;">
                        <?= htmlspecialchars($newItem['item_code']) ?>
                      </span>
                      <strong style="color: #0f172a; font-size: 0.95rem;">
                        <?= htmlspecialchars($newItem['name']) ?>
                      </strong>
                    </div>
                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 3px; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                      <span>Satuan: <strong style="color: #2563eb;"><?= htmlspecialchars($newItem['unit']['code'] ?? '-') ?></strong></span>
                      <?php if (!empty($suppText)): ?>
                        <span style="background: #e0f2fe; color: #0369a1; padding: 1px 6px; border-radius: 4px; font-weight: 600;">
                          <i class="bi bi-truck me-1"></i> Supplier: <?= htmlspecialchars($suppText) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                    <input type="hidden" name="items[0][item_id]" value="<?= $newItem['id'] ?>">
                    <input type="hidden" name="items[0][warehouse_location_id]" value="<?= $locations[0]['id'] ?? 1 ?>">
                  </td>
                  <td>
                    <div class="input-group">
                      <input type="number" step="<?= $isDec ? 'any' : '1' ?>" min="<?= $isDec ? '0.01' : '1' ?>" 
                             name="items[0][qty]" class="form-control item-qty" required placeholder="<?= $isDec ? '0.00' : '1' ?>" autofocus>
                      <span class="unit-badge has-unit"><?= htmlspecialchars($newItem['unit']['code'] ?? '-') ?></span>
                    </div>
                  </td>
                  <td>
                    <select name="items[0][condition]" class="form-select">
                      <option value="good">Baik</option>
                      <option value="damaged">Rusak Fisik</option>
                      <option value="other">Lainnya</option>
                    </select>
                  </td>
                  <td>
                    <input type="text" name="items[0][notes]" class="form-control item-notes" placeholder="Catatan item...">
                  </td>
                  <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeReceiptRow(this)">
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
              <?php else: ?>
                <tr id="empty-receipt-row">
                  <td colspan="5" class="text-center text-muted" style="padding: 2.25rem 1rem;">
                    <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; color: #cbd5e1;"></i>
                    <span style="font-weight: 600; color: #64748b; font-size: 0.95rem;">Belum ada barang di daftar penerimaan.</span>
                    <div style="font-size: 0.825rem; color: #94a3b8; margin-top: 0.25rem;">
                      Ketik nama/kode barang pada kolom pencarian di atas, lalu klik <strong>"Pilih & Masukkan"</strong>.
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 4. DOKUMENTASI FOTO (FILE UPLOAD) -->
      <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
        <label for="doc_file"><i class="bi bi-camera me-1"></i> Dokumentasi Foto / Lampiran Surat Jalan <span class="text-muted" style="font-weight: normal; font-size: 0.85rem;">(Opsional)</span></label>
        <input type="file" name="attachment_file" id="doc_file" class="form-control" accept="image/*,.pdf">
        <small class="text-muted">Format yang didukung: JPG, PNG, WEBP, atau PDF (Maks. 10MB)</small>
      </div>

      <div class="d-flex justify-between align-center">
        <a href="<?= url('receipts') ?>" class="btn btn-secondary">
          <i class="bi bi-arrow-left me-1"></i> Batal
        </a>
        <button type="submit" class="btn btn-primary" style="background-color: <?= $themeColor ?>; border-color: <?= $themeColor ?>; padding: 0.65rem 1.75rem; font-weight: 600;">
          <i class="bi bi-check-circle me-1"></i> Simpan Dokumen Penerimaan PT <?= htmlspecialchars($targetCompanyCode) ?> & Tambah Stok
        </button>
      </div>
    </form>
  </div>
</div>

<script>
let rowCount = <?= (!empty($newItemId) && !empty($newItem)) ? 1 : 0 ?>;
const currentTargetCompanyId = <?= (int)$targetCompanyId ?>;
const currentTargetCompanyCode = '<?= htmlspecialchars($targetCompanyCode) ?>';
const defaultLocationId = <?= (int)($locations[0]['id'] ?? 1) ?>;
let searchDebounceTimer = null;

// Escaping helper for safe HTML rendering
function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/[&<>"']/g, function(m) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
  });
}

// ---------------- LIVE ITEM WAREHOUSE AVAILABILITY SEARCH ----------------
const searchInput = document.getElementById('itemSearchQuery');
const btnClearSearch = document.getElementById('btnClearItemSearch');
const searchResultsBox = document.getElementById('itemSearchResults');

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
    // NOT FOUND: Show clear not-found alert + Link to items/create
    const createPageUrl = `index.php?r=items/create&company=${encodeURIComponent(currentTargetCompanyCode)}&name=${encodeURIComponent(query)}&from=receipts`;
    
    searchResultsBox.innerHTML = `
      <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 8px; padding: 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
        <div>
          <div style="font-weight: 700; color: #92400e; font-size: 0.95rem; margin-bottom: 0.25rem;">
            <i class="bi bi-exclamation-circle-fill me-1" style="color: #d97706;"></i>
            Barang "<strong>${escapeHtml(query)}</strong>" belum terdaftar di gudang PT ${escapeHtml(currentTargetCompanyCode)}
          </div>
          <div style="color: #78350f; font-size: 0.85rem;">
            Barang ini belum tercatat dalam master inventori. Silakan klik tombol untuk mendaftarkan sebagai master barang baru.
          </div>
        </div>
        <div style="flex-shrink: 0;">
          <a href="${createPageUrl}" class="btn btn-primary btn-sm" style="font-weight: 600; padding: 0.5rem 1rem;">
            <i class="bi bi-plus-circle me-1"></i> Tambah Barang Baru
          </a>
        </div>
      </div>
    `;
    return;
  }

  // FOUND: Render list of matching items
  let html = `
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
      <div style="padding: 0.65rem 1rem; background: #e2e8f0; font-size: 0.85rem; font-weight: 700; color: #334155; display: flex; justify-content: space-between; align-items: center;">
        <span><i class="bi bi-check2-circle text-success me-1"></i> Ditemukan ${items.length} barang di gudang PT ${escapeHtml(currentTargetCompanyCode)}:</span>
        <small class="text-muted">Klik "Pilih & Masukkan" untuk menambahkan ke draft penerimaan</small>
      </div>
      <div style="max-height: 280px; overflow-y: auto; divide-y: 1px solid #e2e8f0;">
  `;

  items.forEach(it => {
    const itemCode = escapeHtml(it.item_code || '');
    const itemName = escapeHtml(it.name || '');
    const unitCode = escapeHtml(it.unit ? it.unit.code : '');
    const categoryName = escapeHtml(it.category ? it.category.name : '-');
    const totalStock = parseFloat(it.total_stock || 0);
    const spec = escapeHtml(it.specification || '');

    // Extract supplier info if available
    let supplierText = '';
    if (it.suppliers_summary && it.suppliers_summary !== '-') {
      supplierText = it.suppliers_summary;
    } else if (it.description && it.description.match(/Supplier\s*:\s*([^|\n]+)/i)) {
      const sm = it.description.match(/Supplier\s*:\s*([^|\n]+)/i);
      supplierText = sm ? sm[1].trim() : '';
    }

    const itemJsonStr = JSON.stringify({
      id: it.id,
      item_code: it.item_code,
      name: it.name,
      company_id: it.company_id,
      unit_code: unitCode,
      supplier: supplierText
    }).replace(/"/g, '&quot;');

    html += `
      <div style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; gap: 1rem; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
        <div style="flex: 1; min-width: 0;">
          <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <span style="font-family: monospace; font-weight: 700; color: #1e293b; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 0.85rem;">
              ${itemCode}
            </span>
            <span style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">
              ${itemName}
            </span>
            <span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 0.75rem;">
              ${categoryName}
            </span>
          </div>
          <div style="font-size: 0.8rem; color: #64748b; margin-top: 3px; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <span>Stok Saat Ini: <strong style="color: #0f172a;">${totalStock.toLocaleString('id-ID')} ${unitCode}</strong></span>
            ${spec ? `<span>Spesifikasi: <em>${spec}</em></span>` : ''}
            ${supplierText ? `<span style="background: #e0f2fe; color: #0369a1; padding: 1px 6px; border-radius: 4px; font-weight: 600;"><i class="bi bi-truck me-1"></i> Supplier: ${escapeHtml(supplierText)}</span>` : ''}
          </div>
        </div>
        <div style="flex-shrink: 0;">
          <button type="button" class="btn btn-primary btn-sm" onclick='addItemToReceiptFromSearch(${itemJsonStr})' style="font-weight: 600;">
            <i class="bi bi-plus-lg me-1"></i> Pilih & Masukkan
          </button>
        </div>
      </div>
    `;
  });

  html += `
      </div>
    </div>
  `;

  searchResultsBox.innerHTML = html;
}

function addItemToReceiptFromSearch(item) {
  const tbody = document.getElementById('receipt-items-tbody');

  // If item already exists in table, highlight and focus
  const existingRow = tbody.querySelector(`tr[data-item-id="${item.id}"]`);
  if (existingRow) {
    const qtyInput = existingRow.querySelector('.item-qty');
    if (qtyInput) {
      qtyInput.focus();
      qtyInput.select();
    }
    existingRow.style.transition = 'background 0.3s';
    existingRow.style.background = '#fef3c7';
    setTimeout(() => { existingRow.style.background = 'transparent'; }, 900);
    return;
  }

  // Remove empty row if present
  const emptyRow = document.getElementById('empty-receipt-row');
  if (emptyRow) {
    emptyRow.remove();
  }

  const u = (item.unit_code || '').toUpperCase();
  const isDecimal = ['MTR','METER','M','LTR','LITER','L','KG','KILOGRAM','GR','GRAM'].includes(u);
  const stepVal = isDecimal ? 'any' : '1';
  const minVal = isDecimal ? '0.01' : '1';
  const placeholderVal = isDecimal ? '0.00' : '1';

  const tr = document.createElement('tr');
  tr.className = 'item-row';
  tr.dataset.index = rowCount;
  tr.dataset.itemId = item.id;

  tr.innerHTML = `
    <td>
      <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
        <span class="badge" style="font-family: monospace; font-size: 0.85rem; background: #e2e8f0; color: #1e293b; font-weight: 700;">
          ${escapeHtml(item.item_code)}
        </span>
        <strong style="color: #0f172a; font-size: 0.95rem;">
          ${escapeHtml(item.name)}
        </strong>
      </div>
      <div style="font-size: 0.8rem; color: #64748b; margin-top: 3px; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
        <span>Satuan: <strong style="color: #2563eb;">${escapeHtml(item.unit_code || '-')}</strong></span>
        ${item.supplier ? `<span style="background: #e0f2fe; color: #0369a1; padding: 1px 6px; border-radius: 4px; font-weight: 600;"><i class="bi bi-truck me-1"></i> Supplier: ${escapeHtml(item.supplier)}</span>` : ''}
      </div>
      <input type="hidden" name="items[${rowCount}][item_id]" value="${item.id}">
      <input type="hidden" name="items[${rowCount}][warehouse_location_id]" value="${defaultLocationId}">
    </td>
    <td>
      <div class="input-group">
        <input type="number" step="${stepVal}" min="${minVal}" name="items[${rowCount}][qty]" class="form-control item-qty" required placeholder="${placeholderVal}">
        <span class="unit-badge has-unit">${escapeHtml(item.unit_code || '-')}</span>
      </div>
    </td>
    <td>
      <select name="items[${rowCount}][condition]" class="form-select">
        <option value="good">Baik</option>
        <option value="damaged">Rusak Fisik</option>
        <option value="other">Lainnya</option>
      </select>
    </td>
    <td>
      <input type="text" name="items[${rowCount}][notes]" class="form-control item-notes" placeholder="Catatan item...">
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-danger btn-sm" onclick="removeReceiptRow(this)">
        <i class="bi bi-trash"></i>
      </button>
    </td>
  `;

  tbody.appendChild(tr);
  rowCount++;

  // Scroll to table smoothly
  const tableSection = document.getElementById('receipt-table-section');
  if (tableSection) {
    tableSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  // Focus Qty input on this row
  const qtyInput = tr.querySelector('.item-qty');
  if (qtyInput) {
    setTimeout(() => { qtyInput.focus(); qtyInput.select(); }, 150);
  }
  tr.style.transition = 'background 0.3s';
  tr.style.background = '#ecfdf5';
  setTimeout(() => { tr.style.background = 'transparent'; }, 900);
}

function removeReceiptRow(btn) {
  const tbody = document.getElementById('receipt-items-tbody');
  const tr = btn.closest('tr');
  if (tr) {
    tr.remove();
  }
  const remaining = tbody.querySelectorAll('.item-row');
  if (remaining.length === 0) {
    tbody.innerHTML = `
      <tr id="empty-receipt-row">
        <td colspan="5" class="text-center text-muted" style="padding: 2.25rem 1rem;">
          <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; color: #cbd5e1;"></i>
          <span style="font-weight: 600; color: #64748b; font-size: 0.95rem;">Belum ada barang di daftar penerimaan.</span>
          <div style="font-size: 0.825rem; color: #94a3b8; margin-top: 0.25rem;">
            Ketik nama/kode barang pada kolom pencarian di atas, lalu klik <strong>"Pilih & Masukkan"</strong>.
          </div>
        </td>
      </tr>
    `;
  }
}

// Form validation before submitting: ensure at least 1 item is added
document.getElementById('form-receipt')?.addEventListener('submit', function(e) {
  const rows = document.querySelectorAll('#receipt-items-tbody .item-row');
  if (rows.length === 0) {
    e.preventDefault();
    alert('Harap cari dan pilih minimal 1 barang pada kotak pencarian di atas sebelum menyimpan dokumen penerimaan.');
    document.getElementById('itemSearchQuery')?.focus();
    return false;
  }
});

// Convert comma to dot automatically for decimal-enabled qty inputs
document.addEventListener('keydown', function(e) {
  if (e.target && e.target.classList && e.target.classList.contains('item-qty')) {
    if (e.key === ',') {
      e.preventDefault();
      const input = e.target;
      if (input.step === 'any' || input.step === '0.01') {
        const val = input.value;
        if (!val.includes('.')) {
          input.value = val + '.';
          input.dispatchEvent(new Event('input', { bubbles: true }));
        }
      }
    }
  }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
