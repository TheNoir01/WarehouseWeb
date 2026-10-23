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
          <label for="user_password">Password <span class="text-danger">*</span></label>
          <input type="password" name="password" id="user_password" class="form-control" required minlength="6" placeholder="Minimal 6 karakter">
        </div>
        <div class="form-group">
          <label for="user_role_id">Hak Akses <span class="text-danger">*</span></label>
          <select name="role_id" id="user_role_id" class="form-select" required>
            <?php foreach ($roles as $r): ?>
              <?php
                $rName = $r['name'] ?? '';
                // Kepala Gudang hanya dapat membuat akun role Karyawan Operasional Gudang
                if (isKepalaGudang() && $rName !== 'karyawan') continue;
                // Selain akun Maintenance, tidak dapat membuat akun Maintenance (MT)
                if (!isMaintenance() && $rName === 'maintenance') continue;
              ?>
              <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['label'] ?? $r['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="user_company_id">Afiliasi PT</label>
          <select name="company_id" id="user_company_id" class="form-select">
            <option value="">Semua PT</option>
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
              $targetRole = $u['role']['name'] ?? '';
              $targetIsMaintenance = ($targetRole === 'maintenance');
              $isSelf = ((int)$u['id'] === (int)(currentUser()['id'] ?? 0));

              // Hak Akses Edit:
              // 1. Maintenance: Bisa edit semua akun
              // 2. Admin: Bisa edit semua akun KECUALI Maintenance (MT)
              // 3. Kepala Gudang: Cuman bisa edit role sendiri dan karyawan, sisanya terkunci
              if (isMaintenance()) {
                  $canEditThisUser = true;
                  $lockReason = '';
              } elseif (isAdmin()) {
                  $canEditThisUser = !$targetIsMaintenance;
                  $lockReason = 'Role Maintenance hanya dapat dikelola oleh akun Maintenance';
              } elseif (isKepalaGudang()) {
                  $canEditThisUser = ($isSelf || $targetRole === 'karyawan' || $targetRole === 'kepala_gudang');
                  $lockReason = 'Kepala Gudang hanya dapat mengelola akun role sendiri dan Karyawan Operasional Gudang';
              } else {
                  $canEditThisUser = false;
                  $lockReason = 'Akses terkunci';
              }

              // Hak Akses Hapus:
              // 1. Tidak dapat menghapus akun sendiri
              // 2. Maintenance: Bisa hapus semua akun (kecuali akun sendiri)
              // 3. Admin: Bisa hapus semua akun KECUALI Maintenance (MT) & akun sendiri
              // 4. Kepala Gudang: Hanya bisa hapus akun role Karyawan Operasional Gudang
              if ($isSelf) {
                  $canDeleteThisUser = false;
              } elseif (isMaintenance()) {
                  $canDeleteThisUser = true;
              } elseif (isAdmin()) {
                  $canDeleteThisUser = !$targetIsMaintenance;
              } elseif (isKepalaGudang()) {
                  $canDeleteThisUser = ($targetRole === 'karyawan');
              } else {
                  $canDeleteThisUser = false;
              }

              $userJson = htmlspecialchars(json_encode([
                  'id' => $u['id'],
                  'name' => $u['name'],
                  'email' => $u['email'],
                  'username' => $u['username'] ?? '',
                  'role_id' => $u['role_id'] ?? ($u['role']['id'] ?? ''),
                  'role_name' => $targetRole,
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
                <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 0.35rem;">
                  <?php if ($canEditThisUser): ?>
                    <button type="button" class="btn btn-outline btn-sm" onclick="openEditUserModal(<?= $userJson ?>)" style="color: #2563eb; border-color: #bfdbfe;" title="Edit Pengguna & Hak Akses">
                      <i class="bi bi-pencil-square me-1"></i> Edit
                    </button>
                  <?php else: ?>
                    <span class="badge" style="background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; font-size: 0.75rem; padding: 0.35rem 0.5rem;" title="<?= htmlspecialchars($lockReason) ?>">
                      <i class="bi bi-shield-lock-fill me-1"></i> Terkunci
                    </span>
                  <?php endif; ?>

                  <?php if ($canDeleteThisUser): ?>
                    <button type="button" class="btn btn-outline btn-sm" onclick="confirmDeleteUser(<?= (int)$u['id'] ?>, '<?= htmlspecialchars(addslashes($u['name'])) ?>')" style="color: #dc2626; border-color: #fecaca; background: #fff5f5;" title="Hapus Akun Pengguna">
                      <i class="bi bi-trash"></i>
                    </button>
                  <?php endif; ?>
                </div>
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
                $rName = $r['name'] ?? '';
                // Role maintenance hanya dapat diberikan oleh akun Maintenance
                if (!isMaintenance() && $rName === 'maintenance') continue;
              ?>
              <option value="<?= $r['id'] ?>" data-role-name="<?= htmlspecialchars($rName) ?>"><?= htmlspecialchars($r['label'] ?? $r['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="text-muted" id="editRoleHelp">Menentukan menu dan fungsi yang dapat diakses oleh pengguna ini.</small>
        </div>

        <div class="form-group mb-2">
          <label class="form-label fw-bold" for="editUserCompanyId" style="color: #0369a1;">
            <i class="bi bi-building me-1"></i> Pengaturan PT Akses (Afiliasi Perusahaan)
          </label>
          <select name="company_id" id="editUserCompanyId" class="form-select">
            <option value="">Semua PT</option>
            <?php foreach ($companies as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['code'] . ' - ' . $c['name']) ?></option>
            <?php endforeach; ?>
          </select>
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

  const isKG = <?= isKepalaGudang() ? 'true' : 'false' ?>;
  const roleSelect = document.getElementById('editUserRoleId');
  const roleHelp = document.getElementById('editRoleHelp');

  if (isKG) {
    // Kepala Gudang hanya dapat mengubah hak akses antara Kepala Gudang atau Karyawan Operasional Gudang
    Array.from(roleSelect.options).forEach(opt => {
      const optRole = opt.getAttribute('data-role-name');
      opt.style.display = (optRole === 'karyawan' || optRole === 'kepala_gudang') ? '' : 'none';
    });
    if (roleHelp) {
      roleHelp.innerText = 'Kepala Gudang hanya dapat mengubah hak akses antara Kepala Gudang atau Karyawan Operasional Gudang.';
    }
  } else {
    Array.from(roleSelect.options).forEach(opt => {
      opt.style.display = '';
    });
    if (roleHelp) {
      roleHelp.innerText = 'Menentukan menu dan fungsi yang dapat diakses oleh pengguna ini.';
    }
  }

  // Akun sendiri tidak dapat dinonaktifkan statusnya agar tidak mengunci diri sendiri
  const isActiveSelect = document.getElementById('editUserIsActive');
  const myId = <?= (int)(currentUser()['id'] ?? 0) ?>;
  const nonaktifOpt = isActiveSelect.querySelector('option[value="0"]');
  if (nonaktifOpt) {
    nonaktifOpt.disabled = (parseInt(user.id) === myId);
  }

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

function confirmDeleteUser(id, name) {
  if (confirm('Apakah Anda yakin ingin menghapus akun pengguna "' + name + '"?\n\nPerhatian: Akun yang dihapus tidak akan dapat login lagi ke dalam sistem.')) {
    document.getElementById('deleteUserId').value = id;
    document.getElementById('formDeleteUser').submit();
  }
}
</script>

<!-- Form Tersembunyi untuk Hapus Pengguna -->
<form id="formDeleteUser" method="POST" action="<?= url('users/destroy') ?>" style="display: none;">
  <input type="hidden" name="user_id" id="deleteUserId" value="">
</form>

<?php include __DIR__ . '/../layout/footer.php'; ?>

