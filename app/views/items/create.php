<?php
$pageTitle = 'Tambah Barang Baru';
$isFromReceipts = ($from === 'receipts');
$matchedCompany = null;
if (!empty($presetCompanyCode)) {
  foreach ($companies as $comp) {
    if (strtoupper($comp['code']) === $presetCompanyCode) {
      $matchedCompany = $comp;
      break;
    }
  }
}
$themeColor = ($presetCompanyCode === 'LNP') ? '#0d9488' : '#2563eb';

include __DIR__ . '/../layout/header.php';
?>

<div class="card" style="max-width: 800px; margin: 0 auto; <?= $isFromReceipts ? 'border-top: 4px solid ' . $themeColor . ';' : '' ?>">
  <div class="card-header d-flex justify-between align-center">
    <div class="d-flex align-center gap-1">
      <span><i class="bi bi-boxes me-1"></i> Form Pendaftaran Master Barang Baru</span>
      <?php if ($matchedCompany): ?>
        <span class="badge" style="background-color: <?= $themeColor ?>; color: #fff;">
          PT <?= htmlspecialchars($matchedCompany['code']) ?>
        </span>
      <?php endif; ?>
    </div>
    <?php if ($isFromReceipts): ?>
      <a href="<?= url('receipts/create') ?>&company=<?= htmlspecialchars($presetCompanyCode) ?>" class="btn btn-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Form Penerimaan
      </a>
    <?php else: ?>
      <a href="<?= url('items') ?>" class="btn btn-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
      </a>
    <?php endif; ?>
  </div>

  <div class="card-body">
    <!-- Real-time Duplicate Warning Box -->
    <div id="duplicate_warning_box" style="display: none;"></div>

    <form action="<?= url('items/store') ?>" method="POST">
      <?php if ($isFromReceipts && $matchedCompany): ?>
        <input type="hidden" name="return_to" value="receipts">
        <input type="hidden" name="company_code" value="<?= htmlspecialchars($presetCompanyCode) ?>">
        <input type="hidden" name="company_id" value="<?= $matchedCompany['id'] ?>">

        <div class="form-group">
          <label>PT Pemilik Barang</label>
          <div style="background: #f1f5f9; border: 1px solid #cbd5e1; border-left: 4px solid <?= $themeColor ?>; border-radius: 6px; padding: 0.6rem 0.85rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; justify-content: space-between;">
            <div>
              <span class="badge" style="background-color: <?= $themeColor ?>; color: #fff; margin-right: 0.4rem;"><?= htmlspecialchars($matchedCompany['code']) ?></span>
              <?= htmlspecialchars($matchedCompany['name']) ?>
            </div>
            <small class="text-muted" style="font-weight: normal; font-size: 0.8rem;"><i class="bi bi-lock-fill"></i> Terkunci Otomatis (Dari Form Penerimaan)</small>
          </div>
          <small class="text-muted">Barang baru ini otomatis didaftarkan untuk kepemilikan PT <?= htmlspecialchars($matchedCompany['code']) ?>.</small>
        </div>
      <?php else: ?>
        <div class="form-group">
          <label for="company_id">PT Pemilik Barang <span class="text-danger">*</span></label>
          <select name="company_id" id="company_id" class="form-select" required>
            <option value="">Pilih PT Pemilik</option>
            <?php foreach ($companies as $comp): ?>
              <option value="<?= $comp['id'] ?>" <?= ($matchedCompany && $matchedCompany['id'] == $comp['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($comp['code'] . ' - ' . $comp['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <small class="text-muted">Penting: Stok barang PT A dan PT B terisolasi dan tidak boleh digabung.</small>
        </div>
      <?php endif; ?>

      <div class="form-group">
        <label for="item_name_input">Nama Barang & Ukuran/Spesifikasi <span class="text-danger">*</span></label>
        <input type="text" id="item_name_input" name="name" class="form-control" required 
               value="<?= htmlspecialchars($presetName ?? '') ?>"
               placeholder="Contoh: Plat SS400 10mm 1500x3000, Baut M12x50, Pipa CS 2 Inch">
        <small class="text-muted">Ukuran dan spesifikasi boleh langsung ditulis pada nama barang.</small>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="category_id">Kategori</label>
          <select name="category_id" id="category_id" class="form-select">
            <option value="">-- Pilih Kategori yang Ada --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="mt-1">
            <input type="text" name="category_name" class="form-control" placeholder="Atau ketik kategori baru...">
          </div>
        </div>

        <div class="form-group">
          <label for="unit_id">Satuan Ukuran <span class="text-danger">*</span></label>
          <select name="unit_id" id="unit_id" class="form-select">
            <option value="">-- Pilih Satuan --</option>
            <?php foreach ($units as $unit): ?>
              <option value="<?= $unit['id'] ?>"><?= htmlspecialchars($unit['code'] . ' (' . $unit['name'] . ')') ?></option>
            <?php endforeach; ?>
          </select>
          <div class="mt-1">
            <input type="text" name="unit_code" class="form-control" placeholder="Atau ketik kode satuan baru (cth: SET)...">
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="minimum_stock">Batas Minimum Stok</label>
          <input type="number" step="any" min="0" id="minimum_stock" name="minimum_stock" class="form-control" value="0">
        </div>

        <div class="form-group">
          <label for="generate_qr">Identifikasi QR Code</label>
          <div style="margin-top: 0.5rem;">
            <label style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--primary);">
              <input type="checkbox" name="generate_qr" value="1" checked> 
              <i class="bi bi-qr-code"></i> Generate Token QR Sistem Otomatis
            </label>
          </div>
          <small class="text-muted">Sistem akan membuatkan QR Code unik yang langsung siap dicetak stiker dan di-scan oleh scanner 2D / HP.</small>
        </div>
      </div>

      <div class="form-group">
        <label for="specification">Spesifikasi Detail Tambahan (Opsional)</label>
        <textarea id="specification" name="specification" class="form-control" rows="2" placeholder="Standar material, toleransi, dsb..."></textarea>
      </div>

      <div class="form-group">
        <label for="description">Keterangan / Catatan Tambahan (Opsional)</label>
        <textarea id="description" name="description" class="form-control" rows="2"></textarea>
      </div>

      <div class="d-flex justify-between mt-2">
        <?php if ($isFromReceipts): ?>
          <a href="<?= url('receipts/create') ?>&company=<?= htmlspecialchars($presetCompanyCode) ?>" class="btn btn-secondary">
            <i class="bi bi-x-circle me-1"></i> Batal & Kembali ke Penerimaan
          </a>
          <button type="submit" class="btn btn-primary" style="background-color: <?= $themeColor ?>; border-color: <?= $themeColor ?>;">
            <i class="bi bi-check-circle me-1"></i> Simpan & Lanjutkan ke Form Barang Masuk
          </button>
        <?php else: ?>
          <a href="<?= url('items') ?>" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">Simpan Barang Baru</button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
