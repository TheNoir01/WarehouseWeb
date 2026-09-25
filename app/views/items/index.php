<?php
$pageTitle = 'Daftar Barang & Inventori';
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
  <div class="card-header">
    <div class="d-flex align-center gap-1">
      <span><i class="bi bi-boxes me-1"></i>Daftar Katalog Barang & Stok Fisik</span>
    </div>
  </div>

  <div class="card-body">
    <!-- Live Filter Bar (Real-time Search & Filter tanpa perlu klik tombol) -->
    <form method="GET" action="index.php" class="filter-bar" id="itemsFilterForm" onsubmit="return false;">
      <input type="hidden" name="r" value="items">
      
      <div style="flex: 2; min-width: 260px; position: relative;">
        <input type="text" id="liveSearchInput" name="search" class="form-control" 
               placeholder="Ketik untuk mencari langsung (nama barang, ID/kode, QR, spesifikasi)..." 
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" autocomplete="off"
               style="padding-left: 2.25rem; padding-right: 2rem;">
        <i class="bi bi-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; margin-right: 0;"></i>
        <button type="button" id="btnClearSearch" title="Hapus pencarian" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; display: none; padding: 0.25rem;">
          <i class="bi bi-x-circle-fill" style="margin-right: 0;"></i>
        </button>
      </div>

      <div style="min-width: 150px;">
        <select name="company_id" id="filterCompany" class="form-select">
          <option value="">Semua PT</option>
          <?php foreach ($companies as $comp): ?>
            <option value="<?= $comp['id'] ?>" <?= ($_GET['company_id'] ?? '') == $comp['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($comp['code'] . ' - ' . $comp['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="min-width: 140px;">
        <select name="category_id" id="filterCategory" class="form-select">
          <option value="">Semua Kategori</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= ($_GET['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="min-width: 130px;">
        <select name="stock_status" id="filterStatus" class="form-select">
          <option value="">Semua Status</option>
          <option value="TERSEDIA" <?= ($_GET['stock_status'] ?? '') === 'TERSEDIA' ? 'selected' : '' ?>>TERSEDIA</option>
          <option value="MENIPIS" <?= ($_GET['stock_status'] ?? '') === 'MENIPIS' ? 'selected' : '' ?>>MENIPIS</option>
          <option value="HABIS" <?= ($_GET['stock_status'] ?? '') === 'HABIS' ? 'selected' : '' ?>>HABIS</option>
        </select>
      </div>

      <button type="button" id="btnResetFilters" class="btn btn-outline btn-sm" title="Kembalikan semua filter ke awal">
        <i class="bi bi-arrow-counterclockwise"></i> Reset
      </button>
    </form>

    <div class="d-flex justify-between align-center mb-2" style="font-size: 0.85rem; color: #64748b;">
      <div>
        Menampilkan <strong id="visibleCount" style="color: #0f172a; font-weight: 700;"><?= number_format(count($items), 0, ',', '.') ?></strong> dari total <strong id="totalCount"><?= number_format(count($items), 0, ',', '.') ?></strong> barang
      </div>
      <div id="searchIndicator" style="display: none; font-size: 0.8rem; color: var(--primary);">
        <i class="bi bi-funnel-fill me-1"></i> Filter aktif
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-sticky-header" id="itemsTable">
        <thead>
          <tr>
            <th>PT Pemilik</th>
            <th>ID Barang</th>
            <th>Nama Barang & Spesifikasi</th>
            <th>Kategori</th>
            <th>Satuan</th>
            <th>Total Stok</th>
            <th>Status</th>
            <th>QR Code</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody id="itemsTableBody">
          <tr id="noMatchRow" style="display: none;">
            <td colspan="9" class="text-center text-muted" style="padding: 2.5rem;">
              <i class="bi bi-search" style="font-size: 1.5rem; display: block; margin: 0 auto 0.5rem; color: #94a3b8;"></i>
              Tidak ada barang yang cocok dengan kata kunci pencarian atau filter yang dipilih.
            </td>
          </tr>
          <?php if (empty($items)): ?>
            <tr id="emptyDbRow"><td colspan="9" class="text-center text-muted" style="padding: 2rem;">Tidak ada data barang ditemukan.</td></tr>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
              <?php
                $searchContent = strtolower(
                  ($item['item_code'] ?? '') . ' ' .
                  ($item['name'] ?? '') . ' ' .
                  ($item['specification'] ?? '') . ' ' .
                  ($item['category']['name'] ?? '') . ' ' .
                  ($item['company']['code'] ?? '') . ' ' .
                  ($item['qr_code'] ?? '') . ' ' .
                  ($item['unit']['code'] ?? '')
                );
              ?>
              <tr class="item-row"
                  data-search="<?= htmlspecialchars($searchContent) ?>"
                  data-company="<?= htmlspecialchars($item['company_id'] ?? '') ?>"
                  data-category="<?= htmlspecialchars($item['category_id'] ?? '') ?>"
                  data-status="<?= htmlspecialchars($item['stock_status'] ?? '') ?>">
                <td>
                  <span class="badge badge-primary">
                    <?= htmlspecialchars($item['company']['code'] ?? 'N/A') ?>
                  </span>
                </td>
                <td>
                  <span style="font-family: monospace; font-weight: 600; color: #1e40af;">
                    <?= htmlspecialchars($item['item_code']) ?>
                  </span>
                </td>
                <td>
                  <a href="<?= url('items/show') ?>&id=<?= $item['id'] ?>" class="fw-bold">
                    <?= htmlspecialchars($item['name']) ?>
                  </a>
                  <?php if (!empty($item['specification'])): ?>
                    <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($item['specification']) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <?= htmlspecialchars($item['category']['name'] ?? '-') ?>
                </td>
                <td><?= htmlspecialchars($item['unit']['code'] ?? '-') ?></td>
                <td class="fw-bold" style="font-size: 1rem;">
                  <?= formatQty($item['total_stock'], $item['unit']['code'] ?? '') ?>
                </td>
                <td><?= renderBadge($item['stock_status']) ?></td>
                <td>
                  <?php $qrVal = !empty($item['qr_code']) ? $item['qr_code'] : $item['item_code']; ?>
                  <button type="button" class="btn btn-outline btn-sm" style="padding: 0.25rem 0.55rem; font-size: 0.75rem;" onclick="showQrModal('<?= htmlspecialchars(addslashes($item['name'])) ?>', '<?= htmlspecialchars($item['item_code']) ?>', '<?= htmlspecialchars($qrVal) ?>', '<?= htmlspecialchars($item['company']['code'] ?? '') ?>')">
                    <i class="bi bi-qr-code"></i> QR Code
                  </button>
                </td>
                <td class="text-right">
                  <a href="<?= url('items/show') ?>&id=<?= $item['id'] ?>" class="btn btn-outline btn-sm">Detail</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Bottom summary bar -->
    <div class="d-flex justify-between align-center mt-3 pt-3" style="border-top: 1px solid var(--border);">
      <span class="text-muted" style="font-size: 0.85rem;">
        Menampilkan seluruh <strong id="bottomCount"><?= number_format(count($items), 0, ',', '.') ?></strong> barang tanpa batas halaman.
      </span>
      <button type="button" class="btn btn-outline btn-sm" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        <i class="bi bi-arrow-up"></i> Kembali ke Atas
      </button>
    </div>
  </div>
</div>

<!-- Floating Back to Top Button -->
<button type="button" id="btnBackToTop" class="btn-floating-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
  <i class="bi bi-arrow-up"></i> Ke Atas
</button>

<!-- Modal Quick QR Scan & Print -->
<div class="modal-backdrop" id="modalQrScan">
  <div class="modal-dialog" style="max-width: 380px;">
    <div class="modal-header">
      <span><i class="bi bi-qr-code"></i> QR Code Barang (Siap Scan)</span>
      <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('modalQrScan')">&times;</button>
    </div>
    <div class="modal-body text-center">
      <div id="modal-printable-qr" style="background: white; padding: 14px; border-radius: 8px; border: 2px solid #e2e8f0; margin-bottom: 1rem;">
        <div id="modal-qr-pt" style="font-size: 0.8rem; font-weight: 700; color: #475569;"></div>
        <div id="modal-qr-title" style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 4px 0 8px;"></div>
        <div id="modal-qr-container" style="display: flex; justify-content: center; margin: 10px auto;"></div>
        <div id="modal-qr-code" style="font-family: monospace; font-size: 1.05rem; font-weight: 800; color: #1e293b; letter-spacing: 0.05em; margin-top: 6px;"></div>
      </div>
      <p class="text-muted" style="font-size: 0.8rem; margin: 0;">Arahkan kamera smartphone atau scanner barcode 2D untuk identifikasi barang otomatis.</p>
    </div>
    <div class="modal-footer" style="justify-content: center; gap: 0.75rem;">
      <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
        <i class="bi bi-printer"></i> Cetak Stiker
      </button>
      <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalQrScan')">Tutup</button>
    </div>
  </div>
</div>

<script>
let modalQrInstance = null;
function showQrModal(name, code, qrValue, ptCode) {
  document.getElementById('modal-qr-pt').innerText = ptCode ? '[' + ptCode + ']' : '';
  document.getElementById('modal-qr-title').innerText = name + ' (' + code + ')';
  document.getElementById('modal-qr-code').innerText = qrValue;

  const container = document.getElementById('modal-qr-container');
  container.innerHTML = '';
  
  if (typeof QRCode !== 'undefined') {
    modalQrInstance = new QRCode(container, {
      text: qrValue,
      width: 150,
      height: 150,
      colorDark: "#0f172a",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.H
    });
  }

  openModal('modalQrScan');
}

// ==========================================
// REAL-TIME LIVE SEARCH & FILTER
// ==========================================
let searchDebounceTimer = null;

function performLiveFilter() {
  const searchInput = document.getElementById('liveSearchInput');
  const clearBtn = document.getElementById('btnClearSearch');
  const companySelect = document.getElementById('filterCompany');
  const categorySelect = document.getElementById('filterCategory');
  const statusSelect = document.getElementById('filterStatus');
  const indicator = document.getElementById('searchIndicator');

  const query = (searchInput?.value || '').toLowerCase().trim();
  const company = companySelect?.value || '';
  const category = categorySelect?.value || '';
  const status = statusSelect?.value || '';

  // Show or hide clear button
  if (clearBtn) {
    clearBtn.style.display = query.length > 0 ? 'inline-block' : 'none';
  }

  // Show filter active indicator
  const hasActiveFilter = query.length > 0 || company !== '' || category !== '' || status !== '';
  if (indicator) {
    indicator.style.display = hasActiveFilter ? 'inline-block' : 'none';
  }

  const rows = document.querySelectorAll('#itemsTable tbody tr.item-row');
  let visibleCount = 0;

  // Split query into multiple keywords so "plat 10" matches any row containing both
  const keywords = query.length > 0 ? query.split(/\s+/).filter(k => k.length > 0) : [];

  rows.forEach(row => {
    const rowSearch = row.dataset.search || '';
    const rowCompany = row.dataset.company || '';
    const rowCategory = row.dataset.category || '';
    const rowStatus = row.dataset.status || '';

    // Check company filter
    if (company && rowCompany !== company) {
      row.style.display = 'none';
      return;
    }

    // Check category filter
    if (category && rowCategory !== category) {
      row.style.display = 'none';
      return;
    }

    // Check stock status filter
    if (status && rowStatus !== status) {
      row.style.display = 'none';
      return;
    }

    // Check keywords (all keywords must be contained in pre-computed rowSearch)
    let matches = true;
    for (let i = 0; i < keywords.length; i++) {
      if (!rowSearch.includes(keywords[i])) {
        matches = false;
        break;
      }
    }

    if (matches) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });

  // Show or hide empty match row
  const noMatchRow = document.getElementById('noMatchRow');
  if (noMatchRow) {
    noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
  }

  // Update counter badges
  const visibleEl = document.getElementById('visibleCount');
  if (visibleEl) visibleEl.innerText = visibleCount.toLocaleString('id-ID');
  const bottomEl = document.getElementById('bottomCount');
  if (bottomEl) bottomEl.innerText = visibleCount.toLocaleString('id-ID');
}

// Live search input listener with responsive debounce
const searchInput = document.getElementById('liveSearchInput');
if (searchInput) {
  searchInput.addEventListener('input', function() {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(performLiveFilter, 100);
  });
}

// Clear search button listener
const clearBtn = document.getElementById('btnClearSearch');
if (clearBtn) {
  clearBtn.addEventListener('click', function() {
    if (searchInput) {
      searchInput.value = '';
      searchInput.focus();
      performLiveFilter();
    }
  });
}

// Live filter on dropdown change
['filterCompany', 'filterCategory', 'filterStatus'].forEach(id => {
  const el = document.getElementById(id);
  if (el) {
    el.addEventListener('change', performLiveFilter);
  }
});

// Reset Button listener
const resetBtn = document.getElementById('btnResetFilters');
if (resetBtn) {
  resetBtn.addEventListener('click', function() {
    if (searchInput) searchInput.value = '';
    const comp = document.getElementById('filterCompany');
    if (comp) comp.value = '';
    const cat = document.getElementById('filterCategory');
    if (cat) cat.value = '';
    const stat = document.getElementById('filterStatus');
    if (stat) stat.value = '';
    performLiveFilter();
  });
}

// Run initial filter if values are present from initial load
document.addEventListener('DOMContentLoaded', function() {
  const q = searchInput ? searchInput.value.trim() : '';
  const comp = document.getElementById('filterCompany')?.value || '';
  const cat = document.getElementById('filterCategory')?.value || '';
  const stat = document.getElementById('filterStatus')?.value || '';
  if (q || comp || cat || stat) {
    performLiveFilter();
  }
});

// Floating Back to Top Button scroll handler
window.addEventListener('scroll', () => {
  const btn = document.getElementById('btnBackToTop');
  if (btn) {
    if (window.scrollY > 300) {
      btn.style.display = 'inline-flex';
    } else {
      btn.style.display = 'none';
    }
  }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
