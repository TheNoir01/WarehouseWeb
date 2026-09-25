<?php
$pageTitle = 'Master Satuan Ukuran Barang';
include __DIR__ . '/../layout/header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
  <!-- Form Tambah Satuan -->
  <div class="card">
    <div class="card-header">
      <span><i class="bi bi-plus-circle me-1"></i>Tambah Satuan Baru</span>
    </div>
    <div class="card-body">
      <form action="<?= url('masters/units-store') ?>" method="POST">
        <div class="form-group">
          <label for="unit_code">Kode Satuan <span class="text-danger">*</span></label>
          <input type="text" name="code" id="unit_code" class="form-control" required placeholder="Contoh: PCS, LBR, BTG, KG, MTR" style="text-transform: uppercase;">
        </div>
        <div class="form-group">
          <label for="unit_name">Nama Satuan Lengkap <span class="text-danger">*</span></label>
          <input type="text" name="name" id="unit_name" class="form-control" required placeholder="Contoh: Pieces, Lembar, Batang">
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;"><i class="bi bi-check-circle me-1"></i>Simpan Satuan</button>
      </form>
    </div>
  </div>

  <!-- Daftar Satuan -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span><i class="bi bi-rulers me-1"></i>Daftar Satuan Ukuran Terdaftar</span>
      <span class="badge badge-secondary"><?= count($units) ?> Satuan</span>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 8%;">No</th>
            <th style="width: 25%;">Kode Satuan</th>
            <th style="width: 42%;">Nama Satuan</th>
            <th style="width: 25%; text-align: center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($units)): ?>
            <tr>
              <td colspan="4" class="text-center text-muted" style="padding: 2rem;">Belum ada satuan yang terdaftar.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($units as $idx => $u): ?>
              <tr>
                <td><?= $idx + 1 ?></td>
                <td class="fw-bold" style="font-family: monospace; color: var(--primary);">
                  <span class="badge badge-primary"><?= htmlspecialchars($u['code']) ?></span>
                </td>
                <td class="fw-bold"><?= htmlspecialchars($u['name']) ?></td>
                <td style="text-align: center;">
                  <div style="display: inline-flex; gap: 6px;">
                    <button type="button" class="btn btn-warning btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                            onclick="editUnit(<?= (int)$u['id'] ?>, '<?= htmlspecialchars(addslashes($u['code'])) ?>', '<?= htmlspecialchars(addslashes($u['name'])) ?>')">
                      <i class="bi bi-pencil-square me-1"></i>Edit
                    </button>
                    <form action="<?= url('masters/units-destroy') ?>" method="POST" style="display: inline;" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus satuan \'<?= htmlspecialchars(addslashes($u['code'])) ?>\'?');">
                      <input type="hidden" name="id" value="<?= $u['id'] ?>">
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

<!-- Modal Edit Satuan -->
<div class="modal-backdrop" id="modalEditUnit">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 style="margin: 0; font-size: 1.1rem;"><i class="bi bi-pencil-square me-1"></i>Edit Satuan Ukuran</h3>
      <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('modalEditUnit')">&times;</button>
    </div>
    <form action="<?= url('masters/units-update') ?>" method="POST">
      <input type="hidden" name="id" id="edit_unit_id">
      <div class="modal-body">
        <div class="form-group">
          <label for="edit_unit_code">Kode Satuan <span class="text-danger">*</span></label>
          <input type="text" name="code" id="edit_unit_code" class="form-control" required style="text-transform: uppercase;">
        </div>
        <div class="form-group">
          <label for="edit_unit_name">Nama Satuan Lengkap <span class="text-danger">*</span></label>
          <input type="text" name="name" id="edit_unit_name" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditUnit')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script>
function editUnit(id, code, name) {
  document.getElementById('edit_unit_id').value = id;
  document.getElementById('edit_unit_code').value = code;
  document.getElementById('edit_unit_name').value = name;
  openModal('modalEditUnit');
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
