<?php
$pageTitle = 'Pelacakan Sisa Material: ' . ($traceability['remnant_code'] ?? '');
include __DIR__ . '/../layout/header.php';
$rem = $remnantData['details'] ?? [];
$tr = $remnantData['traceability'] ?? [];
?>

<div class="d-flex justify-between align-center mb-2">
  <div>
    <h1 style="font-size: 1.35rem; font-weight: 700; color: #0f172a;">
      Pelacakan Material Sisa: <?= htmlspecialchars($tr['remnant_code'] ?? '') ?>
    </h1>
    <span class="badge badge-primary"><?= htmlspecialchars($rem['company']['code'] ?? '') ?></span>
    <span class="badge <?= ($rem['status'] ?? 'available') === 'available' ? 'badge-success' : 'badge-secondary' ?>">
      STATUS: <?= strtoupper($rem['status'] ?? 'available') ?>
    </span>
  </div>
  <div class="d-flex gap-1">
    <button onclick="window.print()" class="btn btn-outline btn-sm"><i class="bi bi-printer me-1"></i>Cetak Label Sisa</button>
    <a href="<?= url('remnants') ?>" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
  </div>
</div>

<!-- KARTU JAWABAN KETERLACAKAN (SECTION 15 MANDATE) -->
<div class="card" style="border-left: 5px solid var(--primary);">
  <div class="card-header" style="background: #f0fdf4;">
    <span style="color: #166534; font-weight: 700;">
      <i class="bi bi-search me-1"></i>Jawaban Keterlacakan Sistem (Material Traceability Ledger)
    </span>
  </div>
  <div class="card-body">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
      <div style="background: #f8fafc; padding: 1rem; border-radius: 6px; border: 1px solid #e2e8f0;">
        <span class="text-muted" style="font-size: 0.8rem; text-transform: uppercase;">1. Berasal dari barang apa?</span>
        <div class="fw-bold" style="font-size: 1.05rem; margin-top: 0.25rem;">
          <a href="<?= url('items/show') ?>&id=<?= $rem['parent_item']['id'] ?? '' ?>">
            <?= htmlspecialchars($tr['origin_item_name'] ?? '-') ?>
          </a>
        </div>
        <div style="font-family: monospace; color: #1e40af;"><?= htmlspecialchars($tr['origin_item_code'] ?? '') ?></div>
      </div>

      <div style="background: #f8fafc; padding: 1rem; border-radius: 6px; border: 1px solid #e2e8f0;">
        <span class="text-muted" style="font-size: 0.8rem; text-transform: uppercase;">2. Keluar pada transaksi apa?</span>
        <div class="fw-bold" style="font-size: 1.05rem; margin-top: 0.25rem; font-family: monospace;">
          <a href="<?= url('issues/show') ?>&id=<?= $rem['stock_issue']['id'] ?? '' ?>">
            <?= htmlspecialchars($tr['issue_number'] ?? 'N/A') ?>
          </a>
        </div>
        <div class="text-muted" style="font-size: 0.8rem;">Tanggal keluar: <?= formatDate($rem['stock_issue']['issued_date'] ?? null) ?></div>
      </div>

      <div style="background: #f8fafc; padding: 1rem; border-radius: 6px; border: 1px solid #e2e8f0;">
        <span class="text-muted" style="font-size: 0.8rem; text-transform: uppercase;">3. Untuk proyek apa?</span>
        <div class="fw-bold" style="font-size: 1.05rem; margin-top: 0.25rem; color: #0f172a;">
          <?= htmlspecialchars($tr['project_name'] ?? 'N/A') ?>
        </div>
      </div>

      <div style="background: #f8fafc; padding: 1rem; border-radius: 6px; border: 1px solid #e2e8f0;">
        <span class="text-muted" style="font-size: 0.8rem; text-transform: uppercase;">4. Siapa yang membawa?</span>
        <div class="fw-bold" style="font-size: 1.05rem; margin-top: 0.25rem;">
          <?= htmlspecialchars($tr['carried_by'] ?? 'N/A') ?>
        </div>
      </div>

      <div style="background: #f8fafc; padding: 1rem; border-radius: 6px; border: 1px solid #e2e8f0;">
        <span class="text-muted" style="font-size: 0.8rem; text-transform: uppercase;">5. Kapan kembali ke gudang?</span>
        <div class="fw-bold" style="font-size: 1.05rem; margin-top: 0.25rem;">
          <?= formatDate($tr['return_date'] ?? null) ?>
        </div>
      </div>

      <div style="background: #f8fafc; padding: 1rem; border-radius: 6px; border: 1px solid #e2e8f0;">
        <span class="text-muted" style="font-size: 0.8rem; text-transform: uppercase;">6. Disimpan di mana sekarang?</span>
        <div class="fw-bold" style="font-size: 1.05rem; margin-top: 0.25rem;">
          <span class="badge badge-primary" style="font-size: 0.95rem;">
            <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($tr['storage_location'] ?? 'N/A') ?>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
  <!-- Spesifikasi Fisik Sisa -->
  <div class="card">
    <div class="card-header">
      <span>Karakteristik Fisik Sisa Material</span>
    </div>
    <div class="card-body">
      <table class="table">
        <tr>
          <td class="text-muted" style="width: 40%;">Kondisi Bentuk</td>
          <td class="fw-bold"><?= htmlspecialchars($tr['shape'] ?? '-') ?></td>
        </tr>
        <tr>
          <td class="text-muted">Ukuran / Dimensi</td>
          <td class="fw-bold"><?= htmlspecialchars($tr['dimensions'] ?? '-') ?></td>
        </tr>
        <tr>
          <td class="text-muted">Estimasi Luas</td>
          <td><?= htmlspecialchars($tr['estimated_area'] ?? '-') ?></td>
        </tr>
        <tr>
          <td class="text-muted">Estimasi Berat</td>
          <td><?= htmlspecialchars($tr['estimated_weight'] ?? '-') ?></td>
        </tr>
        <tr>
          <td class="text-muted">Jumlah Sisa</td>
          <td class="fw-bold"><?= formatQty($rem['qty'] ?? 1, $rem['unit']['code'] ?? '') ?></td>
        </tr>
        <?php if (!empty($rem['notes'])): ?>
          <tr>
            <td class="text-muted">Catatan</td>
            <td><?= nl2br(htmlspecialchars($rem['notes'])) ?></td>
          </tr>
        <?php endif; ?>
      </table>

      <!-- Ubah Status Sisa -->
      <?php if (canManageMaster()): ?>
        <form action="<?= url('remnants/update') ?>&id=<?= $rem['id'] ?>" method="POST" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);">
          <div class="d-flex gap-1 align-center">
            <label style="margin-bottom:0; font-size: 0.85rem;">Ubah Status:</label>
            <select name="status" class="form-select" style="max-width: 160px;">
              <option value="available" <?= ($rem['status'] ?? '') === 'available' ? 'selected' : '' ?>>Tersedia</option>
              <option value="used" <?= ($rem['status'] ?? '') === 'used' ? 'selected' : '' ?>>Sudah Dipakai</option>
              <option value="scrapped" <?= ($rem['status'] ?? '') === 'scrapped' ? 'selected' : '' ?>>Afval / Scrap</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Perbarui Status</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- Foto Dokumentasi Sisa -->
  <div class="card">
    <div class="card-header">
      <span>Foto Dokumentasi Bentuk Fisik Sisa</span>
    </div>
    <div class="card-body">
      <?php if (!empty($rem['attachments'])): ?>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
          <?php foreach ($rem['attachments'] as $att): ?>
            <div style="border: 1px solid var(--border); padding: 0.5rem; border-radius: 6px; background: #fff;">
              <a href="<?= htmlspecialchars($att['url']) ?>" target="_blank">
                <img src="<?= htmlspecialchars($att['url']) ?>" style="max-width: 250px; max-height: 200px; object-fit: cover; border-radius: 4px;" alt="Foto">
              </a>
              <div style="font-size: 0.75rem; margin-top: 0.35rem;" class="text-muted">
                <?= htmlspecialchars($att['file_name']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-center text-muted" style="padding: 2rem;">
          Tidak ada foto sisa material terlampir.
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
