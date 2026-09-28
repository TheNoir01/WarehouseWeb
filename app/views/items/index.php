<?php
$pageTitle = 'Daftar Barang & Inventori';
include __DIR__ . '/../layout/header.php';
?>

<style>
@keyframes spinSearch { 100% { transform: translateY(-50%) rotate(360deg); } }
</style>

<div class="card">
  <div class="card-header">
    <div class="d-flex align-center gap-1">
      <span><i class="bi bi-boxes me-1"></i>Daftar Katalog Barang & Stok Fisik</span>
    </div>
  </div>

  <div class="card-body">
    <!-- Live Filter Bar (Real-time Search & Filter serta Pengaturan Jumlah Data Per Halaman) -->
    <form method="GET" action="index.php" class="filter-bar" id="itemsFilterForm">
      <input type="hidden" name="r" value="items">
      
      <div style="flex: 2; min-width: 240px; position: relative;">
        <input type="text" id="liveSearchInput" name="search" class="form-control" 
               placeholder="Cari nama barang, ID/kode, No. PO, spesifikasi..." 
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" autocomplete="off"
               style="padding-left: 2.25rem; padding-right: 2rem;">
        <i class="bi bi-search" id="searchIconSpinner" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; margin-right: 0;"></i>
        <button type="button" id="btnClearSearch" title="Hapus pencarian" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; display: <?= !empty($_GET['search']) ? 'block' : 'none' ?>; padding: 0.25rem;">
          <i class="bi bi-x-circle-fill" style="margin-right: 0;"></i>
        </button>
      </div>

      <div style="min-width: 140px;">
        <select name="company_id" id="filterCompany" class="form-select" onchange="this.form.submit()">
          <option value="">Semua PT</option>
          <?php foreach ($companies as $comp): ?>
            <option value="<?= $comp['id'] ?>" <?= ($_GET['company_id'] ?? '') == $comp['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($comp['code'] . ' - ' . $comp['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="min-width: 140px;">
        <select name="category_id" id="filterCategory" class="form-select" onchange="this.form.submit()">
          <option value="">Semua Kategori</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= ($_GET['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="min-width: 130px;">
        <select name="stock_status" id="filterStatus" class="form-select" onchange="this.form.submit()">
          <option value="">Semua Status</option>
          <option value="TERSEDIA" <?= ($_GET['stock_status'] ?? '') === 'TERSEDIA' ? 'selected' : '' ?>>TERSEDIA</option>
          <option value="MENIPIS" <?= ($_GET['stock_status'] ?? '') === 'MENIPIS' ? 'selected' : '' ?>>MENIPIS</option>
          <option value="HABIS" <?= ($_GET['stock_status'] ?? '') === 'HABIS' ? 'selected' : '' ?>>HABIS</option>
        </select>
      </div>

      <!-- Pengaturan Jumlah Halaman / Data Per Halaman -->
      <div style="min-width: 150px;">
        <select name="per_page" id="filterPerPage" class="form-select" onchange="this.form.submit()" title="Atur berapa banyak data yang ingin ditampilkan per halaman">
          <option value="10" <?= ($_GET['per_page'] ?? '25') == '10' ? 'selected' : '' ?>>10 / halaman</option>
          <option value="25" <?= ($_GET['per_page'] ?? '25') == '25' ? 'selected' : '' ?>>25 / halaman</option>
          <option value="50" <?= ($_GET['per_page'] ?? '25') == '50' ? 'selected' : '' ?>>50 / halaman</option>
          <option value="100" <?= ($_GET['per_page'] ?? '25') == '100' ? 'selected' : '' ?>>100 / halaman</option>
          <option value="all" <?= ($_GET['per_page'] ?? '25') === 'all' ? 'selected' : '' ?>>Tampilkan Semua</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
    </form>

    <div class="d-flex justify-between align-center mb-2" style="font-size: 0.85rem; color: #64748b;">
      <div>
        Menampilkan <strong id="visibleCount" style="color: #0f172a; font-weight: 700;"><?= number_format(!empty($meta['total']) ? $meta['total'] : count($items), 0, ',', '.') ?></strong> data barang
        <span id="topHeaderPageInfo" class="text-muted" style="display: <?= (!empty($meta) && ($meta['last_page'] ?? 1) > 1 && ($_GET['per_page'] ?? '25') !== 'all') ? 'inline' : 'none' ?>;">
          (Halaman <?= $meta['current_page'] ?? 1 ?> dari <?= $meta['last_page'] ?? 1 ?>)
        </span>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-sticky-header" id="itemsTable">
        <thead>
          <tr>
            <th>ID Barang</th>
            <th>Nama Barang</th>
            <th>Harga Beli</th>
            <th>Kategori</th>
            <th>Total Stok</th>
            <th>Status</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody id="itemsTableBody">
          <tr id="noMatchRow" style="display: none;">
            <td colspan="7" class="text-center text-muted" style="padding: 2.5rem;">
              <i class="bi bi-search" style="font-size: 1.5rem; display: block; margin: 0 auto 0.5rem; color: #94a3b8;"></i>
              Tidak ada barang yang cocok dengan kata kunci pencarian atau filter yang dipilih.
            </td>
          </tr>
          <?php if (empty($items)): ?>
            <tr id="emptyDbRow"><td colspan="7" class="text-center text-muted" style="padding: 2rem;">Tidak ada data barang ditemukan.</td></tr>
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
                  <span class="badge badge-primary me-1" style="font-size: 0.75rem;">
                    <?= htmlspecialchars($item['company']['code'] ?? 'N/A') ?>
                  </span>
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
                <td style="font-family: monospace;">
                  <?php if ((float)($item['purchase_price'] ?? 0) > 0): ?>
                    <span class="fw-bold" style="color: #0284c7; font-size: 0.88rem;">
                      <?= formatRupiah($item['purchase_price']) ?>
                    </span>
                  <?php else: ?>
                    <span class="text-muted" style="font-size: 0.8rem; font-style: italic;">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?= htmlspecialchars($item['category']['name'] ?? '-') ?>
                </td>
                <td class="fw-bold" style="font-size: 1rem;">
                  <?= formatQty($item['total_stock'], $item['unit']['code'] ?? '') ?>
                </td>
                <td><?= renderBadge($item['stock_status']) ?></td>
                <td class="text-right" style="white-space: nowrap;">
                  <?php
                    $itemJson = htmlspecialchars(json_encode([
                        'id' => $item['id'],
                        'item_code' => $item['item_code'],
                        'name' => $item['name'],
                        'company_code' => $item['company']['code'] ?? 'N/A',
                        'total_stock' => $item['total_stock'],
                        'unit' => $item['unit']['code'] ?? '',
                        'purchase_price' => $item['purchase_price'] ?? 0,
                        'category_id' => $item['category_id'] ?? ($item['category']['id'] ?? ''),
                        'unit_id' => $item['unit_id'] ?? ($item['unit']['id'] ?? ''),
                        'minimum_stock' => $item['minimum_stock'] ?? 0,
                        'specification' => $item['specification'] ?? '',
                        'description' => $item['description'] ?? '',
                    ]), ENT_QUOTES, 'UTF-8');
                  ?>
                  <?php if (isPurchasing()): ?>
                    <button type="button" class="btn btn-outline btn-sm btn-input-price" onclick="openPurchasingModal(<?= $itemJson ?>)" style="color: #0284c7; border-color: #0284c7; margin-right: 4px;" title="Input / Edit Harga">
                      <i class="bi bi-tag me-1"></i>Input Harga
                    </button>
                  <?php endif; ?>
                  <?php if (canEditItem()): ?>
                    <button type="button" class="btn btn-outline btn-sm btn-edit-item" onclick="openEditItemModal(<?= $itemJson ?>)" style="color: #475569; border-color: #cbd5e1; margin-right: 4px;" title="Edit Isi Barang">
                      <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                  <?php endif; ?>
                  <a href="<?= url('items/show') ?>&id=<?= $item['id'] ?>" class="btn btn-outline btn-sm">
                    <i class="bi bi-eye me-1"></i>Detail
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Bottom pagination & summary bar -->
    <?php
      $cur = (int) ($meta['current_page'] ?? 1);
      $last = (int) ($meta['last_page'] ?? 1);
      $total = (int) ($meta['total'] ?? count($items));
      $perPageVal = $_GET['per_page'] ?? '25';
      $perPageNum = (int) ($meta['per_page'] ?? ($perPageVal !== 'all' ? (int)$perPageVal : $total));
      $from = ($total > 0 && $perPageVal !== 'all') ? (($cur - 1) * $perPageNum + 1) : ($total > 0 ? 1 : 0);
      $to = ($perPageVal !== 'all') ? min($total, $cur * $perPageNum) : $total;
      $queryParams = $_GET;
    ?>
    <div class="d-flex justify-between align-center mt-3 pt-3" style="border-top: 1px solid var(--border); flex-wrap: wrap; gap: 1rem;">
      <div id="paginationSummary" class="text-muted" style="font-size: 0.85rem;">
        <?php if ($perPageVal === 'all'): ?>
          Menampilkan seluruh <strong><?= number_format($total, 0, ',', '.') ?></strong> data barang.
        <?php else: ?>
          Menampilkan baris <strong><?= number_format($from, 0, ',', '.') ?></strong> - <strong><?= number_format($to, 0, ',', '.') ?></strong> dari total <strong><?= number_format($total, 0, ',', '.') ?></strong> barang (Halaman <strong><?= $cur ?></strong> dari <strong><?= $last ?></strong>)
        <?php endif; ?>
      </div>

      <div class="d-flex align-center gap-2" style="flex-wrap: wrap;">
        <div id="paginationNav">
          <?php if ($last > 1 && $perPageVal !== 'all'): ?>
            <div class="pagination" style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
              <?php if ($cur > 1): ?>
                <button type="button" class="btn btn-outline btn-sm pagination-btn" data-page="1" style="padding: 4px 8px;" title="Halaman Pertama">&laquo;</button>
                <button type="button" class="btn btn-outline btn-sm pagination-btn" data-page="<?= $cur - 1 ?>" style="padding: 4px 8px;" title="Sebelumnya">&lsaquo;</button>
              <?php endif; ?>

              <?php
                $startPage = max(1, $cur - 2);
                $endPage = min($last, $cur + 2);
                if ($startPage > 1) {
                  echo '<button type="button" class="btn btn-outline btn-sm pagination-btn" data-page="1" style="padding: 4px 10px;">1</button>';
                  if ($startPage > 2) echo '<span class="text-muted" style="padding: 0 4px;">...</span>';
                }
                for ($p = $startPage; $p <= $endPage; $p++) {
                  $activeStyle = ($p == $cur) ? 'background-color: var(--primary); color: #fff; border-color: var(--primary); font-weight: bold;' : '';
                  echo '<button type="button" class="btn btn-outline btn-sm pagination-btn" data-page="' . $p . '" style="padding: 4px 10px; ' . $activeStyle . '">' . $p . '</button>';
                }
                if ($endPage < $last) {
                  if ($endPage < $last - 1) echo '<span class="text-muted" style="padding: 0 4px;">...</span>';
                  echo '<button type="button" class="btn btn-outline btn-sm pagination-btn" data-page="' . $last . '" style="padding: 4px 10px;">' . $last . '</button>';
                }
              ?>

              <?php if ($cur < $last): ?>
                <button type="button" class="btn btn-outline btn-sm pagination-btn" data-page="<?= $cur + 1 ?>" style="padding: 4px 8px;" title="Selanjutnya">&rsaquo;</button>
                <button type="button" class="btn btn-outline btn-sm pagination-btn" data-page="<?= $last ?>" style="padding: 4px 8px;" title="Halaman Terakhir">&raquo;</button>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
    </div>
  </div>
</div>

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
// REAL-TIME BACKEND LIVE SEARCH & PAGINATION
// ==========================================
let currentSearchAbortController = null;
let searchDebounceTimer = null;

function fetchItemsAjax(page = 1) {
  if (currentSearchAbortController) {
    currentSearchAbortController.abort();
  }
  currentSearchAbortController = new AbortController();

  const searchInput = document.getElementById('liveSearchInput');
  const searchVal = searchInput ? searchInput.value.trim() : '';
  const compVal = document.getElementById('filterCompany')?.value || '';
  const catVal = document.getElementById('filterCategory')?.value || '';
  const statusVal = document.getElementById('filterStatus')?.value || '';
  const perPageVal = document.getElementById('filterPerPage')?.value || '25';

  const clearBtn = document.getElementById('btnClearSearch');
  if (clearBtn) {
    clearBtn.style.display = searchVal.length > 0 ? 'inline-block' : 'none';
  }

  const indicator = document.getElementById('searchIndicator');
  if (indicator) {
    const hasFilter = searchVal.length > 0 || compVal !== '' || catVal !== '' || statusVal !== '';
    indicator.style.display = hasFilter ? 'inline-block' : 'none';
  }

  const spinner = document.getElementById('searchIconSpinner');
  if (spinner) {
    spinner.className = 'bi bi-arrow-repeat';
    spinner.style.animation = 'spinSearch 0.6s linear infinite';
  }

  const tableBody = document.getElementById('itemsTableBody');
  if (tableBody) tableBody.style.opacity = '0.5';

  const params = new URLSearchParams({
    r: 'items',
    ajax: '1',
    search: searchVal,
    company_id: compVal,
    category_id: catVal,
    stock_status: statusVal,
    per_page: perPageVal,
    page: page
  });

  // Update browser URL
  const pageUrlParams = new URLSearchParams(params);
  pageUrlParams.delete('ajax');
  window.history.replaceState(null, '', 'index.php?' + pageUrlParams.toString());

  fetch('index.php?' + params.toString(), {
    signal: currentSearchAbortController.signal
  })
  .then(res => res.json())
  .then(data => {
    if (tableBody) {
      tableBody.style.opacity = '1';
      tableBody.innerHTML = data.rows_html;
    }

    const visibleEl = document.getElementById('visibleCount');
    if (visibleEl) visibleEl.innerText = Number(data.total || 0).toLocaleString('id-ID');

    const topHeaderInfo = document.getElementById('topHeaderPageInfo');
    if (topHeaderInfo) {
      if (data.last_page > 1 && perPageVal !== 'all') {
        topHeaderInfo.style.display = 'inline';
        topHeaderInfo.innerText = `(Halaman ${data.current_page} dari ${data.last_page})`;
      } else {
        topHeaderInfo.style.display = 'none';
      }
    }

    const summaryEl = document.getElementById('paginationSummary');
    if (summaryEl) summaryEl.innerHTML = data.summary_html;

    const pagContainer = document.getElementById('paginationNav');
    if (pagContainer) pagContainer.innerHTML = data.pagination_html;
  })
  .catch(err => {
    if (err.name !== 'AbortError') {
      console.error('Search fetch error:', err);
      if (tableBody) tableBody.style.opacity = '1';
    }
  })
  .finally(() => {
    if (spinner) {
      spinner.className = 'bi bi-search';
      spinner.style.animation = 'none';
    }
  });
}

// Live search input listener with responsive 150ms debounce
const searchInput = document.getElementById('liveSearchInput');
if (searchInput) {
  searchInput.addEventListener('input', function() {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => fetchItemsAjax(1), 150);
  });
}

// Clear search button listener
const clearBtn = document.getElementById('btnClearSearch');
if (clearBtn) {
  clearBtn.addEventListener('click', function() {
    if (searchInput) {
      searchInput.value = '';
      searchInput.focus();
      fetchItemsAjax(1);
    }
  });
}

// Live filter on dropdown change
['filterCompany', 'filterCategory', 'filterStatus', 'filterPerPage'].forEach(id => {
  const el = document.getElementById(id);
  if (el) {
    el.removeAttribute('onchange');
    el.addEventListener('change', () => fetchItemsAjax(1));
  }
});

// Intercept form submission
const itemsFilterForm = document.getElementById('itemsFilterForm');
if (itemsFilterForm) {
  itemsFilterForm.addEventListener('submit', function(e) {
    e.preventDefault();
    fetchItemsAjax(1);
  });
}

// Delegate pagination clicks
document.addEventListener('click', function(e) {
  const btn = e.target.closest('#paginationNav .pagination-btn');
  if (btn && btn.dataset.page) {
    e.preventDefault();
    fetchItemsAjax(btn.dataset.page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
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

// Purchasing Modal Functions (Khusus Role Purchasing)
function openPurchasingModal(itemData) {
  document.getElementById('modalItemId').value = itemData.id || '';
  document.getElementById('modalItemName').innerText = itemData.name || '';
  document.getElementById('modalItemCode').innerText = itemData.item_code || '';
  document.getElementById('modalCompanyCode').innerText = itemData.company_code || '';
  document.getElementById('modalItemStock').innerText = 'Stok: ' + (itemData.total_stock || 0) + ' ' + (itemData.unit || '');
  document.getElementById('modalPurchasePrice').value = (itemData.purchase_price && itemData.purchase_price > 0) ? itemData.purchase_price : '';
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

function clearPurchasingModalFields() {
  if (confirm('Apakah Anda yakin ingin mengosongkan harga barang ini menjadi Rp 0?')) {
    document.getElementById('modalPurchasePrice').value = '0';
    document.getElementById('formPurchasingModal').submit();
  }
}

// Edit Item Modal Functions (Khusus Role Kepala Gudang, Karyawan, Admin)
function openEditItemModal(itemData) {
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
<!-- Modal Input / Edit Harga (Khusus Purchasing) -->
<div id="purchasingModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
  <div style="background: #ffffff; border-radius: 12px; max-width: 520px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden;">
    <div style="padding: 1.2rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
      <div style="font-weight: 700; font-size: 1.1rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
        <i class="bi bi-tag-fill" style="color: #0284c7;"></i>
        <span>Input / Edit Harga Barang (Purchasing)</span>
      </div>
      <button type="button" onclick="closePurchasingModal()" style="background: none; border: none; font-size: 1.5rem; line-height: 1; color: #94a3b8; cursor: pointer;">&times;</button>
    </div>

    <form method="POST" action="<?= url('items/update-purchasing') ?>" id="formPurchasingModal">
      <input type="hidden" name="item_id" id="modalItemId" value="">
      
      <div style="padding: 1.25rem 1.5rem;">
        <!-- Peringatan Hak Akses Purchasing & Kunci Data Master -->
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.825rem; color: #1e40af; display: flex; gap: 0.75rem; align-items: flex-start;">
          <i class="bi bi-shield-lock-fill" style="font-size: 1.2rem; color: #2563eb; flex-shrink: 0; margin-top: 1px;"></i>
          <div>
            <strong>Hak Akses Purchasing:</strong> Anda hanya dapat menginput atau mengubah <strong>Harga Satuan Beli</strong>. Setiap perubahan harga akan otomatis tercatat ke dalam sistem riwayat laporan.
          </div>
        </div>

        <!-- Profil Barang (Read-only) -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
          <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem; margin-bottom: 0.25rem;" id="modalItemName">-</div>
          <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <span class="badge" style="background: #e2e8f0; color: #1e293b; font-family: monospace;" id="modalItemCode">-</span>
            <span class="badge badge-primary" id="modalCompanyCode">-</span>
            <span class="badge badge-secondary" id="modalItemStock">-</span>
          </div>
        </div>

        <!-- Input Harga Satuan -->
        <div class="form-group mb-3">
          <label class="form-label fw-bold" for="modalPurchasePrice" style="color: #0369a1; font-size: 0.9rem;">
            <i class="bi bi-cash-stack me-1"></i> Harga Satuan / Beli (Rp) <span class="text-danger">*</span>
          </label>
          <div class="input-group" style="display: flex;">
            <span style="background: #f1f5f9; padding: 0.45rem 0.75rem; border: 1px solid var(--border); border-right: none; border-radius: 6px 0 0 6px; font-weight: 600; color: #64748b;">Rp</span>
            <input type="number" step="any" min="0" name="purchase_price" id="modalPurchasePrice" class="form-control" 
                   placeholder="0" required style="border-radius: 0 6px 6px 0; font-family: monospace; font-weight: 600; font-size: 1rem; border-color: #7dd3fc;" autocomplete="off">
          </div>
          <small class="text-muted">Harga per 1 satuan unit barang (dalam Rupiah).</small>
        </div>

        <!-- Catatan Perubahan Harga -->
        <div class="form-group mb-1">
          <label class="form-label fw-bold" for="modalPriceNotes" style="color: #475569; font-size: 0.85rem;">
            <i class="bi bi-journal-text me-1"></i> Catatan Perubahan Harga (Opsional)
          </label>
          <input type="text" name="notes" id="modalPriceNotes" class="form-control" 
                 placeholder="Contoh: Penyesuaian supplier, update harga per PO baru, dll." style="font-size: 0.85rem;">
          <small class="text-muted">Akan ditampilkan pada laporan riwayat perubahan harga barang.</small>
        </div>
      </div>

      <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <button type="button" class="btn btn-outline btn-sm" onclick="clearPurchasingModalFields()" title="Set harga jadi Rp 0" style="border-color: #fca5a5; color: #dc2626;">
          <i class="bi bi-trash3 me-1"></i> Set Rp 0
        </button>
        <div style="display: flex; gap: 0.5rem;">
          <button type="button" class="btn btn-secondary btn-sm" onclick="closePurchasingModal()">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm" style="background-color: #0284c7; border-color: #0284c7; font-weight: 600;">
            <i class="bi bi-check-circle me-1"></i> Simpan Harga
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if (canEditItem()): ?>
<!-- Modal Edit Isi Barang (Khusus Kepala Gudang, Karyawan, Admin di Menu Daftar Barang) -->
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

        <!-- Minimum Stok -->
        <div class="form-group mb-2">
          <label class="form-label fw-bold" for="editMinStock">Stok Minimum Peringatan</label>
          <input type="number" step="any" min="0" name="minimum_stock" id="editMinStock" class="form-control" placeholder="0">
          <small class="text-muted">Pemberitahuan status 'MENIPIS' saat stok berada di bawah batas ini.</small>
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
