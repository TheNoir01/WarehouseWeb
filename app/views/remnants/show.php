<?php
$pageTitle = 'Pelacakan Sisa Material: ' . ($traceability['remnant_code'] ?? '');
include __DIR__ . '/../layout/header.php';
$rem = $remnantData['details'] ?? [];
$tr = $remnantData['traceability'] ?? [];
?>

<style>
.hover-underline:hover {
  text-decoration: underline !important;
}
@media print {
  .btn, .sidebar, header, nav, footer, form {
    display: none !important;
  }
  .card {
    border: 1px solid #000 !important;
    box-shadow: none !important;
  }
}
</style>

<div class="d-flex justify-between align-center mb-2 flex-wrap gap-1">
  <div>
    <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; margin-bottom: 2px;">
      Pelacakan Material Sisa
    </div>
    <div class="d-flex align-center gap-1 flex-wrap">
      <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; font-family: monospace;">
        <?= htmlspecialchars($tr['remnant_code'] ?? '') ?>
      </h1>
      <?= renderCompanyBadge($rem['company']['code'] ?? '') ?>
      <?php
        $status = strtolower($rem['status'] ?? 'available');
        $badgeClass = $status === 'available' ? 'badge-success' : ($status === 'used' ? 'badge-primary' : 'badge-danger');
        $statusLabel = $status === 'available' ? 'TERSEDIA' : ($status === 'used' ? 'SUDAH DIPAKAI' : 'AFVAL / SCRAP');
      ?>
      <span class="badge <?= $badgeClass ?>" style="font-weight: 700; padding: 4px 10px; font-size: 0.78rem;">
        <?= $statusLabel ?>
      </span>
    </div>
  </div>
  <div class="d-flex gap-1 align-center">
    <button onclick="window.print()" class="btn btn-outline btn-sm"><i class="bi bi-printer me-1"></i>Cetak Label Sisa</button>
    <a href="<?= url('remnants') ?>" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
  </div>
</div>

<!-- KARTU KETERLACAKAN SISTEM -->
<div class="card mb-2" style="border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
  <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.25rem;">
    <span style="color: #0f172a; font-weight: 700; font-size: 0.95rem;">
      <i class="bi bi-shield-check me-2 text-primary"></i>Keterlacakan Sistem
    </span>
  </div>
  <div class="card-body" style="padding: 1.25rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem;">
      <!-- Card 1: Barang Asal -->
      <div style="background: #ffffff; padding: 1.1rem; border-radius: 8px; border: 1.5px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.4px; margin-bottom: 0.4rem;">
            <i class="bi bi-box-seam me-1 text-primary"></i>Berasal Dari Barang
          </div>
          <div class="fw-bold" style="font-size: 1rem; line-height: 1.35; color: #0f172a;">
            <a href="<?= url('items/show') ?>&id=<?= $rem['parent_item']['id'] ?? '' ?>" style="color: #0f172a; text-decoration: none;" class="hover-underline">
              <?= htmlspecialchars($tr['origin_item_name'] ?? '-') ?>
            </a>
          </div>
        </div>
        <div style="margin-top: 0.5rem; font-family: monospace; font-size: 0.85rem; color: #2563eb; font-weight: 600;">
          <?= htmlspecialchars($tr['origin_item_code'] ?? '') ?>
        </div>
      </div>

      <!-- Card 2: Transaksi Pengeluaran -->
      <div style="background: #ffffff; padding: 1.1rem; border-radius: 8px; border: 1.5px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.4px; margin-bottom: 0.4rem;">
            <i class="bi bi-file-earmark-arrow-up me-1 text-primary"></i>Keluar Pada Transaksi
          </div>
          <div class="fw-bold" style="font-size: 1.05rem; font-family: monospace;">
            <a href="<?= url('issues/show') ?>&id=<?= $rem['stock_issue']['id'] ?? '' ?>" style="color: #2563eb; text-decoration: none;">
              <?= htmlspecialchars($tr['issue_number'] ?? 'N/A') ?>
            </a>
          </div>
        </div>
        <div class="text-muted" style="font-size: 0.78rem; margin-top: 0.5rem;">
          Tanggal keluar: <?= formatDate($rem['stock_issue']['issued_date'] ?? null) ?>
        </div>
      </div>

      <!-- Card 3: Proyek -->
      <div style="background: #ffffff; padding: 1.1rem; border-radius: 8px; border: 1.5px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.4px; margin-bottom: 0.4rem;">
            <i class="bi bi-building me-1 text-primary"></i>Untuk Proyek
          </div>
          <div class="fw-bold" style="font-size: 1rem; color: #0f172a; line-height: 1.35;">
            <?= htmlspecialchars($tr['project_name'] ?? 'N/A') ?>
          </div>
        </div>
        <div class="text-muted" style="font-size: 0.78rem; margin-top: 0.5rem;">
          Alokasi lapangan
        </div>
      </div>

      <!-- Card 4: Pembawa -->
      <div style="background: #ffffff; padding: 1.1rem; border-radius: 8px; border: 1.5px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.4px; margin-bottom: 0.4rem;">
            <i class="bi bi-person-badge me-1 text-primary"></i>Yang Membawa
          </div>
          <div class="fw-bold" style="font-size: 1rem; color: #0f172a;">
            <?= htmlspecialchars($tr['carried_by'] ?? 'N/A') ?>
          </div>
        </div>
        <div class="text-muted" style="font-size: 0.78rem; margin-top: 0.5rem;">
          Penerima material
        </div>
      </div>

      <!-- Card 5: Tanggal Kembali -->
      <div style="background: #ffffff; padding: 1.1rem; border-radius: 8px; border: 1.5px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.4px; margin-bottom: 0.4rem;">
            <i class="bi bi-calendar-check me-1 text-primary"></i>Kembali Ke Gudang
          </div>
          <div class="fw-bold" style="font-size: 1.05rem; color: #0f172a;">
            <?= formatDate($tr['return_date'] ?? null) ?>
          </div>
        </div>
        <div class="text-muted" style="font-size: 0.78rem; margin-top: 0.5rem;">
          Tanggal pengembalian
        </div>
      </div>
    </div>
  </div>
</div>

<div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 1.25rem;">
  <!-- Spesifikasi Fisik Sisa -->
  <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.25rem;">
      <span style="font-weight: 700; color: #0f172a;">
        <i class="bi bi-scissors me-2 text-primary"></i>Karakteristik Fisik Sisa Material
      </span>
    </div>
    <div class="card-body" style="padding: 1.25rem;">
      <table class="table" style="margin-bottom: 0;">
        <tbody>
          <tr>
            <td class="text-muted" style="width: 38%; padding: 0.65rem 0.5rem; border-bottom: 1px solid #f1f5f9;">Kondisi Bentuk</td>
            <td class="fw-bold" style="padding: 0.65rem 0.5rem; border-bottom: 1px solid #f1f5f9; color: #0f172a;">
              <?= htmlspecialchars($tr['shape'] ?? 'Sisa Potongan') ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted" style="padding: 0.65rem 0.5rem; border-bottom: 1px solid #f1f5f9;">Jumlah Sisa</td>
            <td class="fw-bold" style="padding: 0.65rem 0.5rem; border-bottom: 1px solid #f1f5f9; font-size: 1.05rem; color: #059669;">
              <?= formatQty($rem['qty'] ?? 1, $rem['unit']['code'] ?? '') ?>
            </td>
          </tr>
          <?php
            $desc = trim($tr['dimensions'] ?? '');
            $notes = trim($rem['notes'] ?? '');
          ?>
          <tr>
            <td class="text-muted" style="padding: 0.65rem 0.5rem; border-bottom: 1px solid #f1f5f9;">Keterangan / Ukuran Sisa</td>
            <td class="fw-bold" style="padding: 0.65rem 0.5rem; border-bottom: 1px solid #f1f5f9; color: #0f172a;">
              <?= htmlspecialchars($desc ?: ($notes ?: '-')) ?>
            </td>
          </tr>
          <?php if (!empty($notes) && $notes !== $desc): ?>
            <tr>
              <td class="text-muted" style="padding: 0.65rem 0.5rem; border-bottom: 1px solid #f1f5f9;">Catatan Tambahan</td>
              <td style="padding: 0.65rem 0.5rem; border-bottom: 1px solid #f1f5f9;">
                <?= nl2br(htmlspecialchars($notes)) ?>
              </td>
            </tr>
          <?php endif; ?>
          <?php if (!empty($rem['estimated_area']) && (float)$rem['estimated_area'] > 0): ?>
            <tr>
              <td class="text-muted" style="padding: 0.65rem 0.5rem; border-bottom: 1px solid #f1f5f9;">Estimasi Luas</td>
              <td style="padding: 0.65rem 0.5rem; border-bottom: 1px solid #f1f5f9;">
                <?= htmlspecialchars($rem['estimated_area']) ?> m²
              </td>
            </tr>
          <?php endif; ?>
          <?php if (!empty($rem['estimated_weight']) && (float)$rem['estimated_weight'] > 0): ?>
            <tr>
              <td class="text-muted" style="padding: 0.65rem 0.5rem; border-bottom: 1px solid #f1f5f9;">Estimasi Berat</td>
              <td style="padding: 0.65rem 0.5rem; border-bottom: 1px solid #f1f5f9;">
                <?= htmlspecialchars($rem['estimated_weight']) ?> kg
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Ubah Status Sisa -->
      <?php if (canManageMaster()): ?>
        <form action="<?= url('remnants/update') ?>&id=<?= $rem['id'] ?>" method="POST" style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
          <div class="d-flex gap-1 align-center flex-wrap">
            <label style="margin-bottom:0; font-size: 0.85rem; font-weight: 600; color: #475569;">Ubah Status:</label>
            <select name="status" class="form-select" style="max-width: 170px;">
              <option value="tersedia" <?= ($rem['status'] ?? '') === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
              <option value="terpakai" <?= ($rem['status'] ?? '') === 'terpakai' ? 'selected' : '' ?>>Sudah Dipakai</option>
              <option value="scrap" <?= ($rem['status'] ?? '') === 'scrap' ? 'selected' : '' ?>>Scrap</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Perbarui Status</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- Foto Dokumentasi Sisa -->
  <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.25rem;">
      <span style="font-weight: 700; color: #0f172a;">
        <i class="bi bi-camera me-2 text-primary"></i>Foto Dokumentasi Sisa Material
      </span>
    </div>
    <div class="card-body" style="padding: 1.25rem;">
      <?php if (!empty($rem['attachments'])): ?>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
          <?php foreach ($rem['attachments'] as $att): ?>
            <div style="border: 1px solid #e2e8f0; padding: 0.5rem; border-radius: 8px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
              <a href="<?= htmlspecialchars($att['url']) ?>" target="_blank">
                <img src="<?= htmlspecialchars($att['url']) ?>" style="max-width: 240px; max-height: 180px; object-fit: cover; border-radius: 6px; display: block;" alt="Foto">
              </a>
              <div style="font-size: 0.75rem; margin-top: 0.4rem; color: #64748b; font-weight: 500;">
                <i class="bi bi-paperclip me-1"></i><?= htmlspecialchars($att['file_name']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-center" style="padding: 2.5rem 1rem; color: #94a3b8;">
          <i class="bi bi-image" style="font-size: 2.75rem; display: block; margin-bottom: 0.75rem; color: #cbd5e1;"></i>
          <span style="font-size: 0.95rem; font-weight: 600; color: #64748b;">Tidak ada foto sisa material terlampir</span>
          <div style="font-size: 0.8rem; margin-top: 0.35rem; color: #94a3b8;">Foto fisik sisa dapat diunggah saat transaksi pengembalian barang.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
