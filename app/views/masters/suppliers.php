<?php
$pageTitle = 'Master Supplier / Vendor';
include __DIR__ . '/../layout/header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
  <!-- Form Tambah Supplier -->
  <div class="card">
    <div class="card-header">
      <span><i class="bi bi-plus-circle me-1"></i>Tambah Supplier Baru</span>
    </div>
    <div class="card-body">
      <form action="<?= url('masters/suppliers-store') ?>" method="POST">
        <div class="form-group">
          <label for="sup_code">Kode Supplier <span class="text-danger">*</span></label>
          <input type="text" name="code" id="sup_code" class="form-control" required placeholder="Contoh: SUP-004">
        </div>
        <div class="form-group">
          <label for="sup_name">Nama Supplier <span class="text-danger">*</span></label>
          <input type="text" name="name" id="sup_name" class="form-control" required placeholder="Contoh: PT Krakatau Steel">
        </div>
        <div class="form-group">
          <label for="sup_phone">No. Telepon</label>
          <input type="text" name="phone" id="sup_phone" class="form-control" placeholder="021-xxxxxxx">
        </div>
        <div class="form-group">
          <label for="sup_email">Email</label>
          <input type="email" name="email" id="sup_email" class="form-control" placeholder="sales@supplier.com">
        </div>
        <div class="form-group">
          <label for="sup_address">Alamat</label>
          <textarea name="address" id="sup_address" class="form-control" rows="2"></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;"><i class="bi bi-check-circle me-1"></i>Simpan Supplier</button>
      </form>
    </div>
  </div>

  <!-- Daftar Supplier -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span><i class="bi bi-truck me-1"></i>Daftar Supplier / Vendor Terdaftar</span>
      <span class="badge badge-secondary"><?= count($suppliers) ?> Supplier</span>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 15%;">Kode</th>
            <th style="width: 25%;">Nama Perusahaan</th>
            <th style="width: 20%;">Kontak</th>
            <th style="width: 22%;">Alamat</th>
            <th style="width: 18%; text-align: center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($suppliers)): ?>
            <tr>
              <td colspan="5" class="text-center text-muted" style="padding: 2rem;">Belum ada supplier yang terdaftar.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($suppliers as $sup): ?>
              <tr>
                <td class="fw-bold" style="font-family: monospace; color: var(--primary);">
                  <?= htmlspecialchars($sup['code']) ?>
                </td>
                <td class="fw-bold"><?= htmlspecialchars($sup['name']) ?></td>
                <td>
                  <div><?= htmlspecialchars($sup['phone'] ?? '-') ?></div>
                  <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($sup['email'] ?? '') ?></div>
                </td>
                <td class="text-muted" style="font-size: 0.85rem;"><?= htmlspecialchars($sup['address'] ?? '-') ?></td>
                <td style="text-align: center;">
                  <div style="display: inline-flex; gap: 6px;">
                    <button type="button" class="btn btn-warning btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                            onclick='editSupplier(<?= json_encode($sup, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                      <i class="bi bi-pencil-square me-1"></i>Edit
                    </button>
                    <form action="<?= url('masters/suppliers-destroy') ?>" method="POST" style="display: inline;" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus supplier \'<?= htmlspecialchars(addslashes($sup['name'])) ?>\'?');">
                      <input type="hidden" name="id" value="<?= $sup['id'] ?>">
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

<!-- Modal Edit Supplier -->
<div class="modal-backdrop" id="modalEditSupplier">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 style="margin: 0; font-size: 1.1rem;"><i class="bi bi-pencil-square me-1"></i>Edit Data Supplier</h3>
      <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('modalEditSupplier')">&times;</button>
    </div>
    <form action="<?= url('masters/suppliers-update') ?>" method="POST">
      <input type="hidden" name="id" id="edit_sup_id">
      <div class="modal-body">
        <div class="form-group">
          <label for="edit_sup_code">Kode Supplier <span class="text-danger">*</span></label>
          <input type="text" name="code" id="edit_sup_code" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="edit_sup_name">Nama Perusahaan <span class="text-danger">*</span></label>
          <input type="text" name="name" id="edit_sup_name" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="edit_sup_phone">No. Telepon</label>
          <input type="text" name="phone" id="edit_sup_phone" class="form-control">
        </div>
        <div class="form-group">
          <label for="edit_sup_email">Email</label>
          <input type="email" name="email" id="edit_sup_email" class="form-control">
        </div>
        <div class="form-group">
          <label for="edit_sup_address">Alamat</label>
          <textarea name="address" id="edit_sup_address" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditSupplier')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script>
function editSupplier(sup) {
  document.getElementById('edit_sup_id').value = sup.id || '';
  document.getElementById('edit_sup_code').value = sup.code || '';
  document.getElementById('edit_sup_name').value = sup.name || '';
  document.getElementById('edit_sup_phone').value = sup.phone || '';
  document.getElementById('edit_sup_email').value = sup.email || '';
  document.getElementById('edit_sup_address').value = sup.address || '';
  openModal('modalEditSupplier');
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
