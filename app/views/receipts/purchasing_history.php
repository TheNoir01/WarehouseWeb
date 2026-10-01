<?php
$pageTitle = 'Histori Perubahan No. PO & Harga';
include __DIR__ . '/../layout/header.php';

$search = $_GET['search'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
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
  <div class="d-flex gap-1 align-center">
    <a href="<?= url('receipts') ?>" class="btn btn-secondary btn-sm">
      <i class="bi bi-box-arrow-in-down me-1"></i>Daftar Barang Masuk
    </a>
    <a href="<?= url('items') ?>" class="btn btn-secondary btn-sm">
      <i class="bi bi-boxes me-1"></i>Daftar Barang
    </a>
  </div>
</div>

<!-- Info Alert: FIFO Safety Confirmation -->
<div class="alert alert-info d-flex align-center gap-2 mb-2" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 8px; padding: 0.8rem 1.1rem;">
  <i class="bi bi-shield-check" style="font-size: 1.35rem; color: #16a34a; flex-shrink: 0;"></i>
  <div style="font-size: 0.84rem;">
    <strong>Integritas FIFO Terlindungi:</strong> Setiap perubahan nomor PO dan harga beli yang tercatat di bawah ini murni bersifat administratif dan harga akuntansi. Tanggal penerimaan fisik barang dan urutan batch FIFO gudang tetap terkunci 100%.
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

      <button type="submit" class="btn btn-primary btn-sm" style="background-color: #0284c7; border-color: #0284c7;">
        <i class="bi bi-filter me-1"></i>Filter
      </button>

      <?php if (!empty($search) || !empty($startDate) || !empty($endDate)): ?>
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
                $createdAt = !empty($log['created_at']) ? date('d/m/Y H:i', strtotime($log['created_at'])) . ' WIB' : '-';
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
                    <i class="bi bi-calendar3 me-1 text-muted"></i><?= $createdAt ?>
                  </div>
                  <?php if (!empty($log['ip_address'])): ?>
                    <div class="text-muted" style="font-size: 0.72rem; margin-top: 2px;">
                      IP: <?= htmlspecialchars($log['ip_address']) ?>
                    </div>
                  <?php endif; ?>
                </td>

                <!-- Referensi Dokumen / Barang -->
                <td>
                  <?php if ($entityType === 'goods_receipt'): ?>
                    <div>
                      <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 0.72rem; font-weight: 700;">
                        Barang Masuk
                      </span>
                      <?php if (!empty($entity['company_code'])): ?>
                        <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 0.7rem; font-weight: 700;">
                          <?= htmlspecialchars($entity['company_code']) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                    <div style="margin-top: 3px;">
                      <a href="<?= url('receipts/show') ?>&id=<?= $entity['id'] ?>" class="fw-bold" style="font-family: monospace; color: #1d4ed8; text-decoration: none; font-size: 0.92rem;">
                        <?= htmlspecialchars($entity['receipt_number'] ?? "ID #{$entity['id']}") ?>
                      </a>
                    </div>
                    <?php if (!empty($entity['supplier_name']) && $entity['supplier_name'] !== '-'): ?>
                      <div class="text-muted" style="font-size: 0.75rem;">
                        Vendor: <?= htmlspecialchars($entity['supplier_name']) ?>
                      </div>
                    <?php endif; ?>
                  <?php elseif ($entityType === 'item'): ?>
                    <div>
                      <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 0.72rem; font-weight: 700;">
                        Master Barang
                      </span>
                      <?php if (!empty($entity['company_code'])): ?>
                        <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 0.7rem; font-weight: 700;">
                          <?= htmlspecialchars($entity['company_code']) ?>
                        </span>
                      <?php endif; ?>
                    </div>
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
                          <i class="bi bi-file-earmark-check me-1"></i><?= htmlspecialchars($newPo) ?>
                        </span>
                      <?php else: ?>
                        <span class="text-muted" style="font-style: italic;">(Dikosongkan)</span>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <?php if (!empty($newPo)): ?>
                      <span style="font-family: monospace; font-weight: 600; color: #475569; font-size: 0.82rem;">
                        <i class="bi bi-receipt me-1 text-muted"></i><?= htmlspecialchars($newPo) ?>
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

    <!-- Pagination -->
    <?php if (!empty($meta) && ($meta['last_page'] ?? 1) > 1): ?>
      <?php
        $cur = (int) ($meta['current_page'] ?? 1);
        $last = (int) ($meta['last_page'] ?? 1);
        $total = (int) ($meta['total'] ?? count($historyLogs));
        $perPage = (int) ($meta['per_page'] ?? 20);
        $from = ($cur - 1) * $perPage + 1;
        $to = min($total, $cur * $perPage);
        $baseParams = $_GET;
      ?>
      <div class="d-flex justify-between align-center p-3" style="border-top: 1px solid var(--border); flex-wrap: wrap; gap: 1rem;">
        <div class="text-muted" style="font-size: 0.85rem;">
          Menampilkan <?= $from ?> - <?= $to ?> dari <?= $total ?> aktivitas
        </div>
        <div class="pagination d-flex gap-1">
          <?php if ($cur > 1): ?>
            <?php $baseParams['page'] = $cur - 1; ?>
            <a href="index.php?<?= http_build_query($baseParams) ?>" class="btn btn-outline btn-sm">
              <i class="bi bi-chevron-left"></i> Sebelumnya
            </a>
          <?php endif; ?>

          <?php for ($p = max(1, $cur - 2); $p <= min($last, $cur + 2); $p++): ?>
            <?php $baseParams['page'] = $p; ?>
            <a href="index.php?<?= http_build_query($baseParams) ?>" class="btn btn-sm <?= $p === $cur ? 'btn-primary' : 'btn-outline' ?>" style="<?= $p === $cur ? 'background-color: #0284c7; border-color: #0284c7;' : '' ?>">
              <?= $p ?>
            </a>
          <?php endfor; ?>

          <?php if ($cur < $last): ?>
            <?php $baseParams['page'] = $cur + 1; ?>
            <a href="index.php?<?= http_build_query($baseParams) ?>" class="btn btn-outline btn-sm">
              Berikutnya <i class="bi bi-chevron-right"></i>
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
