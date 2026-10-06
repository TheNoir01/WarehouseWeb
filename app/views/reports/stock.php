<?php
$pageTitle = 'Laporan Saldo Stok Inventori per PT';
include __DIR__ . '/../layout/header.php';
?>

<style>
@keyframes spinSearch { 100% { transform: translateY(-50%) rotate(360deg); } }
</style>

<div class="card">
  <div class="card-header d-flex justify-between align-center">
    <span><i class="bi bi-graph-up me-2"></i>Laporan Saldo Stok Fisik per PT</span>
    <a href="<?= url('reports/stock-export-excel') ?>" class="btn btn-outline btn-sm" style="font-weight: 600; color: #16a34a; border-color: #16a34a;" title="Ekspor seluruh data saldo stok ke 1 file Excel multi-sheet terpisah per PT & per Bulan">
      <i class="bi bi-file-earmark-excel me-1"></i>Ekspor Excel (.xlsx)
    </a>
  </div>

  <div class="card-body">
    <!-- Filter -->
    <form method="GET" action="index.php" class="filter-bar" id="stockFilterForm">
      <input type="hidden" name="r" value="reports/stock">

      <div style="flex: 2; min-width: 220px; position: relative;">
        <input type="text" id="stockLiveSearchInput" name="search" class="form-control" 
               placeholder="Cari nama barang, kode, No. PO..." 
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" autocomplete="off"
               style="padding-left: 2.25rem; padding-right: 2rem;">
        <i class="bi bi-search" id="stockSearchIconSpinner" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; margin-right: 0;"></i>
        <button type="button" id="btnClearStockSearch" title="Hapus pencarian" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; display: <?= !empty($_GET['search']) ? 'block' : 'none' ?>; padding: 0.25rem;">
          <i class="bi bi-x-circle-fill" style="margin-right: 0;"></i>
        </button>
      </div>

      <div style="min-width: 180px;">
        <select name="company_id" id="filterStockCompany" class="form-select" onchange="this.form.submit()">
          <option value="">Semua PT Pemilik</option>
          <?php foreach ($companies as $comp): ?>
            <option value="<?= $comp['id'] ?>" <?= ($_GET['company_id'] ?? '') == $comp['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($comp['code'] . ' - ' . $comp['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Pengaturan Jumlah Halaman / Data Per Halaman -->
      <div style="min-width: 150px;">
        <select name="per_page" id="filterStockPerPage" class="form-select" onchange="this.form.submit()" title="Atur berapa banyak data yang ingin ditampilkan per halaman">
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
        Menampilkan <strong id="stockVisibleCount" style="color: #0f172a; font-weight: 700;"><?= number_format(!empty($meta['total']) ? $meta['total'] : count($balances), 0, ',', '.') ?></strong> data barang
        <span id="stockTopHeaderPageInfo" class="text-muted" style="display: <?= (!empty($meta) && ($meta['last_page'] ?? 1) > 1 && ($_GET['per_page'] ?? '25') !== 'all') ? 'inline' : 'none' ?>;">
          (Halaman <?= $meta['current_page'] ?? 1 ?> dari <?= $meta['last_page'] ?? 1 ?>)
        </span>
      </div>
      <div id="stockSearchIndicator" style="display: <?= !empty($_GET['search']) ? 'block' : 'none' ?>; font-size: 0.8rem; color: var(--primary);">
        <i class="bi bi-funnel-fill me-1"></i> Filter aktif
      </div>
    </div>

    <div class="table-responsive">
      <table class="table" id="stockTable">
        <thead>
          <tr>
            <th>PT Pemilik</th>
            <th>ID Barang</th>
            <th>Nama Barang & Spesifikasi</th>
            <th>Kategori</th>
            <th>Satuan</th>
            <th>Total Stok</th>
            <th>Status</th>
            <th>Terakhir Dimutasi</th>
          </tr>
        </thead>
        <tbody id="stockTableBody">
          <?php if (empty($balances)): ?>
            <tr id="emptyDbRow"><td colspan="8" class="text-center text-muted" style="padding: 2.5rem;">
              <i class="bi bi-search" style="font-size: 1.5rem; display: block; margin: 0 auto 0.5rem; color: #94a3b8;"></i>
              Tidak ada data stok yang cocok dengan kata kunci pencarian atau filter yang dipilih.
            </td></tr>
          <?php else: ?>
            <?php foreach ($balances as $b): ?>
              <?php
                $item = $b['item'] ?? [];
                $stock = (float) ($b['qty'] ?? 0);
                $min = (float) ($item['category']['minimum_stock'] ?? $item['category_minimum_stock'] ?? $item['minimum_stock'] ?? 0);
                $stockStatus = $b['stock_status'] ?? ($stock <= 0 ? 'HABIS' : (($min > 0 && $stock <= $min) ? 'MENIPIS' : 'TERSEDIA'));
              ?>
              <tr>
                <td><?= renderCompanyBadge($b['company']['code'] ?? 'N/A') ?></td>
                <td>
                  <span style="font-family: monospace; font-weight: 600; color: #1e40af;">
                    <?= htmlspecialchars($item['item_code'] ?? '-') ?>
                  </span>
                </td>
                <td>
                  <a href="<?= url('items/show') ?>&id=<?= $item['id'] ?? '' ?>" class="fw-bold" style="color: #1e40af; text-decoration: none;">
                    <?= htmlspecialchars($item['name'] ?? '-') ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($item['category']['name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($item['unit']['code'] ?? '-') ?></td>
                <td class="fw-bold" style="font-size: 1rem;">
                  <?= formatQty($stock, $item['unit']['code'] ?? '') ?>
                </td>
                <td><?= renderBadge($stockStatus) ?></td>
                <td class="text-muted" style="font-size: 0.85rem;"><?= formatDateTime($b['last_movement_at'] ?? $b['updated_at'] ?? null) ?></td>
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
      $total = (int) ($meta['total'] ?? count($balances));
      $perPageVal = $_GET['per_page'] ?? '25';
      $perPageNum = (int) ($meta['per_page'] ?? ($perPageVal !== 'all' ? (int)$perPageVal : $total));
      $from = ($total > 0 && $perPageVal !== 'all') ? (($cur - 1) * $perPageNum + 1) : ($total > 0 ? 1 : 0);
      $to = ($perPageVal !== 'all') ? min($total, $cur * $perPageNum) : $total;
      $queryParams = $_GET;
    ?>
    <div class="d-flex justify-between align-center mt-3 pt-3" style="border-top: 1px solid var(--border); flex-wrap: wrap; gap: 1rem;">
      <div id="stockPaginationSummary" class="text-muted" style="font-size: 0.85rem;">
        <?php if ($perPageVal === 'all'): ?>
          Menampilkan seluruh <strong><?= number_format($total, 0, ',', '.') ?></strong> data stok barang.
        <?php else: ?>
          Menampilkan baris <strong><?= number_format($from, 0, ',', '.') ?></strong> - <strong><?= number_format($to, 0, ',', '.') ?></strong> dari total <strong><?= number_format($total, 0, ',', '.') ?></strong> data barang (Halaman <strong><?= $cur ?></strong> dari <strong><?= $last ?></strong>)
        <?php endif; ?>
      </div>

      <div class="d-flex align-center gap-2" style="flex-wrap: wrap;">
        <div id="stockPaginationNav">
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

        <button type="button" class="btn btn-outline btn-sm" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" title="Kembali ke bagian atas tabel">
          <i class="bi bi-arrow-up"></i> Ke Atas
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// ==========================================
// REAL-TIME BACKEND LIVE SEARCH & PAGINATION UNTUK STOK
// ==========================================
let currentStockAbortController = null;
let stockSearchDebounceTimer = null;

function fetchStockAjax(page = 1) {
  if (currentStockAbortController) {
    currentStockAbortController.abort();
  }
  currentStockAbortController = new AbortController();

  const searchInput = document.getElementById('stockLiveSearchInput');
  const searchVal = searchInput ? searchInput.value.trim() : '';
  const compVal = document.getElementById('filterStockCompany')?.value || '';
  const perPageVal = document.getElementById('filterStockPerPage')?.value || '25';

  const clearBtn = document.getElementById('btnClearStockSearch');
  if (clearBtn) {
    clearBtn.style.display = searchVal.length > 0 ? 'inline-block' : 'none';
  }

  const indicator = document.getElementById('stockSearchIndicator');
  if (indicator) {
    const hasFilter = searchVal.length > 0 || compVal !== '';
    indicator.style.display = hasFilter ? 'inline-block' : 'none';
  }

  const spinner = document.getElementById('stockSearchIconSpinner');
  if (spinner) {
    spinner.className = 'bi bi-arrow-repeat';
    spinner.style.animation = 'spinSearch 0.6s linear infinite';
  }

  const tableBody = document.getElementById('stockTableBody');
  if (tableBody) tableBody.style.opacity = '0.5';

  const params = new URLSearchParams({
    r: 'reports/stock',
    ajax: '1',
    search: searchVal,
    company_id: compVal,
    per_page: perPageVal,
    page: page
  });

  const pageUrlParams = new URLSearchParams(params);
  pageUrlParams.delete('ajax');
  window.history.replaceState(null, '', 'index.php?' + pageUrlParams.toString());

  fetch('index.php?' + params.toString(), {
    signal: currentStockAbortController.signal
  })
  .then(res => res.json())
  .then(data => {
    if (tableBody) {
      tableBody.style.opacity = '1';
      tableBody.innerHTML = data.rows_html;
    }

    const visibleEl = document.getElementById('stockVisibleCount');
    if (visibleEl) visibleEl.innerText = Number(data.total || 0).toLocaleString('id-ID');

    const topHeaderInfo = document.getElementById('stockTopHeaderPageInfo');
    if (topHeaderInfo) {
      if (data.last_page > 1 && perPageVal !== 'all') {
        topHeaderInfo.style.display = 'inline';
        topHeaderInfo.innerText = `(Halaman ${data.current_page} dari ${data.last_page})`;
      } else {
        topHeaderInfo.style.display = 'none';
      }
    }

    const summaryEl = document.getElementById('stockPaginationSummary');
    if (summaryEl) summaryEl.innerHTML = data.summary_html;

    const pagContainer = document.getElementById('stockPaginationNav');
    if (pagContainer) pagContainer.innerHTML = data.pagination_html;
  })
  .catch(err => {
    if (err.name !== 'AbortError') {
      console.error('Stock search fetch error:', err);
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
const stockSearchInput = document.getElementById('stockLiveSearchInput');
if (stockSearchInput) {
  stockSearchInput.addEventListener('input', function() {
    clearTimeout(stockSearchDebounceTimer);
    stockSearchDebounceTimer = setTimeout(() => fetchStockAjax(1), 150);
  });
}

// Clear search button listener
const clearStockBtn = document.getElementById('btnClearStockSearch');
if (clearStockBtn) {
  clearStockBtn.addEventListener('click', function() {
    if (stockSearchInput) {
      stockSearchInput.value = '';
      stockSearchInput.focus();
      fetchStockAjax(1);
    }
  });
}

// Live filter on dropdown change
['filterStockCompany', 'filterStockPerPage'].forEach(id => {
  const el = document.getElementById(id);
  if (el) {
    el.removeAttribute('onchange');
    el.addEventListener('change', () => fetchStockAjax(1));
  }
});

// Intercept form submission
const stockFilterForm = document.getElementById('stockFilterForm');
if (stockFilterForm) {
  stockFilterForm.addEventListener('submit', function(e) {
    e.preventDefault();
    fetchStockAjax(1);
  });
}

// Delegate pagination clicks
document.addEventListener('click', function(e) {
  const btn = e.target.closest('#stockPaginationNav .pagination-btn');
  if (btn && btn.dataset.page) {
    e.preventDefault();
    fetchStockAjax(btn.dataset.page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
