<?php
$pageTitle = 'Form Pengeluaran Barang Keluar';
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
  <div class="card-header d-flex justify-between align-center" style="border-left: 5px solid #2563eb;">
    <div class="d-flex align-center gap-1">
      <span><i class="bi bi-box-arrow-up me-1 text-primary"></i> Form Pengeluaran Barang Keluar</span>
      <span class="badge badge-primary" style="font-size: 0.8rem;">
        <i class="bi bi-clock-history me-1"></i> Sistem FIFO
      </span>
    </div>
    <a href="<?= url('issues') ?>" class="btn btn-outline btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
    </a>
  </div>

  <div class="card-body">
    <form action="<?= url('issues/store') ?>" method="POST" enctype="multipart/form-data" id="form-issue" onsubmit="return validateIssueForm()">
      
      <!-- 1. KOTAK PENCARIAN LIVE SEPERTI MENU DAFTAR BARANG -->
      <div style="background: #ffffff; padding: 1.25rem; border-radius: 8px; border: 2px dashed #2563eb; margin-bottom: 1.5rem;">
        <div>
          <h3 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 0.2rem;">
            <i class="bi bi-search me-1 text-primary"></i> 1. Cari & Tambahkan Barang Keluar
          </h3>
          <small class="text-muted">Ketik nama barang, kode item, atau spesifikasi. Barang dengan nama yang sama otomatis digabungkan total stoknya.</small>
        </div>

        <!-- Kolom Input Pencarian Live (Sama seperti menu Daftar Barang) -->
        <div style="position: relative; margin-top: 0.75rem;">
          <input type="text" id="liveSearchInput" class="form-control" 
                 style="padding-left: 2.75rem; padding-right: 2.5rem; font-size: 0.95rem; height: 46px; border-radius: 8px; border: 1.5px solid #cbd5e1;" 
                 placeholder="Ketik untuk mencari langsung nama barang (cth: Plat, Baut M8, Pipa, Klem), ID/kode, spesifikasi..."
                 autocomplete="off" autofocus>
          <i class="bi bi-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.1rem; pointer-events: none;"></i>
          <button type="button" id="btnClearSearch" 
                  style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); display: none; background: transparent; border: none; font-size: 1.2rem; color: #94a3b8; cursor: pointer; padding: 0.25rem 0.5rem;"
                  title="Hapus pencarian">&times;</button>
        </div>

        <!-- Wadah Hasil Pencarian Live (Tabel Seperti Menu Daftar Barang) -->
        <div id="liveSearchResultsContainer" style="margin-top: 1rem; display: none;"></div>
      </div>

      <!-- TABEL DAFTAR BARANG YANG DIKELUARKAN (DRAFT PENGELUARAN) -->
      <div style="margin-bottom: 1.5rem;" id="issue-table-section">
        <div class="d-flex justify-between align-center mb-1">
          <div>
            <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 0;">
              <i class="bi bi-boxes me-1 text-primary"></i> Daftar Barang yang Dikeluarkan
            </h3>
            <small class="text-muted">Barang yang dipilih dari pencarian di atas otomatis masuk ke tabel ini untuk ditentukan kuantitas pengeluarannya.</small>
          </div>
          <span id="selectedItemsCount" class="badge badge-secondary" style="font-size: 0.8rem;">0 barang dipilih</span>
        </div>

        <div class="table-responsive">
          <table class="table" id="issue-items-table">
            <thead>
              <tr>
                <th style="width: 44%;">Nama Barang & Spesifikasi</th>
                <th style="width: 20%;">Lokasi Pengambilan Rak</th>
                <th style="width: 18%;">Qty Keluar</th>
                <th style="width: 13%;">Keterangan</th>
                <th style="width: 5%; text-align: center;">Hapus</th>
              </tr>
            </thead>
            <tbody id="issue-items-tbody">
              <tr id="empty-issue-row">
                <td colspan="5" class="text-center text-muted" style="padding: 2.25rem 1rem;">
                  <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; color: #cbd5e1;"></i>
                  <span style="font-weight: 600; color: #64748b; font-size: 0.95rem;">Belum ada barang di daftar pengeluaran.</span>
                  <div style="font-size: 0.825rem; color: #94a3b8; margin-top: 0.25rem;">
                    Ketik nama atau kode barang pada kolom pencarian di atas, lalu klik tombol <strong>"Pilih & Tambahkan"</strong>.
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 2. INFORMASI PENGAMBILAN & KEPERLUAN -->
      <div style="background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1rem; color: #1e293b;">
          <i class="bi bi-person-badge me-1 text-primary"></i> 2. Informasi Pengambilan & Keperluan
        </h3>

        <div class="form-row">
          <div class="form-group" style="flex: 1;">
            <label for="recipient_name"><i class="bi bi-person me-1"></i> Nama Pengambil <span class="text-danger">*</span></label>
            <input type="text" name="recipient_name" id="recipient_name" class="form-control" required 
                   placeholder="Contoh: Budi Santoso (Mekanik / Tim Lapangan)">
          </div>

          <div class="form-group" style="flex: 1;">
            <label for="requester_name"><i class="bi bi-diagram-3 me-1"></i> Divisi <span class="text-danger">*</span></label>
            <input type="text" name="requester_name" id="requester_name" class="form-control" required 
                   placeholder="Contoh: Workshop, Fabrikasi, Maintenance, Lapangan...">
          </div>
        </div>

        <div class="form-row" style="margin-top: 0.75rem;">
          <div class="form-group" style="flex: 2;">
            <label for="project_name"><i class="bi bi-briefcase me-1"></i> Keperluan / Proyek <span class="text-danger">*</span></label>
            <input type="text" name="project_name" id="project_name" class="form-control" required 
                   placeholder="Contoh: Fabrikasi Conveyor Line 2 / Perbaikan Mesin Pompa">
          </div>

          <div class="form-group" style="flex: 1;">
            <label for="issued_date"><i class="bi bi-calendar3 me-1"></i> Tanggal Pengeluaran <span class="text-danger">*</span></label>
            <input type="date" name="issued_date" id="issued_date" class="form-control" required value="<?= date('Y-m-d') ?>">
          </div>
        </div>

        <div class="form-row" style="margin-top: 0.75rem;">
          <div class="form-group" style="flex: 1;">
            <label for="notes"><i class="bi bi-chat-left-dots me-1"></i> Catatan Pengeluaran (Opsional)</label>
            <input type="text" name="notes" id="notes" class="form-control" 
                   placeholder="Instruksi khusus pemakaian, referensi kerja, dsb.">
          </div>
        </div>
      </div>

      <!-- 3. DOKUMENTASI SERAH TERIMA (OPSIONAL) -->
      <div style="background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
        <label for="handover_photo" style="font-weight: 600; color: #1e293b; margin-bottom: 0.35rem; display: block;">
          <i class="bi bi-camera me-1 text-primary"></i> 3. Foto Dokumentasi Serah Terima / Bukti Pengeluaran (Opsional)
        </label>
        <input type="file" name="attachment_file" id="handover_photo" class="form-control" accept="image/*">
        <small class="text-muted">Foto serah terima fisik barang atau surat bukti pengambilan (Format: JPG, PNG, WEBP)</small>
      </div>

      <div class="d-flex justify-between align-center">
        <a href="<?= url('issues') ?>" class="btn btn-secondary">
          <i class="bi bi-x-circle me-1"></i> Batal
        </a>
        <button type="submit" class="btn btn-primary" id="btnSubmitIssue" style="padding: 0.65rem 1.75rem; font-weight: 600; font-size: 0.95rem;">
          <i class="bi bi-check-circle me-1"></i> Simpan Pengeluaran & Kurangi Stok
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Available locations from backend
const warehouseLocations = <?= json_encode(array_map(function($l) {
  return ['id' => $l['id'], 'code' => $l['code']];
}, $locations ?? [])) ?>;

let issueItemIndex = 0;
let searchDebounceTimer = null;
const addedItemIds = new Set();
const addedItemNames = new Set();

const searchInput = document.getElementById('liveSearchInput');
const btnClearSearch = document.getElementById('btnClearSearch');
const resultsContainer = document.getElementById('liveSearchResultsContainer');

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function formatNumber(num) {
  const n = parseFloat(num) || 0;
  return n.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

// Live Search Listener
if (searchInput) {
  searchInput.addEventListener('input', function() {
    const q = this.value.trim();
    if (btnClearSearch) {
      btnClearSearch.style.display = q ? 'block' : 'none';
    }

    clearTimeout(searchDebounceTimer);
    if (!q) {
      resultsContainer.style.display = 'none';
      resultsContainer.innerHTML = '';
      return;
    }

    searchDebounceTimer = setTimeout(() => {
      performLiveSearch(q);
    }, 150);
  });

  searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      const q = this.value.trim();
      if (q) {
        clearTimeout(searchDebounceTimer);
        performLiveSearch(q);
      }
    }
  });
}

if (btnClearSearch) {
  btnClearSearch.addEventListener('click', function() {
    searchInput.value = '';
    btnClearSearch.style.display = 'none';
    resultsContainer.style.display = 'none';
    resultsContainer.innerHTML = '';
    searchInput.focus();
  });
}

function performLiveSearch(query) {
  resultsContainer.style.display = 'block';
  resultsContainer.innerHTML = `
    <div style="padding: 1.5rem; text-align: center; color: #64748b;">
      <i class="bi bi-hourglass-split me-1"></i> Sedang mencari inventori barang...
    </div>
  `;

  // Fetch with merge_by_name=1 to group identical items across PTs
  fetch(`index.php?r=items/search-ajax&merge_by_name=1&q=${encodeURIComponent(query)}`)
    .then(r => r.json())
    .then(res => {
      const items = res.data || [];
      renderLiveSearchTable(query, items);
    })
    .catch(err => {
      resultsContainer.innerHTML = `
        <div style="padding: 0.75rem; color: #dc2626; font-size: 0.85rem;">
          <i class="bi bi-exclamation-triangle me-1"></i> Gagal melakukan pencarian: ${escapeHtml(err.message || err)}
        </div>
      `;
    });
}

function renderLiveSearchTable(query, rawItems) {
  // Client-side merge safety: ensure any item with identical name is united into ONE row
  const mergedMap = new Map();
  rawItems.forEach(it => {
    const normName = (it.name || '').trim().toLowerCase();
    const stock = parseFloat(it.total_stock || 0);

    if (!mergedMap.has(normName)) {
      mergedMap.set(normName, {
        id: it.id,
        item_code: it.item_code || '',
        name: it.name || '',
        specification: it.specification || '',
        category: it.category ? it.category.name : '-',
        unit_code: it.unit ? it.unit.code : '-',
        total_stock: stock,
      });
    } else {
      const existing = mergedMap.get(normName);
      existing.total_stock += stock;
      if (!existing.specification && it.specification) {
        existing.specification = it.specification;
      }
    }
  });

  const items = Array.from(mergedMap.values());

  if (items.length === 0) {
    resultsContainer.innerHTML = `
      <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 8px; padding: 1.25rem;">
        <div style="font-weight: 700; color: #92400e; font-size: 0.95rem; margin-bottom: 0.25rem;">
          <i class="bi bi-exclamation-circle-fill me-1" style="color: #d97706;"></i>
          Barang "<strong>${escapeHtml(query)}</strong>" tidak ditemukan di inventori gudang
        </div>
        <div style="color: #78350f; font-size: 0.85rem;">
          Pastikan ejaan kata kunci benar, atau cek kembali apakah barang sudah terdaftar di katalog master.
        </div>
      </div>
    `;
    return;
  }

  // Render Table Layout (persis seperti menu Daftar Barang)
  let html = `
    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #fff;">
      <div style="padding: 0.65rem 1rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 0.85rem; font-weight: 700; color: #334155; display: flex; justify-content: space-between; align-items: center;">
        <span><i class="bi bi-check2-circle text-success me-1"></i> Menampilkan <strong>${items.length}</strong> barang ditemukan:</span>
        <small class="text-muted">Klik "Pilih & Tambahkan" untuk memasukkan ke daftar pengeluaran</small>
      </div>

      <div class="table-responsive" style="max-height: 340px; overflow-y: auto;">
        <table class="table table-hover table-sticky-header mb-0" id="liveSearchTable">
          <thead style="background: #f1f5f9; position: sticky; top: 0; z-index: 2;">
            <tr>
              <th style="width: 52%;">Nama Barang & Spesifikasi</th>
              <th style="width: 16%;">Total Stok</th>
              <th style="width: 14%;">Satuan</th>
              <th style="width: 18%; text-align: right;">Aksi</th>
            </tr>
          </thead>
          <tbody>
  `;

  items.forEach(it => {
    const itemName = escapeHtml(it.name);
    const unitCode = escapeHtml(it.unit_code);
    const spec = escapeHtml(it.specification);
    const totalStock = it.total_stock;
    const isOutOfStock = (totalStock <= 0);

    const itemJsonStr = JSON.stringify({
      id: it.id,
      item_code: it.item_code,
      name: it.name,
      specification: it.specification,
      unit_code: it.unit_code,
      total_stock: totalStock
    }).replace(/"/g, '&quot;');

    html += `
      <tr style="transition: background 0.15s;">
        <td>
          <strong style="color: #0f172a; font-size: 0.95rem;">${itemName}</strong>
          ${spec ? `<div class="text-muted" style="font-size: 0.75rem; margin-top: 2px;">${spec}</div>` : ''}
        </td>
        <td>
          <span class="fw-bold" style="font-size: 1rem; color: ${isOutOfStock ? '#dc2626' : '#16a34a'};">
            ${formatNumber(totalStock)}
          </span>
          <div style="font-size: 0.7rem; margin-top: 1px;">
            ${isOutOfStock ? 
              '<span class="badge badge-danger" style="font-size: 0.65rem; padding: 1px 5px;">HABIS</span>' : 
              '<span class="badge badge-success" style="font-size: 0.65rem; padding: 1px 5px;">TERSEDIA</span>'}
          </div>
        </td>
        <td>
          <span style="font-weight: 600; color: #334155;">${unitCode}</span>
        </td>
        <td class="text-right">
          ${isOutOfStock ? `
            <button type="button" class="btn btn-secondary btn-sm" disabled style="opacity: 0.55; font-size: 0.8rem; padding: 0.35rem 0.65rem;">
              Stok Habis
            </button>
          ` : `
            <button type="button" class="btn btn-primary btn-sm" onclick='addItemToIssueFromSearch(${itemJsonStr})' style="font-weight: 600; font-size: 0.8rem; padding: 0.35rem 0.75rem;">
              <i class="bi bi-plus-lg me-1"></i> Pilih & Tambahkan
            </button>
          `}
        </td>
      </tr>
    `;
  });

  html += `
          </tbody>
        </table>
      </div>
    </div>
  `;

  resultsContainer.innerHTML = html;
}

function addItemToIssueFromSearch(item) {
  const tbody = document.getElementById('issue-items-tbody');
  const emptyRow = document.getElementById('empty-issue-row');
  const normName = (item.name || '').trim().toLowerCase();

  // If item with the same name or ID is already in the list, highlight & focus
  const existingRow = tbody.querySelector(`tr[data-item-name="${escapeHtml(normName)}"]`) || 
                      tbody.querySelector(`tr[data-item-id="${item.id}"]`);

  if (existingRow) {
    const qtyInput = existingRow.querySelector('.item-qty');
    if (qtyInput) {
      qtyInput.focus();
      qtyInput.select();
    }
    existingRow.style.backgroundColor = '#fef3c7';
    setTimeout(() => { existingRow.style.backgroundColor = ''; }, 1200);
    return;
  }

  // Remove empty placeholder row if exists
  if (emptyRow) {
    emptyRow.remove();
  }

  const u = (item.unit_code || '').toUpperCase();
  const isDec = ['MTR', 'METER', 'M', 'LTR', 'LITER', 'L', 'KG', 'KILOGRAM', 'GR', 'GRAM'].includes(u);
  const step = isDec ? 'any' : '1';
  const min = isDec ? '0.01' : '1';
  const placeholder = isDec ? '0.00' : '1';

  // Build Location Select Options
  let locationOptionsHtml = '';
  if (warehouseLocations && warehouseLocations.length > 0) {
    warehouseLocations.forEach(loc => {
      locationOptionsHtml += `<option value="${loc.id}">${escapeHtml(loc.code)}</option>`;
    });
  } else {
    locationOptionsHtml = `<option value="1">A-01-01</option>`;
  }

  const tr = document.createElement('tr');
  tr.className = 'issue-row';
  tr.dataset.index = issueItemIndex;
  tr.dataset.itemId = item.id;
  tr.dataset.itemName = normName;
  tr.style.animation = 'fadeIn 0.25s ease-in-out';

  tr.innerHTML = `
    <td>
      <strong style="color: #0f172a; font-size: 0.95rem;">
        ${escapeHtml(item.name)}
      </strong>
      <div style="font-size: 0.8rem; color: #64748b; margin-top: 3px; display: flex; align-items: center; gap: 0.75rem;">
        <span>Stok Tersedia: <strong style="color: #16a34a;">${formatNumber(item.total_stock)} ${escapeHtml(item.unit_code)}</strong></span>
        ${item.specification ? `<span class="text-muted">(${escapeHtml(item.specification)})</span>` : ''}
      </div>
      <input type="hidden" name="items[${issueItemIndex}][item_id]" value="${item.id}">
    </td>
    <td>
      <select name="items[${issueItemIndex}][warehouse_location_id]" class="form-select" required>
        ${locationOptionsHtml}
      </select>
    </td>
    <td>
      <div class="input-group">
        <input type="number" step="${step}" min="${min}" max="${item.total_stock}" 
               name="items[${issueItemIndex}][qty_issued]" class="form-control item-qty" required 
               placeholder="${placeholder}" autofocus oninput="validateRowQty(this, ${item.total_stock})">
        <span class="unit-badge has-unit">${escapeHtml(item.unit_code || '-')}</span>
      </div>
      <div class="qty-warning text-danger" style="font-size: 0.75rem; margin-top: 2px; display: none;"></div>
    </td>
    <td>
      <input type="text" name="items[${issueItemIndex}][notes]" class="form-control item-notes" placeholder="Catatan baris...">
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-danger btn-sm" onclick="removeIssueRow(this, '${escapeHtml(normName)}')" title="Hapus dari daftar">
        <i class="bi bi-trash"></i>
      </button>
    </td>
  `;

  tbody.appendChild(tr);
  addedItemIds.add(item.id);
  addedItemNames.add(normName);
  issueItemIndex++;
  updateSelectedCount();

  // Focus to qty input of newly added row
  const qtyInput = tr.querySelector('.item-qty');
  if (qtyInput) {
    qtyInput.focus();
  }
}

function validateRowQty(input, maxStock) {
  const val = parseFloat(input.value);
  const warningEl = input.closest('td').querySelector('.qty-warning');
  if (warningEl) {
    if (!isNaN(val) && val > maxStock) {
      warningEl.textContent = `Melebihi total stok (${formatNumber(maxStock)})`;
      warningEl.style.display = 'block';
      input.style.borderColor = '#dc2626';
    } else {
      warningEl.style.display = 'none';
      input.style.borderColor = '';
    }
  }
}

function removeIssueRow(btn, normName) {
  const row = btn.closest('tr');
  if (row) {
    const itemId = parseInt(row.dataset.itemId);
    row.remove();
    if (itemId) addedItemIds.delete(itemId);
    if (normName) addedItemNames.delete(normName);
  }

  const tbody = document.getElementById('issue-items-tbody');
  if (tbody.querySelectorAll('tr.issue-row').length === 0) {
    tbody.innerHTML = `
      <tr id="empty-issue-row">
        <td colspan="5" class="text-center text-muted" style="padding: 2.25rem 1rem;">
          <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; color: #cbd5e1;"></i>
          <span style="font-weight: 600; color: #64748b; font-size: 0.95rem;">Belum ada barang di daftar pengeluaran.</span>
          <div style="font-size: 0.825rem; color: #94a3b8; margin-top: 0.25rem;">
            Ketik nama atau kode barang pada kolom pencarian di atas, lalu klik <strong>"Pilih & Tambahkan"</strong>.
          </div>
        </td>
      </tr>
    `;
  }
  updateSelectedCount();
}

function updateSelectedCount() {
  const count = document.querySelectorAll('#issue-items-tbody tr.issue-row').length;
  const countBadge = document.getElementById('selectedItemsCount');
  if (countBadge) {
    countBadge.textContent = `${count} barang dipilih`;
    countBadge.className = count > 0 ? 'badge badge-primary' : 'badge badge-secondary';
  }
}

function validateIssueForm() {
  const rows = document.querySelectorAll('#issue-items-tbody tr.issue-row');
  if (rows.length === 0) {
    alert('Harap cari dan tambahkan minimal 1 barang ke dalam daftar pengeluaran.');
    if (searchInput) searchInput.focus();
    return false;
  }

  let valid = true;
  rows.forEach(r => {
    const qtyInput = r.querySelector('.item-qty');
    const val = parseFloat(qtyInput ? qtyInput.value : 0);
    if (!val || val <= 0) {
      alert('Kuantitas keluar pada setiap barang harus lebih dari 0.');
      if (qtyInput) qtyInput.focus();
      valid = false;
      return false;
    }
  });

  return valid;
}

// Convert comma to dot automatically for decimal qty inputs
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
