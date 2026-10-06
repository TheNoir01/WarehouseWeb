<?php
$pageTitle = 'Manajemen Pengguna & Hak Akses';
include __DIR__ . '/../layout/header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
  <!-- Form Tambah Pengguna -->
  <div class="card">
    <div class="card-header">
      <span>+ Tambah Pengguna Baru</span>
    </div>
    <div class="card-body">
      <form action="<?= url('users/store') ?>" method="POST">
        <div class="form-group">
          <label for="user_name">Nama Lengkap <span class="text-danger">*</span></label>
          <input type="text" name="name" id="user_name" class="form-control" required placeholder="Contoh: Rahmat Hidayat">
        </div>
        <div class="form-group">
          <label for="user_email">Alamat Email <span class="text-danger">*</span></label>
          <input type="email" name="email" id="user_email" class="form-control" required placeholder="rahmat@warehouse.test">
        </div>
        <div class="form-group">
          <label for="user_username">Username</label>
          <input type="text" name="username" id="user_username" class="form-control" placeholder="rahmat">
        </div>
        <div class="form-group">
          <label for="user_password">Password Awal <span class="text-danger">*</span></label>
          <input type="password" name="password" id="user_password" class="form-control" required minlength="6" placeholder="Minimal 6 karakter">
        </div>
        <div class="form-group">
          <label for="user_role_id">Hak Akses (Role) <span class="text-danger">*</span></label>
          <select name="role_id" id="user_role_id" class="form-select" required>
            <?php foreach ($roles as $r): ?>
              <?php
                // Kepala Gudang tidak dapat membuat user dengan role Maintenance (admin)
                if (isKepalaGudang() && ($r['name'] ?? '') === 'admin') continue;
              ?>
              <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['label'] ?? $r['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="user_company_id">Afiliasi PT (Opsional jika multi-PT)</label>
          <select name="company_id" id="user_company_id" class="form-select">
            <option value="">Multi-Company / Semua PT</option>
            <?php foreach ($companies as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['code'] . ' - ' . $c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">Daftarkan Pengguna</button>
      </form>
    </div>
  </div>

  <!-- Daftar Pengguna -->
  <div class="card">
    <div class="card-header d-flex justify-between align-center">
      <span>Daftar Pengguna Sistem</span>
      <span class="badge badge-secondary"><?= count($users) ?> Pengguna</span>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Nama & Email</th>
            <th>Role / Hak Akses</th>
            <th>PT Terkait</th>
            <th>Status</th>
            <th class="text-right" style="text-align: right;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <?php
              $targetIsMaintenance = ($u['role']['name'] ?? '') === 'admin';
              // Maintenance (admin) bisa edit semua akun
              // Kepala gudang bisa edit akun pengguna lainnya, TAPI TIDAK BISA edit akun Maintenance
              $canEditThisUser = isAdmin() || (isKepalaGudang() && !$targetIsMaintenance);
              $userJson = htmlspecialchars(json_encode([
                  'id' => $u['id'],
                  'name' => $u['name'],
                  'email' => $u['email'],
                  'username' => $u['username'] ?? '',
                  'role_id' => $u['role_id'] ?? ($u['role']['id'] ?? ''),
                  'role_name' => $u['role']['name'] ?? '',
                  'company_id' => $u['company_id'] ?? ($u['company']['id'] ?? ''),
                  'is_active' => (int)($u['is_active'] ?? 1),
              ]), ENT_QUOTES, 'UTF-8');
            ?>
            <tr>
              <td>
                <div class="fw-bold"><?= htmlspecialchars($u['name']) ?></div>
                <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($u['email']) ?></div>
              </td>
              <td>
                <span class="badge badge-primary"><?= htmlspecialchars($u['role']['label'] ?? $u['role']['name'] ?? '-') ?></span>
              </td>
              <td>
                <?= !empty($u['company']) ? renderCompanyBadge($u['company']['code']) : '<span class="text-muted">Semua PT</span>' ?>
              </td>
              <td>
                <span class="badge <?= ($u['is_active'] ?? true) ? 'badge-success' : 'badge-danger' ?>">
                  <?= ($u['is_active'] ?? true) ? 'AKTIF' : 'NONAKTIF' ?>
                </span>
              </td>
              <td class="text-right" style="white-space: nowrap;">
                <?php if ($canEditThisUser): ?>
                  <button type="button" class="btn btn-outline btn-sm" onclick="openEditUserModal(<?= $userJson ?>)" style="color: #2563eb; border-color: #bfdbfe;" title="Edit Pengguna & Hak Akses">
                    <i class="bi bi-pencil-square me-1"></i> Edit
                  </button>
                <?php else: ?>
                  <span class="badge" style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; font-size: 0.75rem; padding: 0.35rem 0.5rem;" title="Role Maintenance hanya dapat diedit oleh akun Maintenance">
                    <i class="bi bi-shield-lock-fill me-1"></i> Terkunci
                  </span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Edit Pengguna & Hak Akses -->
<div id="modalEditUser" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
  <div style="background: #ffffff; border-radius: 12px; max-width: 520px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
    <div style="padding: 1.2rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
      <div style="font-weight: 700; font-size: 1.1rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
        <i class="bi bi-person-gear" style="color: #2563eb;"></i>
        <span>Edit Pengguna & Hak Akses</span>
      </div>
      <button type="button" onclick="closeEditUserModal()" style="background: none; border: none; font-size: 1.5rem; line-height: 1; color: #94a3b8; cursor: pointer;">&times;</button>
    </div>

    <form method="POST" action="<?= url('users/update') ?>" id="formEditUserModal" style="display: flex; flex-direction: column; overflow: hidden; margin: 0;">
      <input type="hidden" name="user_id" id="editUserId" value="">

      <div style="padding: 1.25rem 1.5rem; overflow-y: auto; flex: 1;">
        <div class="form-group mb-2">
          <label class="form-label fw-bold" for="editUserName">Nama Lengkap <span class="text-danger">*</span></label>
          <input type="text" name="name" id="editUserName" class="form-control" required placeholder="Nama lengkap pengguna">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.75rem;">
          <div class="form-group mb-0">
            <label class="form-label fw-bold" for="editUserEmail">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" id="editUserEmail" class="form-control" required placeholder="email@warehouse.test">
          </div>

          <div class="form-group mb-0">
            <label class="form-label fw-bold" for="editUserUsername">Username</label>
            <input type="text" name="username" id="editUserUsername" class="form-control" placeholder="username">
          </div>
        </div>

        <div class="form-group mb-2">
          <label class="form-label fw-bold" for="editUserRoleId" style="color: #1e40af;">
            <i class="bi bi-shield-lock me-1"></i> Pengaturan Role / Hak Akses <span class="text-danger">*</span>
          </label>
          <select name="role_id" id="editUserRoleId" class="form-select" required>
            <?php foreach ($roles as $r): ?>
              <?php
                // Kepala Gudang tidak dapat memberikan role Maintenance (admin)
                if (isKepalaGudang() && ($r['name'] ?? '') === 'admin') continue;
              ?>
              <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['label'] ?? $r['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="text-muted">Menentukan menu dan fungsi yang dapat diakses oleh pengguna ini.</small>
        </div>

        <div class="form-group mb-2">
          <label class="form-label fw-bold" for="editUserCompanyId" style="color: #0369a1;">
            <i class="bi bi-building me-1"></i> Pengaturan PT Akses (Afiliasi Perusahaan)
          </label>
          <select name="company_id" id="editUserCompanyId" class="form-select">
            <option value="">Multi-Company / Semua PT</option>
            <?php foreach ($companies as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['code'] . ' - ' . $c['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="text-muted">Pilih 'Semua PT' untuk akses lintas perusahaan, atau pilih PT spesifik.</small>
        </div>

        <div class="form-group mb-2">
          <label class="form-label fw-bold" for="editUserIsActive">Status Akun</label>
          <select name="is_active" id="editUserIsActive" class="form-select">
            <option value="1">Aktif (Dapat Login ke Sistem)</option>
            <option value="0">Nonaktif (Akses Masuk Dinonaktifkan)</option>
          </select>
        </div>

        <div class="form-group mb-1" style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 0.75rem 1rem;">
          <label class="form-label fw-bold" for="editUserPassword" style="font-size: 0.85rem; color: #475569;">
            <i class="bi bi-key me-1"></i> Reset Password (Opsional)
          </label>
          <input type="password" name="password" id="editUserPassword" class="form-control" minlength="6" placeholder="Biarkan kosong jika tidak diubah" style="font-size: 0.85rem;">
          <small class="text-muted" style="font-size: 0.75rem;">Hanya isi jika ingin mengganti password akun pengguna ini.</small>
        </div>
      </div>

      <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem; align-items: center;">
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeEditUserModal()">Batal</button>
        <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 600;">
          <i class="bi bi-check-circle me-1"></i> Simpan Perubahan Pengguna
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditUserModal(user) {
  document.getElementById('editUserId').value = user.id || '';
  document.getElementById('editUserName').value = user.name || '';
  document.getElementById('editUserEmail').value = user.email || '';
  document.getElementById('editUserUsername').value = user.username || '';
  document.getElementById('editUserRoleId').value = user.role_id || '';
  document.getElementById('editUserCompanyId').value = user.company_id || '';
  document.getElementById('editUserIsActive').value = (user.is_active !== undefined) ? user.is_active : 1;
  document.getElementById('editUserPassword').value = '';

  const modal = document.getElementById('modalEditUser');
  if (modal) {
    modal.style.display = 'flex';
    setTimeout(() => {
      document.getElementById('editUserName').focus();
    }, 100);
  }
}

function closeEditUserModal() {
  const modal = document.getElementById('modalEditUser');
  if (modal) modal.style.display = 'none';
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>

