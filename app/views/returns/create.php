<?php
$pageTitle = 'Input Pengembalian Barang';
include __DIR__ . '/../layout/header.php';
$selectedIssueId = $_GET['issue_id'] ?? '';
?>

<div class="card">
  <div class="card-header">
    <span><i class="bi bi-arrow-repeat me-2"></i>Form Pengembalian Barang Lapangan ke Gudang</span>
    <a href="<?= url('returns') ?>" class="btn btn-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
  </div>

  <div class="card-body">
    <form action="<?= url('returns/store') ?>" method="POST" enctype="multipart/form-data" id="form-return">
      <!-- HEADER RETURN -->
      <div style="background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1rem; color: #1e293b;">
          1. Referensi Transaksi Pengeluaran Sebelumnya
        </h3>

        <div class="form-row">
          <div class="form-group" style="grid-column: span 2; position: relative;" id="issueSearchContainer">
            <label for="issueSearchInput" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
              <span>Pilih No. Dokumen Pengeluaran <span class="text-danger">*</span></span>
              <span id="selectedIssueBadge" class="badge badge-success" style="display: none; font-size: 0.75rem; font-weight: 700; background: #059669; color: #fff;">
                <i class="bi bi-check-circle me-1"></i>Dokumen Terpilih
              </span>
            </label>

            <!-- Hidden input for form submission -->
            <input type="hidden" name="stock_issue_id" id="stock_issue_id" value="<?= htmlspecialchars($selectedIssueId) ?>" required>

            <!-- Search Live Input Container -->
            <div style="position: relative;">
              <i class="bi bi-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 0.95rem; pointer-events: none;"></i>
              <input type="text" 
                     id="issueSearchInput" 
                     class="form-control" 
                     placeholder="Ketik untuk cari No. Dokumen OUT / Proyek / Penerima / PT..." 
                     autocomplete="off" 
                     style="padding-left: 38px; padding-right: 36px; font-weight: 600; font-size: 0.92rem; height: 42px; border: 1.5px solid #cbd5e1; border-radius: 6px;">
              <button type="button" 
                      id="btnClearIssueSearch" 
                      style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 1.25rem; color: #94a3b8; cursor: pointer; display: none; line-height: 1; padding: 2px 6px;" 
                      title="Bersihkan pilihan & cari dokumen lain">
                &times;
              </button>
            </div>

            <!-- Dropdown Live Search Results -->
            <div id="issueDropdownResults" style="display: none; position: absolute; left: 0; right: 0; top: calc(100% - 6px); z-index: 1050; background: #ffffff; border: 1.5px solid #3b82f6; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.18), 0 8px 10px -6px rgba(0,0,0,0.1); max-height: 290px; overflow-y: auto;">
              <!-- Dynamic issue items rendered by JS -->
            </div>

            <small class="text-muted" style="display: block; margin-top: 4px;">Setiap pengembalian wajib mereferensikan transaksi pengeluaran asalnya. Ketik kata kunci apa saja untuk mencari.</small>
          </div>

          <div class="form-group">
            <label for="returned_by_name">Nama Pengembali Barang <span class="text-danger">*</span></label>
            <input type="text" name="returned_by_name" id="returned_by_name" class="form-control" required placeholder="Contoh: Andi Wijaya (Teknisi)">
          </div>

          <div class="form-group">
            <label for="returned_date">Tanggal Pengembalian <span class="text-danger">*</span></label>
            <input type="date" name="returned_date" id="returned_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label for="notes">Catatan Pengembalian</label>
          <input type="text" name="notes" id="notes" class="form-control" placeholder="Alasan pengembalian, status pengerjaan, dsb.">
        </div>
      </div>

      <!-- DETAIL BARANG PENGEMBALIAN -->
      <div style="margin-bottom: 1.5rem;">
        <div class="d-flex justify-between align-center mb-1">
          <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b;">2. Daftar Barang yang Dikembalikan</h3>
          <button type="button" class="btn btn-primary btn-sm" onclick="addReturnRow()"><i class="bi bi-plus-circle me-1"></i>Tambah Baris Barang</button>
        </div>

        <div class="table-responsive">
          <table class="table" id="return-items-table">
            <thead>
              <tr>
                <th style="width: 32%;">Barang ex Transaksi OUT</th>
                <th style="width: 13%;">Qty Kembali</th>
                <th style="width: 17%;">Status Pengembalian</th>
                <th style="width: 13%;">Kondisi</th>
                <th style="width: 20%;">Keterangan Sisa</th>
                <th style="width: 5%; text-align: center;">Hapus</th>
              </tr>
            </thead>
            <tbody id="return-items-tbody">
              <tr class="return-row" data-index="0">
                <td>
                  <select name="items[0][stock_issue_item_id]" class="form-select issue-item-dropdown" required onchange="updateReturnRowUnit(this)">
                    <option value="">Pilih Barang dari Dokumen</option>
                  </select>
                  <input type="hidden" name="items[0][warehouse_location_id]" value="1">
                </td>
                <td>
                  <div class="input-group">
                    <input type="number" step="1" min="1" name="items[0][qty_returned]" class="form-control item-qty" required placeholder="1">
                    <span class="unit-badge">-</span>
                  </div>
                </td>
                <td>
                  <select name="items[0][return_status]" class="form-select return-status-select">
                    <option value="sisa">Sisa Pekerjaan</option>
                    <option value="kelebihan">Kelebihan Bawa</option>
                    <option value="tidak_terpakai">Tidak Jadi Terpakai</option>
                    <option value="bekas">Material Bekas Pakai</option>
                    <option value="sisa_material">Sisa Material Potongan</option>
                    <option value="lainnya">Lainnya</option>
                  </select>
                </td>
                <td>
                  <select name="items[0][condition]" class="form-select">
                    <option value="baik">Baik</option>
                    <option value="potongan">Potongan</option>
                    <option value="scrap">Scrap</option>
                    <option value="rework">Perlu Rework</option>
                    <option value="damaged">Rusak</option>
                  </select>
                </td>
                <td>
                  <input type="text" name="items[0][notes]" class="form-control" placeholder="Keterangan jika ada sisa...">
                </td>
                <td class="text-center">
                  <button type="button" class="btn btn-danger btn-sm" onclick="removeReturnRow(this)"><i class="bi bi-trash"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- DOKUMENTASI PENGEMBALIAN -->
      <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
        <label for="return_photo"><i class="bi bi-camera me-1"></i>Foto Bukti Barang Pengembalian / Sisa Material <strong>(WAJIB DISERTAKAN)</strong></label>
        <input type="file" name="attachment_file" id="return_photo" class="form-control" accept="image/*">
        <small class="text-muted">Untuk sisa material potongan atau barang rusak/bekas, sertakan foto fisik barang.</small>
      </div>

      <div class="d-flex justify-between">
        <a href="<?= url('returns') ?>" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem;">
          <i class="bi bi-check-circle me-1"></i>Simpan Pengembalian & Tambah Kembali Stok
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// --- LIVE SEARCH DOKUMEN PENGELUARAN ---
let allIssues = <?= json_encode($issues ?? []) ?>;
const initialSelectedId = "<?= htmlspecialchars($selectedIssueId) ?>";
let issueSearchDebounceTimer = null;

function escapeHtml(text) {
  if (!text) return '';
  return String(text)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function formatDate(isoStr) {
  if (!isoStr) return '-';
  try {
    const d = new Date(isoStr);
    if (isNaN(d.getTime())) return isoStr;
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
  } catch(e) {
    return isoStr;
  }
}

function getCompanyBadge(issue) {
  const code = (issue.company && issue.company.code ? issue.company.code : '').toUpperCase();
  if (code === 'KJG') {
    return '<span style="background: #990000; color: #ffffff; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; border: 1px solid #700000; letter-spacing: 0.5px;">KJG</span>';
  } else if (code === 'LNP') {
    return '<span style="background: #0284c7; color: #ffffff; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; border: 1px solid #0369a1; letter-spacing: 0.5px;">LNP</span>';
  }
  return '<span style="background: #ecfdf5; color: #059669; font-size: 0.72rem; font-weight: 600; padding: 2px 8px; border-radius: 4px; border: 1px solid #a7f3d0;">Gudang Bersama</span>';
}

function matchesIssue(issue, tokens) {
  if (!tokens || tokens.length === 0) return true;
  const issueNo = (issue.issue_number || '').toLowerCase();
  const proj = (issue.project_name || '').toLowerCase();
  const recip = (issue.recipient_name || '').toLowerCase();
  const req = (issue.requester_name || '').toLowerCase();
  const notes = (issue.notes || '').toLowerCase();
  const compCode = (issue.company && issue.company.code ? issue.company.code : '').toLowerCase();
  const compName = (issue.company && issue.company.name ? issue.company.name : '').toLowerCase();
  const dateStr = (issue.issued_date || '').toLowerCase();

  const combined = `${issueNo} ${proj} ${recip} ${req} ${notes} ${compCode} ${compName} ${dateStr}`;
  return tokens.every(token => combined.includes(token));
}

function renderIssueResults(query = '') {
  const dropdown = document.getElementById('issueDropdownResults');
  if (!dropdown) return;

  const tokens = query.toLowerCase().trim().split(/\s+/).filter(Boolean);
  const filtered = allIssues.filter(issue => matchesIssue(issue, tokens));

  if (filtered.length === 0) {
    dropdown.innerHTML = `
      <div style="padding: 16px; text-align: center; color: #64748b; font-size: 0.88rem;">
        <i class="bi bi-search" style="font-size: 1.25rem; display: block; margin-bottom: 6px; color: #94a3b8;"></i>
        Tidak ditemukan dokumen pengeluaran yang cocok dengan: <strong>"${escapeHtml(query)}"</strong>
      </div>
    `;
    dropdown.style.display = 'block';
    return;
  }

  let html = `<div style="padding: 4px 0;">`;
  filtered.forEach((issue, idx) => {
    const badgeHtml = getCompanyBadge(issue);
    const dateFormatted = formatDate(issue.issued_date);
    const isSelected = String(document.getElementById('stock_issue_id').value) === String(issue.id);
    const bgStyle = isSelected ? 'background: #eff6ff; border-left: 3px solid #2563eb;' : 'border-left: 3px solid transparent;';

    html += `
      <div class="issue-search-item" 
           data-id="${issue.id}" 
           data-index="${idx}"
           style="padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: all 0.15s ease; ${bgStyle}">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
          <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-weight: 700; color: #1e3a8a; font-size: 0.93rem;">${escapeHtml(issue.issue_number)}</span>
            ${badgeHtml}
          </div>
          <span style="font-size: 0.78rem; color: #64748b;">
            <i class="bi bi-calendar3 me-1"></i>${dateFormatted}
          </span>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 14px; font-size: 0.82rem; color: #334155;">
          <span><strong style="color: #64748b;">Proyek:</strong> ${escapeHtml(issue.project_name || '-')}</span>
          <span><strong style="color: #64748b;">Penerima:</strong> ${escapeHtml(issue.recipient_name || '-')}</span>
          ${issue.notes ? `<span style="color: #94a3b8; font-style: italic;">(${escapeHtml(issue.notes)})</span>` : ''}
        </div>
      </div>
    `;
  });
  html += `</div>`;

  dropdown.innerHTML = html;
  dropdown.style.display = 'block';

  dropdown.querySelectorAll('.issue-search-item').forEach(itemEl => {
    itemEl.addEventListener('click', function(e) {
      e.stopPropagation();
      const issueId = this.dataset.id;
      const selected = allIssues.find(i => String(i.id) === String(issueId));
      if (selected) {
        selectIssue(selected);
      }
    });
    itemEl.addEventListener('mouseenter', function() {
      dropdown.querySelectorAll('.issue-search-item').forEach(el => {
        if (!el.style.borderLeft.includes('#2563eb')) el.style.background = '';
      });
      this.style.background = '#f8fafc';
    });
  });
}

function selectIssue(issue) {
  document.getElementById('stock_issue_id').value = issue.id;

  const compText = issue.company && issue.company.code ? `[${issue.company.code}] ` : '';
  const displayText = `${compText}${issue.issue_number} — ${issue.project_name || 'Tanpa Proyek'} (${issue.recipient_name || '-'})`;
  
  const input = document.getElementById('issueSearchInput');
  input.value = displayText;
  input.style.borderColor = '#10b981';
  input.style.backgroundColor = '#f0fdf4';

  document.getElementById('selectedIssueBadge').style.display = 'inline-block';
  document.getElementById('btnClearIssueSearch').style.display = 'block';
  document.getElementById('issueDropdownResults').style.display = 'none';

  loadIssueDetails(issue.id);
}

function clearIssueSelection() {
  document.getElementById('stock_issue_id').value = '';
  
  const input = document.getElementById('issueSearchInput');
  input.value = '';
  input.style.borderColor = '#cbd5e1';
  input.style.backgroundColor = '#ffffff';

  document.getElementById('selectedIssueBadge').style.display = 'none';
  document.getElementById('btnClearIssueSearch').style.display = 'none';
  document.getElementById('returned_by_name').value = '';

  currentIssueItems = [];
  populateIssueDropdowns();

  renderIssueResults('');
  input.focus();
}

function setupIssueLiveSearch() {
  const input = document.getElementById('issueSearchInput');
  const dropdown = document.getElementById('issueDropdownResults');
  const btnClear = document.getElementById('btnClearIssueSearch');
  const container = document.getElementById('issueSearchContainer');

  if (!input || !dropdown) return;

  input.addEventListener('focus', function() {
    if (this.style.backgroundColor === 'rgb(240, 253, 244)' || this.style.backgroundColor === '#f0fdf4') {
      renderIssueResults('');
    } else {
      renderIssueResults(this.value);
    }
  });

  input.addEventListener('click', function() {
    if (dropdown.style.display !== 'block') {
      renderIssueResults(this.value);
    }
  });

  input.addEventListener('input', function() {
    const q = this.value;
    input.style.borderColor = '#3b82f6';
    input.style.backgroundColor = '#ffffff';
    document.getElementById('selectedIssueBadge').style.display = 'none';
    document.getElementById('stock_issue_id').value = '';

    if (q.trim()) {
      btnClear.style.display = 'block';
    } else {
      btnClear.style.display = 'none';
    }

    renderIssueResults(q);

    // Fallback debounced server search
    clearTimeout(issueSearchDebounceTimer);
    if (q.trim().length >= 2) {
      issueSearchDebounceTimer = setTimeout(() => {
        fetch(`index.php?r=issues/ajax-search&q=${encodeURIComponent(q.trim())}`)
          .then(res => res.json())
          .then(resData => {
            if (resData.success && Array.isArray(resData.data)) {
              let added = false;
              resData.data.forEach(item => {
                if (!allIssues.some(existing => String(existing.id) === String(item.id))) {
                  allIssues.push(item);
                  added = true;
                }
              });
              if (added) {
                renderIssueResults(input.value);
              }
            }
          })
          .catch(err => console.error('Error fetching issue search:', err));
      }, 350);
    }
  });

  if (btnClear) {
    btnClear.addEventListener('click', function(e) {
      e.stopPropagation();
      clearIssueSelection();
    });
  }

  // Klik di luar dropdown untuk menutup
  document.addEventListener('click', function(e) {
    if (container && !container.contains(e.target)) {
      dropdown.style.display = 'none';
    }
  });

  // Validasi form submit agar wajib pilih dokumen OUT
  const form = document.getElementById('form-return');
  if (form) {
    form.addEventListener('submit', function(e) {
      const issueId = document.getElementById('stock_issue_id').value;
      if (!issueId) {
        e.preventDefault();
        alert('Silakan pilih No. Dokumen Pengeluaran terlebih dahulu melalui kolom pencarian.');
        input.focus();
      }
    });
  }

  // Inisialisasi awal jika ada initialSelectedId
  if (initialSelectedId) {
    const found = allIssues.find(i => String(i.id) === String(initialSelectedId));
    if (found) {
      selectIssue(found);
    } else {
      fetch(`index.php?r=issues/ajax-get&id=${initialSelectedId}`)
        .then(r => r.json())
        .then(res => {
          if (res.success && res.data) {
            allIssues.unshift(res.data);
            selectIssue(res.data);
          }
        })
        .catch(e => console.error(e));
    }
  }
}

// --- LOGIKA TABEL BARANG RETURN ---
let currentIssueItems = [];
let returnRowCount = 1;

function loadIssueDetails(issueId) {
  if (!issueId) {
    currentIssueItems = [];
    populateIssueDropdowns();
    return;
  }

  fetch(`index.php?r=issues/ajax-get&id=${issueId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success && data.data && data.data.items) {
        currentIssueItems = data.data.items;
        populateIssueDropdowns();
        if (data.data.recipient_name) {
          document.getElementById('returned_by_name').value = data.data.recipient_name;
        }
      }
    })
    .catch(err => console.error(err));
}

function updateReturnRowUnit(selectEl) {
  const selectedOption = selectEl.options[selectEl.selectedIndex];
  const unit = selectedOption ? (selectedOption.dataset.unit || '').trim() : '';
  const row = selectEl.closest('tr');
  if (!row) return;

  const unitBadge = row.querySelector('.unit-badge');
  const qtyInput = row.querySelector('.item-qty');

  if (unitBadge) {
    unitBadge.textContent = unit || '-';
    if (unit) {
      unitBadge.classList.add('has-unit');
    } else {
      unitBadge.classList.remove('has-unit');
    }
  }

  if (qtyInput) {
    const u = unit.toUpperCase();
    if (['MTR', 'METER', 'M', 'LTR', 'LITER', 'L', 'KG', 'KILOGRAM', 'GR', 'GRAM'].includes(u)) {
      qtyInput.step = 'any';
      qtyInput.min = '0.01';
      qtyInput.placeholder = '0.00';
    } else {
      qtyInput.step = '1';
      qtyInput.min = '1';
      qtyInput.placeholder = '1';
    }
    if (selectedOption && selectedOption.dataset.max) {
      qtyInput.max = selectedOption.dataset.max;
    }
  }
}

function populateIssueDropdowns() {
  document.querySelectorAll('.issue-item-dropdown').forEach(select => {
    const prevVal = select.value;
    select.innerHTML = '<option value="">Pilih Barang dari Dokumen OUT</option>';
    currentIssueItems.forEach(item => {
      const opt = document.createElement('option');
      opt.value = item.id;
      const unitCode = item.item && item.item.unit ? item.item.unit.code : '';
      opt.dataset.unit = unitCode;
      opt.dataset.max = item.remaining_qty;
      opt.text = `${item.item.item_code} - ${item.item.name} (Sisa belum kembali: ${item.remaining_qty} ${unitCode})`;
      if (opt.value === prevVal) opt.selected = true;
      select.appendChild(opt);
    });
    if (select.value) updateReturnRowUnit(select);
  });
}

function addReturnRow() {
  const tbody = document.getElementById('return-items-tbody');
  const tr = document.createElement('tr');
  tr.className = 'return-row';
  tr.dataset.index = returnRowCount;

  tr.innerHTML = `
    <td>
      <select name="items[${returnRowCount}][stock_issue_item_id]" class="form-select issue-item-dropdown" required onchange="updateReturnRowUnit(this)">
        <option value="">Pilih Barang dari Dokumen OUT</option>
      </select>
      <input type="hidden" name="items[${returnRowCount}][warehouse_location_id]" value="1">
    </td>
    <td>
      <div class="input-group">
        <input type="number" step="1" min="1" name="items[${returnRowCount}][qty_returned]" class="form-control item-qty" required placeholder="1">
        <span class="unit-badge">-</span>
      </div>
    </td>
    <td>
      <select name="items[${returnRowCount}][return_status]" class="form-select return-status-select">
        <option value="sisa">Sisa Pekerjaan</option>
        <option value="kelebihan">Kelebihan Bawa</option>
        <option value="tidak_terpakai">Tidak Jadi Terpakai</option>
        <option value="bekas">Material Bekas Pakai</option>
        <option value="sisa_material">Sisa Material Potongan</option>
        <option value="lainnya">Lainnya</option>
      </select>
    </td>
    <td>
      <select name="items[${returnRowCount}][condition]" class="form-select">
        <option value="baik">Baik</option>
        <option value="potongan">Potongan</option>
        <option value="scrap">Scrap</option>
        <option value="rework">Perlu Rework</option>
        <option value="damaged">Rusak</option>
      </select>
    </td>
    <td>
      <input type="text" name="items[${returnRowCount}][notes]" class="form-control" placeholder="Keterangan jika ada sisa...">
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-danger btn-sm" onclick="removeReturnRow(this)"><i class="bi bi-trash"></i></button>
    </td>
  `;

  tbody.appendChild(tr);
  returnRowCount++;
  populateIssueDropdowns();
}

function removeReturnRow(btn) {
  const tbody = document.getElementById('return-items-tbody');
  if (tbody.querySelectorAll('tr').length <= 1) {
    alert('Pengembalian barang minimal harus memiliki 1 item.');
    return;
  }
  btn.closest('tr').remove();
}

document.addEventListener('DOMContentLoaded', function() {
  setupIssueLiveSearch();
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
