<?php
$pageTitle = 'Detail Pengeluaran: ' . ($issue['issue_number'] ?? '');
include __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-between align-center mb-2">
  <div>
    <h1 style="font-size: 1.35rem; font-weight: 700; color: #0f172a;">
      Dokumen Pengeluaran: <?= htmlspecialchars($issue['issue_number'] ?? '') ?>
    </h1>
    <?php if (!empty($issue['company']['code'])): ?>
      <span class="badge badge-primary"><?= htmlspecialchars($issue['company']['code']) ?></span>
    <?php else: ?>
      <span class="badge badge-success" style="background: #059669; color: #fff;">Gudang Bersama (FIFO Lintas PT)</span>
    <?php endif; ?>
    <span class="text-muted" style="margin-left: 0.5rem; font-size: 0.85rem;">
      Tanggal: <?= formatDate($issue['issued_date']) ?>
    </span>
  </div>
  <div class="d-flex gap-1">
    <?php if ($issue['status'] !== 'fully_returned' && $issue['status'] !== 'closed'): ?>
      <a href="<?= url('returns/create') ?>&issue_id=<?= $issue['id'] ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-arrow-repeat"></i> Input Pengembalian Barang
      </a>
    <?php endif; ?>
    <button onclick="window.print()" class="btn btn-outline btn-sm">
      <i class="bi bi-printer"></i> Cetak
    </button>
    <a href="<?= url('issues') ?>" class="btn btn-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <span>Informasi Dokumen Pengeluaran</span>
    <?= renderBadge($issue['status']) ?>
  </div>
  <div class="card-body">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; background: #f8fafc; padding: 1rem; border-radius: 8px;">
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">Status Kuota</div>
        <div class="fw-bold"><?= htmlspecialchars($issue['company']['name'] ?? 'Gudang Bersama (FIFO Otomatis 2 PT)') ?></div>
      </div>
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">Nama Pengambil</div>
        <div class="fw-bold" style="color: var(--primary);"><?= htmlspecialchars($issue['recipient_name']) ?></div>
      </div>
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">Divisi</div>
        <div class="fw-bold"><?= htmlspecialchars($issue['requester_name']) ?></div>
      </div>
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">Keperluan / Proyek</div>
        <div class="fw-bold"><?= htmlspecialchars($issue['project_name']) ?></div>
      </div>
      <div>
        <div class="text-muted" style="font-size: 0.8rem;">Petugas Gudang</div>
        <div class="fw-bold"><?= htmlspecialchars($issue['issued_by']['name'] ?? '-') ?></div>
      </div>
    </div>

    <!-- Section 11 & 13: Keterlacakan Qty Keluar, Dipakai, Kembali, Hilang -->
    <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.75rem;">
      Pelacakan Siklus Barang di Lapangan:
    </h3>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 5%;">No</th>
            <th>ID Barang</th>
            <th>Nama Barang & Spesifikasi</th>
            <th>Lokasi Asal</th>
            <th class="text-right">Qty Keluar</th>
            <th class="text-right" style="color: #475569;">Qty Dipakai</th>
            <th class="text-right" style="color: var(--success);">Qty Kembali</th>
            <th class="text-right" style="color: var(--warning);">Sisa di Lapangan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($issue['items'] ?? [] as $idx => $itemRow): ?>
            <tr>
              <td><?= $idx + 1 ?></td>
              <td style="font-family: monospace; font-weight: 600; color: var(--primary);">
                <?= htmlspecialchars($itemRow['item']['item_code'] ?? '-') ?>
              </td>
              <td>
                <a href="<?= url('items/show') ?>&id=<?= $itemRow['item']['id'] ?? '' ?>" class="fw-bold">
                  <?= htmlspecialchars($itemRow['item']['name'] ?? '-') ?>
                </a>
              </td>
              <td>
                <span class="badge badge-secondary">
                  <?= htmlspecialchars($itemRow['location']['code'] ?? '-') ?>
                </span>
              </td>
              <td class="text-right fw-bold" style="font-size: 1rem; color: var(--danger);">
                -<?= formatQty($itemRow['qty_issued']) ?> <?= htmlspecialchars($itemRow['item']['unit']['code'] ?? '') ?>
              </td>
              <td class="text-right fw-bold">
                <?= formatQty($itemRow['qty_used']) ?>
              </td>
              <td class="text-right fw-bold" style="color: var(--success);">
                <?= formatQty($itemRow['qty_returned']) ?>
              </td>
              <td class="text-right fw-bold" style="font-size: 1rem; color: var(--warning);">
                <?= formatQty($itemRow['remaining_qty']) ?>
              </td>
              <td>
                <?php if ((float)$itemRow['remaining_qty'] > 0): ?>
                  <a href="<?= url('returns/create') ?>&issue_id=<?= $issue['id'] ?>&issue_item_id=<?= $itemRow['id'] ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem;">
                    Kembalikan
                  </a>
                <?php else: ?>
                  <span class="badge badge-success">Selesai</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Rincian Alokasi Pemotongan Kuota PT (True FIFO by Date) -->
    <?php if (!empty($issue['allocations'])): ?>
      <div style="margin-top: 1.5rem; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 8px; padding: 1.25rem;">
        <div class="d-flex justify-between align-center mb-1">
          <h3 style="font-size: 0.95rem; font-weight: 700; color: #166534; margin: 0;">
            <i class="bi bi-diagram-3-fill me-1" style="color: #16a34a;"></i> Rincian Alokasi Pemotongan Kuota PT (True FIFO by Date)
          </h3>
          <span class="badge" style="background: #166534; color: #fff; font-size: 0.75rem;">
            <?= count($issue['allocations']) ?> Batch Terpotong
          </span>
        </div>
        <p class="text-muted" style="font-size: 0.82rem; margin-bottom: 0.85rem;">
          Stok dikeluarkan dari wadah fisik menggunakan pemotongan FIFO murni berdasarkan tanggal masuk barang terlama melintasi PT:
        </p>
        <div class="table-responsive">
          <table class="table" style="background: #fff; border-radius: 6px; overflow: hidden; border: 1px solid #dcfce7;">
            <thead>
              <tr style="background: #f8fafc;">
                <th style="width: 5%;">No</th>
                <th>No. Batch</th>
                <th>PT Pemilik Kuota</th>
                <th>Barang Terpotong</th>
                <th>Lokasi Rak</th>
                <th>Tgl Masuk Batch</th>
                <th class="text-right">Qty Terpotong</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($issue['allocations'] as $aIdx => $alloc): ?>
                <tr>
                  <td><?= $aIdx + 1 ?></td>
                  <td style="font-family: monospace; font-weight: 600; color: #1e40af;">
                    <?= htmlspecialchars($alloc['batch']['batch_number'] ?? '-') ?>
                  </td>
                  <td>
                    <span class="badge badge-primary"><?= htmlspecialchars($alloc['company']['code'] ?? '-') ?></span>
                    <span style="font-size: 0.85rem; margin-left: 4px;"><?= htmlspecialchars($alloc['company']['name'] ?? '') ?></span>
                  </td>
                  <td><?= htmlspecialchars($alloc['item']['name'] ?? '-') ?></td>
                  <td>
                    <span class="badge badge-secondary"><?= htmlspecialchars($alloc['location']['code'] ?? '-') ?></span>
                  </td>
                  <td class="text-muted" style="font-size: 0.85rem;"><?= formatDate($alloc['batch']['received_at'] ?? null) ?></td>
                  <td class="text-right fw-bold" style="color: #dc2626; font-size: 0.95rem;">
                    -<?= formatQty($alloc['qty_deducted']) ?> <?= htmlspecialchars($alloc['item']['unit']['code'] ?? '') ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <!-- Lampiran Dokumentasi Foto -->
    <?php if (!empty($issue['attachments'])): ?>
      <div style="margin-top: 1.5rem;">
        <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.75rem;">Dokumentasi Foto Serah Terima:</h3>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
          <?php foreach ($issue['attachments'] as $att): ?>
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

<?php include __DIR__ . '/../layout/footer.php'; ?>
