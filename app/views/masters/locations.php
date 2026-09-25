<?php
$pageTitle = 'Master Lokasi Rak & Gudang';
include __DIR__ . '/../layout/header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
  <!-- Form Tambah Lokasi Rak -->
  <div class="card">
    <div class="card-header">
      <span><i class="bi bi-plus-circle me-1"></i>Tambah Lokasi Rak Gudang</span>
    </div>
    <div class="card-body">
      <form action="<?= url('masters/locations-store') ?>" method="POST">
        <div class="form-group">
          <label for="loc_warehouse_id">Gudang Fisik <span class="text-danger">*</span></label>
          <select name="warehouse_id" id="loc_warehouse_id" class="form-select" required>
            <?php foreach ($warehouses as $wh): ?>
              <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['code'] . ' - ' . $wh['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="loc_code">Kode Lokasi Rak <span class="text-danger">*</span></label>
          <input type="text" name="code" id="loc_code" class="form-control" required placeholder="Contoh: A-01-04, B-03-01">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="loc_zone">Zona</label>
            <input type="text" name="zone" id="loc_zone" class="form-control" placeholder="Zona A">
          </div>
          <div class="form-group">
            <label for="loc_rack">Rak</label>
            <input type="text" name="rack" id="loc_rack" class="form-control" placeholder="Rak 01">
          </div>
          <div class="form-group">
            <label for="loc_shelf">Tingkat / Shelf</label>
            <input type="text" name="shelf" id="loc_shelf" class="form-control" placeholder="Tingkat 4">
          </div>
        </div>
        <div class="form-group">
          <label for="loc_desc">Keterangan Lokasi</label>
          <input type="text" name="description" id="loc_desc" class="form-control" placeholder="Area khusus pipa, dsb.">
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;"><i class="bi bi-check-circle me-1"></i>Simpan Lokasi Rak</button>
      </form>
    </div>
  </div>

  <!-- Daftar Lokasi Rak -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span><i class="bi bi-geo-alt me-1"></i>Daftar Lokasi Rak Penyimpanan Fisik</span>
      <span class="badge badge-secondary"><?= count($locations) ?> Lokasi</span>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 18%;">Kode Lokasi</th>
            <th style="width: 22%;">Gudang Fisik</th>
            <th style="width: 22%;">Zona / Rak / Tingkat</th>
            <th style="width: 20%;">Keterangan</th>
            <th style="width: 18%; text-align: center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($locations)): ?>
            <tr>
              <td colspan="5" class="text-center text-muted" style="padding: 2rem;">Belum ada lokasi rak yang terdaftar.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($locations as $loc): ?>
              <tr>
                <td class="fw-bold" style="font-family: monospace; color: var(--primary);">
                  <span class="badge badge-primary"><?= htmlspecialchars($loc['code']) ?></span>
                </td>
                <td><?= htmlspecialchars($loc['warehouse']['name'] ?? 'Gudang Pusat') ?></td>
                <td><?= htmlspecialchars(($loc['zone'] ?? '-') . ' / ' . ($loc['rack'] ?? '-') . ' / ' . ($loc['shelf'] ?? '-')) ?></td>
                <td class="text-muted" style="font-size: 0.85rem;"><?= htmlspecialchars($loc['description'] ?? '-') ?></td>
                <td style="text-align: center;">
                  <div style="display: inline-flex; gap: 6px;">
                    <button type="button" class="btn btn-warning btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                            onclick='editLocation(<?= json_encode($loc, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                      <i class="bi bi-pencil-square me-1"></i>Edit
                    </button>
                    <form action="<?= url('masters/locations-destroy') ?>" method="POST" style="display: inline;" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi rak \'<?= htmlspecialchars(addslashes($loc['code'])) ?>\'?');">
                      <input type="hidden" name="id" value="<?= $loc['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                        <i class="bi bi-trash3 me-1"></i>Hapus
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Edit Lokasi Rak -->
<div class="modal-backdrop" id="modalEditLocation">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 style="margin: 0; font-size: 1.1rem;"><i class="bi bi-pencil-square me-1"></i>Edit Lokasi Rak</h3>
      <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('modalEditLocation')">&times;</button>
    </div>
    <form action="<?= url('masters/locations-update') ?>" method="POST">
      <input type="hidden" name="id" id="edit_loc_id">
      <div class="modal-body">
        <div class="form-group">
          <label for="edit_loc_warehouse_id">Gudang Fisik <span class="text-danger">*</span></label>
          <select name="warehouse_id" id="edit_loc_warehouse_id" class="form-select" required>
            <?php foreach ($warehouses as $wh): ?>
              <option value="<?= $wh['id'] ?>"><?= htmlspecialchars($wh['code'] . ' - ' . $wh['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="edit_loc_code">Kode Lokasi Rak <span class="text-danger">*</span></label>
          <input type="text" name="code" id="edit_loc_code" class="form-control" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="edit_loc_zone">Zona</label>
            <input type="text" name="zone" id="edit_loc_zone" class="form-control">
          </div>
          <div class="form-group">
            <label for="edit_loc_rack">Rak</label>
            <input type="text" name="rack" id="edit_loc_rack" class="form-control">
          </div>
          <div class="form-group">
            <label for="edit_loc_shelf">Tingkat / Shelf</label>
            <input type="text" name="shelf" id="edit_loc_shelf" class="form-control">
          </div>
        </div>
        <div class="form-group">
          <label for="edit_loc_desc">Keterangan Lokasi</label>
          <input type="text" name="description" id="edit_loc_desc" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditLocation')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script>
function editLocation(loc) {
  document.getElementById('edit_loc_id').value = loc.id || '';
  document.getElementById('edit_loc_warehouse_id').value = loc.warehouse_id || '';
  document.getElementById('edit_loc_code').value = loc.code || '';
  document.getElementById('edit_loc_zone').value = loc.zone || '';
  document.getElementById('edit_loc_rack').value = loc.rack || '';
  document.getElementById('edit_loc_shelf').value = loc.shelf || '';
  document.getElementById('edit_loc_desc').value = loc.description || '';
  openModal('modalEditLocation');
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
