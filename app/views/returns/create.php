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
          <div class="form-group" style="grid-column: span 2;">
            <label for="stock_issue_id">Pilih No. Dokumen Pengeluaran <span class="text-danger">*</span></label>
            <select name="stock_issue_id" id="stock_issue_id" class="form-select" required onchange="loadIssueDetails(this.value)">
              <option value="">Pilih Transaksi Barang Keluar</option>
              <?php foreach ($issues as $iss): ?>
                <option value="<?= $iss['id'] ?>" <?= $selectedIssueId == $iss['id'] ? 'selected' : '' ?>>
                  [<?= $iss['company']['code'] ?? '' ?>] <?= htmlspecialchars($iss['issue_number']) ?> - Proyek: <?= htmlspecialchars($iss['project_name']) ?> (Penerima: <?= htmlspecialchars($iss['recipient_name']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">Setiap pengembalian wajib mereferensikan transaksi pengeluaran asalnya.</small>
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
          <label for="notes">Catatan Pengembalian (Opsional)</label>
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
                <th style="width: 30%;">Barang ex Transaksi OUT</th>
                <th style="width: 15%;">Lokasi Simpan Rak</th>
                <th style="width: 12%;">Qty Kembali</th>
                <th style="width: 18%;">Status Pengembalian</th>
                <th style="width: 12%;">Kondisi</th>
                <th style="width: 8%;">Detail Sisa</th>
                <th style="width: 5%;">Hapus</th>
              </tr>
            </thead>
            <tbody id="return-items-tbody">
              <tr class="return-row" data-index="0">
                <td>
                  <select name="items[0][stock_issue_item_id]" class="form-select issue-item-dropdown" required onchange="updateReturnRowUnit(this)">
                    <option value="">Pilih Barang dari Dokumen</option>
                  </select>
                </td>
                <td>
                  <select name="items[0][warehouse_location_id]" class="form-select" required>
                    <?php foreach ($locations as $loc): ?>
                      <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['code']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td>
                  <div class="input-group">
                    <input type="number" step="1" min="1" name="items[0][qty_returned]" class="form-control item-qty" required placeholder="1">
                    <span class="unit-badge">-</span>
                  </div>
                </td>
                <td>
                  <select name="items[0][return_status]" class="form-select return-status-select" onchange="toggleRemnantSection(this, 0)">
                    <option value="sisa">Sisa Pekerjaan</option>
                    <option value="kelebihan">Kelebihan Bawa</option>
                    <option value="tidak_terpakai">Tidak Jadi Terpakai</option>
                    <option value="bekas">Material Bekas Pakai</option>
                    <option value="sisa_material">Sisa Material Potongan (Remnant)</option>
                    <option value="lainnya">Lainnya</option>
                  </select>
                </td>
                <td>
                  <select name="items[0][condition]" class="form-select">
                    <option value="good">Baik</option>
                    <option value="scrap">Scrap</option>
                    <option value="rework">Perlu Rework</option>
                    <option value="damaged">Rusak</option>
                  </select>
                </td>
                <td class="text-center">
                  <button type="button" class="btn btn-outline btn-sm btn-remnant-toggle" id="btn-remnant-0" style="display:none;" onclick="openRemnantRowModal(0)">
                    <i class="bi bi-scissors me-1"></i>Spesifikasi Sisa
                  </button>
                </td>
                <td class="text-center">
                  <button type="button" class="btn btn-danger btn-sm" onclick="removeReturnRow(this)"><i class="bi bi-trash"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Hidden Remnant Inputs for Row 0 -->
        <div id="remnant-inputs-0" style="display: none;">
          <input type="hidden" name="items[0][material_remnant][shape_condition]" value="Tidak Beraturan">
          <input type="hidden" name="items[0][material_remnant][dimension_description]" value="">
          <input type="hidden" name="items[0][material_remnant][estimated_area]" value="">
          <input type="hidden" name="items[0][material_remnant][estimated_weight]" value="">
        </div>
      </div>

      <!-- DOKUMENTASI PENGEMBALIAN -->
      <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
        <label for="return_photo"><i class="bi bi-camera me-1"></i>Foto Bukti Barang Pengembalian / Sisa Material (Sangat Dianjurkan)</label>
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

<!-- MODAL SPESIFIKASI SISA MATERIAL (SECTION 14) -->
<div class="modal-backdrop" id="modalRemnant">
  <div class="modal-dialog">
    <div class="modal-header">
      <span>Detail Spesifikasi Sisa Material Potongan</span>
      <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('modalRemnant')">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="active_remnant_row_index" value="0">
      
      <div class="form-group">
        <label>Kondisi Bentuk Fisik</label>
        <input type="text" id="remnant_shape" class="form-control" placeholder="Contoh: Tidak Beraturan, Segitiga, Potongan L-Shape, Sudut">
      </div>

      <div class="form-group">
        <label>Deskripsi Dimensi / Ukuran</label>
        <input type="text" id="remnant_dimension" class="form-control" placeholder="Contoh: 1200 x 800 mm, Panjang 1.5 meter">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Estimasi Luas (m²)</label>
          <input type="number" step="0.0001" id="remnant_area" class="form-control" placeholder="0.75">
        </div>
        <div class="form-group">
          <label>Estimasi Berat (kg)</label>
          <input type="number" step="0.0001" id="remnant_weight" class="form-control" placeholder="15.5">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="closeModal('modalRemnant')">Batal</button>
      <button type="button" class="btn btn-primary" onclick="applyRemnantData()">Terapkan Spesifikasi</button>
    </div>
  </div>
</div>

<script>
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
    select.innerHTML = '<option value="">-- Pilih Barang dari Dokumen OUT --</option>';
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
        <option value="">-- Pilih Barang dari Dokumen OUT --</option>
      </select>
    </td>
    <td>
      <select name="items[${returnRowCount}][warehouse_location_id]" class="form-select" required>
        <?php foreach ($locations as $loc): ?>
          <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['code']) ?></option>
        <?php endforeach; ?>
      </select>
    </td>
    <td>
      <div class="input-group">
        <input type="number" step="1" min="1" name="items[${returnRowCount}][qty_returned]" class="form-control item-qty" required placeholder="1">
        <span class="unit-badge">-</span>
      </div>
    </td>
    <td>
      <select name="items[${returnRowCount}][return_status]" class="form-select return-status-select" onchange="toggleRemnantSection(this, ${returnRowCount})">
        <option value="sisa">Sisa Pekerjaan (Sisa)</option>
        <option value="kelebihan">Kelebihan Bawa (Kelebihan)</option>
        <option value="tidak_terpakai">Tidak Jadi Terpakai</option>
        <option value="bekas">Material Bekas Pakai</option>
        <option value="sisa_material">Sisa Material Potongan (Remnant)</option>
        <option value="lainnya">Lainnya</option>
      </select>
    </td>
    <td>
      <select name="items[${returnRowCount}][condition]" class="form-select">
        <option value="good">Baik (Good)</option>
        <option value="scrap">Scrap (Afval)</option>
        <option value="rework">Perlu Rework</option>
        <option value="damaged">Rusak (Damaged)</option>
      </select>
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-outline btn-sm btn-remnant-toggle" id="btn-remnant-${returnRowCount}" style="display:none;" onclick="openRemnantRowModal(${returnRowCount})">
        <i class="bi bi-scissors me-1"></i>Spesifikasi Sisa
      </button>
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-danger btn-sm" onclick="removeReturnRow(this)"><i class="bi bi-trash"></i></button>
    </td>
  `;

  tbody.appendChild(tr);

  // Add hidden remnant inputs container
  const hiddenDiv = document.createElement('div');
  hiddenDiv.id = `remnant-inputs-${returnRowCount}`;
  hiddenDiv.style.display = 'none';
  hiddenDiv.innerHTML = `
    <input type="hidden" name="items[${returnRowCount}][material_remnant][shape_condition]" value="Tidak Beraturan">
    <input type="hidden" name="items[${returnRowCount}][material_remnant][dimension_description]" value="">
    <input type="hidden" name="items[${returnRowCount}][material_remnant][estimated_area]" value="">
    <input type="hidden" name="items[${returnRowCount}][material_remnant][estimated_weight]" value="">
  `;
  document.getElementById('form-return').appendChild(hiddenDiv);

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

function toggleRemnantSection(select, idx) {
  const btn = document.getElementById(`btn-remnant-${idx}`);
  if (select.value === 'sisa_material') {
    if (btn) btn.style.display = 'inline-block';
  } else {
    if (btn) btn.style.display = 'none';
  }
}

function openRemnantRowModal(idx) {
  document.getElementById('active_remnant_row_index').value = idx;
  const container = document.getElementById(`remnant-inputs-${idx}`);
  if (container) {
    document.getElementById('remnant_shape').value = container.querySelector('input[name*="shape_condition"]').value || 'Tidak Beraturan';
    document.getElementById('remnant_dimension').value = container.querySelector('input[name*="dimension_description"]').value || '';
    document.getElementById('remnant_area').value = container.querySelector('input[name*="estimated_area"]').value || '';
    document.getElementById('remnant_weight').value = container.querySelector('input[name*="estimated_weight"]').value || '';
  }
  openModal('modalRemnant');
}

function applyRemnantData() {
  const idx = document.getElementById('active_remnant_row_index').value;
  const container = document.getElementById(`remnant-inputs-${idx}`);
  if (container) {
    container.querySelector('input[name*="shape_condition"]').value = document.getElementById('remnant_shape').value;
    container.querySelector('input[name*="dimension_description"]').value = document.getElementById('remnant_dimension').value;
    container.querySelector('input[name*="estimated_area"]').value = document.getElementById('remnant_area').value;
    container.querySelector('input[name*="estimated_weight"]').value = document.getElementById('remnant_weight').value;
  }
  closeModal('modalRemnant');
  alert('Spesifikasi material sisa telah disimpan untuk baris ini.');
}

document.addEventListener('DOMContentLoaded', function() {
  const select = document.getElementById('stock_issue_id');
  if (select && select.value) {
    loadIssueDetails(select.value);
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
