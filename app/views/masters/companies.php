<?php
$pageTitle = 'Master Entitas PT Pemilik Stok';
include __DIR__ . '/../layout/header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
  <!-- Form Tambah PT -->
  <div class="card">
    <div class="card-header">
      <span><i class="bi bi-plus-circle me-1"></i>Tambah PT Pemilik Baru</span>
    </div>
    <div class="card-body">
      <form action="<?= url('masters/companies-store') ?>" method="POST">
        <div class="form-group">
          <label for="comp_code">Kode PT <span class="text-danger">*</span></label>
          <input type="text" name="code" id="comp_code" class="form-control" required placeholder="Contoh: PT-C" style="text-transform: uppercase;">
        </div>
        <div class="form-group">
          <label for="comp_name">Nama Lengkap Perusahaan <span class="text-danger">*</span></label>
          <input type="text" name="name" id="comp_name" class="form-control" required placeholder="Contoh: PT Cahaya Mandiri Steel">
        </div>
        <div class="form-group">
          <label for="comp_phone">No. Telepon</label>
          <input type="text" name="phone" id="comp_phone" class="form-control" placeholder="021-xxxxxxx">
        </div>
        <div class="form-group">
          <label for="comp_address">Alamat Kantor</label>
          <textarea name="address" id="comp_address" class="form-control" rows="2"></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;"><i class="bi bi-check-circle me-1"></i>Daftarkan PT</button>
      </form>
    </div>
  </div>

  <!-- Daftar PT -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span><i class="bi bi-buildings me-1"></i>Daftar PT Pemilik yang Berbagi Gudang Fisik</span>
      <span class="badge badge-secondary"><?= count($companies) ?> Perusahaan</span>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 15%;">Kode</th>
            <th style="width: 40%;">Nama Perusahaan</th>
            <th style="width: 20%;">Jumlah Barang</th>
            <th style="width: 25%; text-align: center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($companies)): ?>
            <tr>
              <td colspan="4" class="text-center text-muted" style="padding: 2rem;">Belum ada PT yang terdaftar.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($companies as $comp): ?>
              <tr>
                <td class="fw-bold" style="font-family: monospace;">
                  <span class="badge badge-primary"><?= htmlspecialchars($comp['code']) ?></span>
                </td>
                <td class="fw-bold"><?= htmlspecialchars($comp['name']) ?></td>
                <td><span class="badge badge-secondary"><?= $comp['items_count'] ?? 0 ?> Item</span></td>
                <td style="text-align: center;">
                  <div style="display: inline-flex; gap: 6px;">
                    <button type="button" class="btn btn-warning btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                            onclick='editCompany(<?= json_encode($comp, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                      <i class="bi bi-pencil-square me-1"></i>Edit
                    </button>
                    <form action="<?= url('masters/companies-destroy') ?>" method="POST" style="display: inline;" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus PT \'<?= htmlspecialchars(addslashes($comp['name'])) ?>\'?');">
                      <input type="hidden" name="id" value="<?= $comp['id'] ?>">
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

<!-- Modal Edit PT -->
<div class="modal-backdrop" id="modalEditCompany">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 style="margin: 0; font-size: 1.1rem;"><i class="bi bi-pencil-square me-1"></i>Edit Data Perusahaan (PT)</h3>
      <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('modalEditCompany')">&times;</button>
    </div>
    <form action="<?= url('masters/companies-update') ?>" method="POST">
      <input type="hidden" name="id" id="edit_comp_id">
      <div class="modal-body">
        <div class="form-group">
          <label for="edit_comp_code">Kode PT <span class="text-danger">*</span></label>
          <input type="text" name="code" id="edit_comp_code" class="form-control" required style="text-transform: uppercase;">
        </div>
        <div class="form-group">
          <label for="edit_comp_name">Nama Lengkap Perusahaan <span class="text-danger">*</span></label>
          <input type="text" name="name" id="edit_comp_name" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="edit_comp_phone">No. Telepon</label>
          <input type="text" name="phone" id="edit_comp_phone" class="form-control">
        </div>
        <div class="form-group">
          <label for="edit_comp_address">Alamat Kantor</label>
          <textarea name="address" id="edit_comp_address" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditCompany')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script>
function editCompany(comp) {
  document.getElementById('edit_comp_id').value = comp.id || '';
  document.getElementById('edit_comp_code').value = comp.code || '';
  document.getElementById('edit_comp_name').value = comp.name || '';
  document.getElementById('edit_comp_phone').value = comp.phone || '';
  document.getElementById('edit_comp_address').value = comp.address || '';
  openModal('modalEditCompany');
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
