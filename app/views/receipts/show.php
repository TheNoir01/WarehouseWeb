<?php
$pageTitle = 'Detail Penerimaan: ' . ($receipt['receipt_number'] ?? '');
include __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-between align-center mb-2" style="flex-wrap: wrap; gap: 0.75rem;">
  <div>
    <h1 style="font-size: 1.35rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">
      Dokumen Penerimaan: <?= htmlspecialchars($receipt['receipt_number'] ?? '') ?>
    </h1>
    <?= renderCompanyBadge($receipt['company']['code'] ?? '') ?>
    <span class="text-muted" style="margin-left: 0.5rem; font-size: 0.85rem;">
      Tanggal: <?= formatDate($receipt['received_date']) ?>
    </span>
<?php
  $isPurchasingLocked = !empty($receipt['is_purchasing_locked']) || (int)($receipt['purchasing_edit_count'] ?? 0) >= 3;
  $purchasingEditCount = (int)($receipt['purchasing_edit_count'] ?? 0);
?>
  </div>
  <div class="d-flex gap-1 align-center">
    <?php if ($isPurchasingLocked): ?>
      <?php if (isAdmin() || isMaintenance()): ?>
        <form method="POST" action="<?= url('receipts/unlock-purchasing') ?>" style="display: inline; margin: 0;" onsubmit="return confirm('Buka kunci akses edit No. PO & Harga untuk dokumen ini agar Purchasing dapat mengedit kembali?');">
          <input type="hidden" name="receipt_id" value="<?= $receipt['id'] ?>">
        </form>
      <?php endif; ?>

      <?php if (isPurchasing()): ?>
        <span class="badge badge-danger" style="padding: 0.5rem 0.8rem; font-size: 0.82rem; font-weight: 600;" title="Akses edit No. PO & harga telah terkunci. Hubungi Admin untuk membuka akses.">
          <i class="bi bi-lock-fill me-1"></i> PO & Harga Terkunci
        </span>
      <?php elseif (isMaintenance()): ?>
        <a href="<?= url('receipts/edit-purchasing') ?>&id=<?= $receipt['id'] ?>" class="btn btn-outline btn-sm" style="font-weight: 600;">
          <i class="bi bi-tag-fill me-1"></i> Edit PO & Harga (Maintenance)
        </a>
      <?php endif; ?>

    <?php else: ?>
      <?php if (canManagePurchasing()): ?>
        <a href="<?= url('receipts/edit-purchasing') ?>&id=<?= $receipt['id'] ?>" class="btn btn-primary btn-sm" style="background-color: #0284c7; border-color: #0284c7; font-weight: 600;">
          <i class="bi bi-tag-fill me-1"></i> Input / Edit PO & Harga
        </a>
      <?php endif; ?>
    <?php endif; ?>

    <button onclick="window.print()" class="btn btn-outline btn-sm">
      <i class="bi bi-printer"></i> Cetak Bukti
    </button>
    <a href="<?= url('receipts') ?>" class="btn btn-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
  </div>
</div>

<?php if ($isPurchasingLocked): ?>
  <div class="alert alert-danger d-flex align-center justify-between mb-2" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 8px; padding: 0.85rem 1.15rem;">
    <div style="font-size: 0.875rem;">
      <i class="bi bi-lock-fill me-2" style="font-size: 1.15rem; color: #dc2626;"></i>
      <strong>Akses PO & Harga Terkunci:</strong> Pengeditan No. PO & Harga untuk dokumen ini telah terkunci.
      <?php if (isPurchasing()): ?>
        <div style="margin-top: 3px; color: #b91c1c;">Silakan hubungi <strong>Admin</strong> untuk membuka akses kembali.</div>
      <?php endif; ?>
    </div>
    <?php if (isAdmin() || isMaintenance()): ?>
      <form method="POST" action="<?= url('receipts/unlock-purchasing') ?>" style="margin: 0;" onsubmit="return confirm('Buka kunci akses edit No. PO & Harga untuk dokumen ini?');">
        <input type="hidden" name="receipt_id" value="<?= $receipt['id'] ?>">
        <button type="submit" class="btn btn-sm" style="background: #dc2626; color: #fff; font-weight: 600; border: none; white-space: nowrap; padding: 0.45rem 0.85rem;">
          <i class="bi bi-unlock-fill me-1"></i> Buka Kunci Sekarang
        </button>
      </form>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header d-flex justify-between align-center">
    <span>Informasi Dokumen Penerimaan</span>
    <span class="badge badge-success"><?= strtoupper($receipt['status'] ?? 'COMPLETED') ?></span>
  </div>
  <div class="card-body">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; background: #f8fafc; padding: 1.15rem; border-radius: 8px; border: 1px solid var(--border);">
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">PT Pemilik Stok</div>
        <div class="fw-bold" style="color: #1e293b; margin-top: 2px;"><?= htmlspecialchars($receipt['company']['name'] ?? '-') ?></div>
      </div>

      <div>
        <div class="text-muted" style="font-size: 0.8rem;">Supplier</div>
        <div class="fw-bold" style="color: #1e293b; margin-top: 2px;"><?= htmlspecialchars($receipt['supplier']['name'] ?? $receipt['supplier_name'] ?? '-') ?></div>
      </div>

      <div>
        <div class="text-muted" style="font-size: 0.8rem;">No. Purchase Order (PO)</div>
        <div class="fw-bold" style="font-family: monospace; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-top: 2px;">
          <?php if (!empty($receipt['po_number'])): ?>
            <span style="color: #0284c7; font-size: 0.95rem; font-weight: 700; background: #f0f9ff; padding: 2px 8px; border-radius: 4px; border: 1px solid #bae6fd;">
              <?= htmlspecialchars($receipt['po_number']) ?>
            </span>
          <?php else: ?>
            <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.75rem; font-weight: 600; padding: 3px 8px;">
              <i class="bi bi-clock-history me-1"></i>Belum diinput Purchasing
            </span>
          <?php endif; ?>

          <?php if ($isPurchasingLocked): ?>
            <span class="badge badge-danger" style="font-size: 0.72rem; padding: 2px 6px;">
              <i class="bi bi-lock-fill me-1"></i>Terkunci
            </span>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!empty($receipt['delivery_order_number'])): ?>
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">No. Surat Jalan Vendor</div>
        <div class="fw-bold" style="font-family: monospace; color: #1e293b; margin-top: 2px;"><?= htmlspecialchars($receipt['delivery_order_number']) ?></div>
      </div>
      <?php endif; ?>

      <div>
        <div class="text-muted" style="font-size: 0.8rem;">Petugas Gudang Penerima</div>
        <div class="fw-bold" style="color: #1e293b; margin-top: 2px;"><?= htmlspecialchars($receipt['received_by']['name'] ?? '-') ?></div>
      </div>
    </div>

    <?php if (!empty($receipt['notes'])): ?>
      <div style="margin-bottom: 1.25rem;">
        <span class="text-muted" style="font-size: 0.85rem;">Catatan:</span>
        <p><?= nl2br(htmlspecialchars($receipt['notes'])) ?></p>
      </div>
    <?php endif; ?>

    <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.75rem; color: #0f172a;">Daftar Barang Masuk:</h3>
    <div class="table-responsive">
      <table class="table" style="vertical-align: middle;">
        <thead>
          <tr>
            <th style="width: 4%;">No</th>
            <th>ID Barang</th>
            <th>Nama Barang & Spesifikasi</th>
            <th class="text-right">Kuantitas</th>
            <th>Satuan</th>
            <th class="text-right" style="white-space: nowrap; min-width: 130px;">Harga Satuan</th>
            <th class="text-right" style="white-space: nowrap; min-width: 160px;">Total Harga</th>
            <th>Kondisi Fisik</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $grandTotal = 0;
          foreach ($receipt['items'] ?? [] as $idx => $itemRow): 
            $qty = (float) $itemRow['qty'];
            $unitPrice = (float) ($itemRow['unit_price'] ?? 0);
            $totalPrice = (float) ($itemRow['total_price'] ?? ($qty * $unitPrice));
            $grandTotal += $totalPrice;
          ?>
            <tr>
              <td><?= $idx + 1 ?></td>
              <td style="font-family: monospace; font-weight: 600; color: var(--primary);">
                <?= htmlspecialchars($itemRow['item']['item_code'] ?? '-') ?>
              </td>
              <td>
                <a href="<?= url('items/show') ?>&id=<?= $itemRow['item']['id'] ?? '' ?>" class="fw-bold" style="text-decoration: none; color: #0f172a;">
                  <?= htmlspecialchars($itemRow['item']['name'] ?? '-') ?>
                </a>
              </td>
              <td class="text-right fw-bold" style="font-size: 0.95rem; color: var(--success); white-space: nowrap;">
                +<?= formatQty($itemRow['qty']) ?>
              </td>
              <td><?= htmlspecialchars($itemRow['item']['unit']['code'] ?? '-') ?></td>
              <td class="text-right" style="font-family: monospace; font-weight: 600; white-space: nowrap;">
                <?= $unitPrice > 0 ? formatRupiah($unitPrice) : '<span class="text-muted" style="font-weight: 400; font-size: 0.85rem;">Rp 0</span>' ?>
              </td>
              <td class="text-right fw-bold" style="font-family: monospace; font-size: 0.95rem; color: #0284c7; white-space: nowrap;">
                <?= $totalPrice > 0 ? formatRupiah($totalPrice) : '<span class="text-muted" style="font-weight: 400; font-size: 0.85rem;">Rp 0</span>' ?>
              </td>
              <td>
                <span class="badge <?= ($itemRow['condition'] ?? 'good') === 'good' ? 'badge-success' : 'badge-danger' ?>">
                  <?= strtoupper($itemRow['condition'] ?? 'good') ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <?php if ($grandTotal > 0): ?>
          <tfoot>
            <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid var(--border);">
              <td colspan="6" class="text-right" style="font-size: 0.95rem; white-space: nowrap; padding: 0.75rem 1rem;">
                Total Nilai Pembelian:
              </td>
              <td class="text-right" style="font-size: 1.05rem; font-family: monospace; color: #0284c7; white-space: nowrap; padding: 0.75rem 1rem;">
                <?= formatRupiah($grandTotal) ?>
              </td>
              <td></td>
            </tr>
          </tfoot>
        <?php endif; ?>
      </table>
    </div>

    <!-- Lampiran Dokumentasi -->
    <?php if (!empty($receipt['attachments'])): ?>
      <div style="margin-top: 1.5rem;">
        <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.75rem; color: #0f172a;">Dokumentasi Barang:</h3>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
          <?php foreach ($receipt['attachments'] as $att): ?>
            <div style="border: 1px solid var(--border); padding: 0.5rem; border-radius: 6px; background: #fff;">
              <a href="<?= htmlspecialchars($att['url']) ?>" target="_blank">
                <img src="<?= htmlspecialchars($att['url']) ?>" style="max-width: 180px; max-height: 140px; object-fit: cover; border-radius: 4px;" alt="Foto">
              </a>
              <div style="font-size: 0.75rem; margin-top: 0.35rem;" class="text-muted">
                <?= htmlspecialchars($att['file_name']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Histori Perubahan No. PO & Harga (Purchasing) -->
<div class="card mt-3">
  <div class="card-header d-flex justify-between align-center">
    <span style="font-weight: 700; color: #0f172a;">
      <i class="bi bi-clock-history me-1 text-primary"></i> Riwayat Perubahan No. PO & Harga
    </span>
    <span class="badge" style="background: #e0f2fe; color: #0284c7; font-weight: 600; border: 1px solid #bae6fd;">
      <?= count($receipt['purchasing_logs'] ?? []) ?> Catatan Riwayat
    </span>
  </div>
  <div class="card-body" style="padding: <?= empty($receipt['purchasing_logs']) ? '1.5rem' : '0' ?>;">
    <?php if (empty($receipt['purchasing_logs'])): ?>
      <div class="text-center text-muted" style="font-size: 0.88rem;">
        <i class="bi bi-clock-history" style="font-size: 1.75rem; color: #cbd5e1; display: block; margin-bottom: 0.35rem;"></i>
        Belum ada riwayat perubahan No. PO atau harga pada dokumen penerimaan ini.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table" style="vertical-align: middle; margin-bottom: 0;">
          <thead>
            <tr style="background: #f8fafc;">
              <th style="width: 18%;">Waktu & Tanggal</th>
              <th style="width: 25%;">Perubahan No. PO</th>
              <th style="width: 37%;">Perubahan Harga Item</th>
              <th style="width: 20%;">Petugas Purchasing</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($receipt['purchasing_logs'] as $pLog): ?>
              <?php
                $pDate = !empty($pLog['created_at']) ? date('d/m/Y H:i', strtotime($pLog['created_at'])) : '-';
                $pUser = $pLog['user']['name'] ?? ($pLog['user']['username'] ?? 'Purchasing');
                $oldVals = $pLog['old_values'] ?? [];
                $newVals = $pLog['new_values'] ?? [];
                $oldPo = $oldVals['po_number'] ?? null;
                $newPo = $newVals['po_number'] ?? null;
                $oldItems = $oldVals['items'] ?? [];
                $newItems = $newVals['items'] ?? [];
              ?>
              <tr>
                <td>
                  <div style="font-weight: 600; font-size: 0.85rem; color: #334155;"><?= $pDate ?></div>
                </td>
                <td>
                  <?php if ($oldPo !== $newPo): ?>
                    <div style="font-size: 0.8rem; margin-bottom: 3px;">
                      <span class="text-muted" style="font-size: 0.72rem;">Lama:</span>
                      <?php if (!empty($oldPo)): ?>
                        <span style="font-family: monospace; text-decoration: line-through; color: #dc2626; font-weight: 600;"><?= htmlspecialchars($oldPo) ?></span>
                      <?php else: ?>
                        <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.7rem;">(Belum ada PO)</span>
                      <?php endif; ?>
                    </div>
                    <div>
                      <span class="text-muted" style="font-size: 0.72rem;">Baru:</span>
                      <?php if (!empty($newPo)): ?>
                        <span style="font-family: monospace; font-weight: 700; color: #0284c7; background: #f0f9ff; padding: 2px 6px; border-radius: 4px; border: 1px solid #bae6fd;">
                          <?= htmlspecialchars($newPo) ?>
                        </span>
                      <?php else: ?>
                        <span class="text-muted" style="font-style: italic;">(Dikosongkan)</span>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span style="font-family: monospace; font-size: 0.85rem; color: #475569;"><?= htmlspecialchars($newPo ?? '-') ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($newItems)): ?>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                      <?php foreach ($newItems as $iKey => $nItem): ?>
                        <?php 
                          $oItem = $oldItems[$iKey] ?? null;
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
                  <?php else: ?>
                    <span class="text-muted" style="font-size: 0.8rem;">(Tidak ada perubahan harga item)</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="font-weight: 600; color: #1e293b; font-size: 0.85rem;"><?= htmlspecialchars($pUser) ?></div>
                  <span class="badge" style="background: #e0e7ff; color: #4338ca; font-size: 0.68rem;">Purchasing</span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
