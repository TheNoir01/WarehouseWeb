<?php
$pageTitle = 'Master Kategori Barang';
include __DIR__ . '/../layout/header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">

  <div>
    <div class="card">
      <div class="card-header">
        <span><i class="bi bi-plus-circle me-1"></i>Tambah Kategori Baru</span>
      </div>
      <div class="card-body">
        <form action="<?= url('masters/categories-store') ?>" method="POST">
          <div class="form-group">
            <label for="cat_name">Nama Kategori <span class="text-danger">*</span></label>
            <input type="text" name="name" id="cat_name" class="form-control" required placeholder="Contoh: Plat, Pipa, Fitting">
          </div>
          <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;"><i class="bi bi-check-circle me-1"></i>Simpan Kategori</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Daftar Kategori -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span><i class="bi bi-tags me-1"></i>Daftar Kategori Barang</span>
      <span class="badge badge-secondary"><?= count($categories) ?> Kategori</span>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 8%;">No</th>
            <th style="width: 50%;">Nama Kategori</th>
            <th style="width: 20%;">Jml Barang</th>
            <th style="width: 22%; text-align: center;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($categories)): ?>
            <tr>
              <td colspan="4" class="text-center text-muted" style="padding: 2rem;">Belum ada kategori yang terdaftar.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($categories as $index => $cat): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td class="fw-bold" style="color: var(--primary);">
                  <?= htmlspecialchars($cat['name']) ?>
                </td>
                <td>
                  <span class="badge badge-primary"><?= $cat['items_count'] ?? count($cat['items'] ?? []) ?> Item</span>
                </td>
                <td style="text-align: center;">
                  <div style="display: inline-flex; gap: 6px;">
                    <button type="button" class="btn btn-warning btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                            onclick="editCategory(<?= (int)$cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>')">
                      <i class="bi bi-pencil-square me-1"></i>Edit
                    </button>
                    <form action="<?= url('masters/categories-destroy') ?>" method="POST" style="display: inline;" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori \'<?= htmlspecialchars(addslashes($cat['name'])) ?>\'?');">
                      <input type="hidden" name="id" value="<?= $cat['id'] ?>">
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

<!-- Modal Edit Kategori -->
<div class="modal-backdrop" id="modalEditCategory">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 style="margin: 0; font-size: 1.1rem;"><i class="bi bi-pencil-square me-1"></i>Edit Kategori</h3>
      <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('modalEditCategory')">&times;</button>
    </div>
    <form action="<?= url('masters/categories-update') ?>" method="POST">
      <input type="hidden" name="id" id="edit_cat_id">
      <div class="modal-body">
        <div class="form-group">
          <label for="edit_cat_name">Nama Kategori <span class="text-danger">*</span></label>
          <input type="text" name="name" id="edit_cat_name" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditCategory')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script>
function editCategory(id, name) {
  document.getElementById('edit_cat_id').value = id;
  document.getElementById('edit_cat_name').value = name;
  openModal('modalEditCategory');
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
