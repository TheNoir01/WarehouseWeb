<?php
$pageTitle = 'Histori Perubahan No. PO & Harga';
include __DIR__ . '/../layout/header.php';

$search = $_GET['search'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$perPage = (int)($_GET['per_page'] ?? ($meta['per_page'] ?? 20));
if (!in_array($perPage, [10, 20, 50, 100])) {
    $perPage = 20;
}
$historyLogs = is_array($historyLogs ?? null) ? $historyLogs : [];
$meta = is_array($meta ?? null) ? $meta : [];
?>

<div class="d-flex justify-between align-center mb-2" style="flex-wrap: wrap; gap: 0.75rem;">
  <div>
    <h1 style="font-size: 1.35rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">
      <i class="bi bi-clock-history text-primary me-2"></i>Histori Perubahan No. PO & Harga
    </h1>
    <div class="text-muted" style="font-size: 0.85rem;">
      Catatan audit lengkap setiap penambahan dan pembaruan nomor PO serta harga beli barang oleh Purchasing.
    </div>
  </div>
</div>

<!-- Filter Bar -->
<div class="card mb-2">
  <div class="card-body" style="padding: 1rem 1.25rem;">
    <form method="GET" action="index.php" class="d-flex align-center gap-2" style="flex-wrap: wrap;">
      <input type="hidden" name="r" value="receipts/purchasing-history">

      <div style="flex: 1; min-width: 250px;">
        <div class="input-group" style="display: flex;">
          <span style="background: #f8fafc; border: 1px solid var(--border); border-right: none; padding: 0.45rem 0.75rem; border-radius: 6px 0 0 6px; color: #64748b;">
            <i class="bi bi-search"></i>
          </span>
          <input type="text" name="search" class="form-control" placeholder="Cari No. PO, dokumen penerimaan, nama barang, atau nama petugas..." value="<?= htmlspecialchars($search) ?>" style="border-radius: 0 6px 6px 0; font-size: 0.875rem;">
        </div>
      </div>

      <div style="display: flex; align-items: center; gap: 0.5rem;">
        <label style="font-size: 0.8rem; color: #64748b; white-space: nowrap;">Tanggal Dari:</label>
        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($startDate) ?>" style="font-size: 0.85rem;">
      </div>

      <div style="display: flex; align-items: center; gap: 0.5rem;">
        <label style="font-size: 0.8rem; color: #64748b; white-space: nowrap;">Sampai:</label>
        <input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($endDate) ?>" style="font-size: 0.85rem;">
      </div>

      <div style="display: flex; align-items: center; gap: 0.5rem;">
        <label style="font-size: 0.8rem; color: #64748b; white-space: nowrap;">Tampilkan:</label>
        <select name="per_page" class="form-select form-control-sm" onchange="this.form.submit()" style="font-size: 0.85rem; padding: 0.35rem 0.65rem; border-radius: 6px; border: 1px solid var(--border); background-color: #fff; cursor: pointer;">
          <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10 / hal</option>
          <option value="20" <?= $perPage == 20 ? 'selected' : '' ?>>20 / hal</option>
          <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50 / hal</option>
          <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100 / hal</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary btn-sm" style="background-color: #0284c7; border-color: #0284c7;">
        <i class="bi bi-filter me-1"></i>Filter
      </button>

      <?php if (!empty($search) || !empty($startDate) || !empty($endDate) || (isset($_GET['per_page']) && $_GET['per_page'] != 20)): ?>
        <a href="<?= url('receipts/purchasing-history') ?>" class="btn btn-outline btn-sm" title="Reset Pencarian">
          <i class="bi bi-x-circle me-1"></i>Reset
        </a>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- History Table Card -->
<div class="card">
  <div class="card-header d-flex justify-between align-center">
    <div style="font-weight: 600; color: #1e293b;">
      <i class="bi bi-journal-text me-1 text-primary"></i>Daftar Riwayat Perubahan (<?= number_format((int)($meta['total'] ?? count($historyLogs))) ?> Aktivitas)
    </div>
  </div>
  <div class="card-body" style="padding: 0;">
    <div class="table-responsive">
      <table class="table table-sticky-header" style="vertical-align: middle; margin-bottom: 0;">
        <thead>
          <tr>
            <th style="width: 14%;">Waktu & Tanggal</th>
            <th style="width: 18%;">Dokumen / Barang</th>
            <th style="width: 22%;">Perubahan No. PO</th>
            <th style="width: 28%;">Perubahan Harga Beli</th>
            <th style="width: 12%;">Petugas</th>
            <th class="text-right" style="width: 6%; white-space: nowrap;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($historyLogs)): ?>
            <tr>
              <td colspan="6" class="text-center text-muted" style="padding: 3rem 1rem;">
                <i class="bi bi-clock-history" style="font-size: 2.25rem; display: block; margin-bottom: 0.6rem; color: #cbd5e1;"></i>
                <div style="font-size: 0.95rem; font-weight: 600; color: #475569;">Belum Ada Riwayat Perubahan</div>
                <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 0.25rem;">
                  Semua aktivitas input atau edit nomor PO dan harga oleh Purchasing akan tercatat otomatis di sini.
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($historyLogs as $log): ?>
              <?php
                $createdAt = !empty($log['created_at']) ? date('d/m/Y H:i', strtotime($log['created_at'])) : '-';
                $entity = $log['entity'] ?? [];
                $entityType = $entity['type'] ?? '';
                $oldVals = $log['old_values'] ?? [];
                $newVals = $log['new_values'] ?? [];
                $user = $log['user'] ?? [];
                $userName = $user['name'] ?? ($user['username'] ?? 'Sistem');

                $oldPo = $oldVals['po_number'] ?? null;
                $newPo = $newVals['po_number'] ?? null;
                $hasPoChange = ($oldPo !== $newPo);

                $oldItems = $oldVals['items'] ?? [];
                $newItems = $newVals['items'] ?? [];
              ?>
              <tr>
                <!-- Waktu -->
                <td style="white-space: nowrap;">
                  <div style="font-size: 0.85rem; font-weight: 600; color: #334155;">
                    <?= $createdAt ?>
                  </div>
                </td>

                <!-- Referensi Dokumen / Barang -->
                <td>
                  <?php if ($entityType === 'goods_receipt'): ?>
                    <div>
                      <a href="<?= url('receipts/show') ?>&id=<?= $entity['id'] ?>" class="fw-bold" style="font-family: monospace; color: #1d4ed8; text-decoration: none; font-size: 0.92rem;">
                        <?= htmlspecialchars($entity['receipt_number'] ?? "ID #{$entity['id']}") ?>
                      </a>
                    </div>
                  <?php elseif ($entityType === 'item'): ?>
                    <div style="margin-top: 3px;">
                      <a href="<?= url('items/show') ?>&id=<?= $entity['id'] ?>" class="fw-bold" style="color: #0f172a; text-decoration: none; font-size: 0.88rem;">
                        <?= htmlspecialchars($entity['name'] ?? '-') ?>
                      </a>
                    </div>
                    <div style="font-family: monospace; font-size: 0.76rem; color: #64748b;">
                      <?= htmlspecialchars($entity['item_code'] ?? '') ?>
                    </div>
                  <?php else: ?>
                    <span class="text-muted" style="font-size: 0.85rem;">-</span>
                  <?php endif; ?>
                </td>

                <!-- Perubahan Nomor PO -->
                <td>
                  <?php if ($hasPoChange): ?>
                    <div style="font-size: 0.82rem; margin-bottom: 3px;">
                      <span class="text-muted" style="font-size: 0.75rem;">Lama:</span>
                      <?php if (!empty($oldPo)): ?>
                        <span style="font-family: monospace; text-decoration: line-through; color: #dc2626; font-weight: 600;">
                          <?= htmlspecialchars($oldPo) ?>
                        </span>
                      <?php else: ?>
                        <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.7rem;">(Belum ada PO)</span>
                      <?php endif; ?>
                    </div>
                    <div style="font-size: 0.85rem;">
                      <span class="text-muted" style="font-size: 0.75rem;">Baru:</span>
                      <?php if (!empty($newPo)): ?>
                        <span style="font-family: monospace; font-weight: 700; color: #0284c7; background: #f0f9ff; padding: 2px 6px; border-radius: 4px; border: 1px solid #bae6fd;">
                          <?= htmlspecialchars($newPo) ?>
                        </span>
                      <?php else: ?>
                        <span class="text-muted" style="font-style: italic;">(Dikosongkan)</span>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <?php if (!empty($newPo)): ?>
                      <span style="font-family: monospace; font-weight: 600; color: #475569; font-size: 0.82rem;">
                        <?= htmlspecialchars($newPo) ?>
                      </span>
                    <?php else: ?>
                      <span class="text-muted" style="font-size: 0.78rem;">(Tidak ada perubahan PO)</span>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>

                <!-- Perubahan Harga Beli -->
                <td>
                  <?php if (!empty($newItems)): ?>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                      <?php foreach ($newItems as $iIdx => $nItem): ?>
                        <?php 
                          $oItem = $oldItems[$iIdx] ?? null;
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
                  <?php elseif (isset($newVals['purchase_price'])): ?>
                    <?php
                      $oldPrice = (float)($oldVals['purchase_price'] ?? 0);
                      $newPrice = (float)($newVals['purchase_price'] ?? 0);
                      $diff = $newPrice - $oldPrice;
                    ?>
                    <div style="font-size: 0.82rem;">
                      <span style="text-decoration: line-through; color: #94a3b8; font-family: monospace;">
                        <?= formatRupiah($oldPrice) ?>
                      </span>
                      <i class="bi bi-arrow-right text-muted mx-1"></i>
                      <span style="font-weight: 700; color: #0284c7; font-family: monospace;">
                        <?= formatRupiah($newPrice) ?>
                      </span>
                      <?php if ($diff != 0): ?>
                        <span class="badge <?= $diff > 0 ? 'badge-danger' : 'badge-success' ?>" style="font-size: 0.7rem; margin-left: 4px;">
                          <?= ($diff > 0 ? '+' : '-') . formatRupiah(abs($diff)) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span class="text-muted" style="font-size: 0.78rem;">(Tidak ada perubahan harga)</span>
                  <?php endif; ?>
                </td>

                <!-- Petugas -->
                <td>
                  <div style="font-weight: 600; color: #1e293b; font-size: 0.85rem;">
                    <?= htmlspecialchars($userName) ?>
                  </div>
                  <span class="badge" style="background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 0.68rem; font-weight: 600;">
                    Purchasing
                  </span>
                </td>

                <!-- Aksi -->
                <td class="text-right" style="white-space: nowrap;">
                  <?php if ($entityType === 'goods_receipt' && !empty($entity['id'])): ?>
                    <a href="<?= url('receipts/show') ?>&id=<?= $entity['id'] ?>" class="btn-action-view" title="Buka Detail Dokumen Penerimaan">
                      <i class="bi bi-eye me-1"></i>Detail
                    </a>
                  <?php elseif ($entityType === 'item' && !empty($entity['id'])): ?>
                    <a href="<?= url('items/show') ?>&id=<?= $entity['id'] ?>" class="btn-action-view" title="Buka Detail Barang">
                      <i class="bi bi-eye me-1"></i>Detail
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Card Footer: Pagination & Per Page Selector -->
  <?php
    $cur = (int) ($meta['current_page'] ?? 1);
    $last = (int) ($meta['last_page'] ?? 1);
    $total = (int) ($meta['total'] ?? count($historyLogs));
    $perPageNum = (int) ($meta['per_page'] ?? $perPage ?? 20);
    $from = $total > 0 ? (($cur - 1) * $perPageNum + 1) : 0;
    $to = min($total, $cur * $perPageNum);

    $baseParams = $_GET;
    $buildPageUrl = function($pageNum) use ($baseParams, $perPageNum) {
      $p = $baseParams;
      $p['r'] = 'receipts/purchasing-history';
      $p['page'] = $pageNum;
      $p['per_page'] = $perPageNum;
      return 'index.php?' . http_build_query($p);
    };
  ?>
  <div class="card-footer d-flex justify-between align-center" style="padding: 0.85rem 1.25rem; background: #ffffff; border-top: 1px solid var(--border); border-radius: 0 0 var(--radius) var(--radius); flex-wrap: wrap; gap: 1rem;">
    <!-- Info & Per Page -->
    <div class="d-flex align-center gap-3" style="flex-wrap: wrap;">
      <div class="text-muted" style="font-size: 0.85rem;">
        <?php if ($total > 0): ?>
          Menampilkan baris <strong style="color: #0f172a;"><?= number_format($from) ?> - <?= number_format($to) ?></strong> dari total <strong style="color: #0f172a;"><?= number_format($total) ?></strong> aktivitas
          <?php if ($last > 1): ?>
            (Halaman <strong style="color: #0f172a;"><?= $cur ?></strong> dari <strong style="color: #0f172a;"><?= $last ?></strong>)
          <?php endif; ?>
        <?php else: ?>
          Menampilkan 0 aktivitas
        <?php endif; ?>
      </div>
    </div>

    <!-- Pagination Controls -->
    <?php if ($last > 1): ?>
      <nav aria-label="Navigasi Halaman">
        <ul style="display: flex; gap: 5px; align-items: center; margin: 0; padding: 0; list-style: none;">
          <!-- Previous Button -->
          <?php if ($cur > 1): ?>
            <li>
              <a href="<?= $buildPageUrl($cur - 1) ?>" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 4px; padding: 0.35rem 0.75rem; font-size: 0.82rem; font-weight: 500; border-radius: 6px; border-color: #cbd5e1; color: #334155; text-decoration: none;" title="Halaman Sebelumnya">
                <i class="bi bi-chevron-left"></i> Sebelumnya
              </a>
            </li>
          <?php else: ?>
            <li>
              <span class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 4px; padding: 0.35rem 0.75rem; font-size: 0.82rem; font-weight: 500; border-radius: 6px; border-color: #e2e8f0; color: #94a3b8; opacity: 0.55; cursor: not-allowed; pointer-events: none;">
                <i class="bi bi-chevron-left"></i> Sebelumnya
              </span>
            </li>
          <?php endif; ?>

          <!-- Page Numbers -->
          <?php
            $startPage = max(1, $cur - 2);
            $endPage = min($last, $cur + 2);

            if ($startPage > 1) {
              echo '<li><a href="' . $buildPageUrl(1) . '" class="btn btn-outline btn-sm" style="min-width: 34px; height: 32px; padding: 0 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 500; border-radius: 6px; border-color: #cbd5e1; color: #334155; text-decoration: none;">1</a></li>';
              if ($startPage > 2) {
                echo '<li><span class="text-muted" style="padding: 0 4px; font-size: 0.82rem;">...</span></li>';
              }
            }

            for ($p = $startPage; $p <= $endPage; $p++):
              if ($p === $cur):
          ?>
                <li>
                  <span style="min-width: 34px; height: 32px; padding: 0 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 700; border-radius: 6px; background-color: #0284c7; border: 1px solid #0284c7; color: #ffffff; box-shadow: 0 1px 2px rgba(2, 132, 199, 0.25);">
                    <?= $p ?>
                  </span>
                </li>
          <?php else: ?>
                <li>
                  <a href="<?= $buildPageUrl($p) ?>" class="btn btn-outline btn-sm" style="min-width: 34px; height: 32px; padding: 0 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 500; border-radius: 6px; border-color: #cbd5e1; color: #334155; text-decoration: none;">
                    <?= $p ?>
                  </a>
                </li>
          <?php
              endif;
            endfor;

            if ($endPage < $last) {
              if ($endPage < $last - 1) {
                echo '<li><span class="text-muted" style="padding: 0 4px; font-size: 0.82rem;">...</span></li>';
              }
              echo '<li><a href="' . $buildPageUrl($last) . '" class="btn btn-outline btn-sm" style="min-width: 34px; height: 32px; padding: 0 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 500; border-radius: 6px; border-color: #cbd5e1; color: #334155; text-decoration: none;">' . $last . '</a></li>';
            }
          ?>

          <!-- Next Button -->
          <?php if ($cur < $last): ?>
            <li>
              <a href="<?= $buildPageUrl($cur + 1) ?>" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 4px; padding: 0.35rem 0.75rem; font-size: 0.82rem; font-weight: 500; border-radius: 6px; border-color: #cbd5e1; color: #334155; text-decoration: none;" title="Halaman Selanjutnya">
                Berikutnya <i class="bi bi-chevron-right"></i>
              </a>
            </li>
          <?php else: ?>
            <li>
              <span class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 4px; padding: 0.35rem 0.75rem; font-size: 0.82rem; font-weight: 500; border-radius: 6px; border-color: #e2e8f0; color: #94a3b8; opacity: 0.55; cursor: not-allowed; pointer-events: none;">
                Berikutnya <i class="bi bi-chevron-right"></i>
              </span>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</div>

<script>
function changePurchasingHistoryPerPage(val) {
  const url = new URL(window.location.href);
  url.searchParams.set('per_page', val);
  url.searchParams.set('page', '1');
  window.location.href = url.toString();
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
