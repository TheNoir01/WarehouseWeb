<?php

namespace App\Controllers;

use App\Services\ApiService;

class UserController
{
    protected ApiService $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index(): void
    {
        if (!canManageUsers()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk mengelola pengguna & hak akses.', 'danger');
        }

        $users = $this->api->get('users')['data'] ?? [];
        $roles = $this->api->get('roles')['data'] ?? [];
        $companies = $this->api->get('companies')['data'] ?? [];

        include __DIR__ . '/../views/users/index.php';
    }

    public function store(): void
    {
        if (!canManageUsers()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk mengelola pengguna & hak akses.', 'danger');
        }

        $roleId = (int) ($_POST['role_id'] ?? 0);
        $roles = $this->api->get('roles')['data'] ?? [];
        $selectedRole = null;
        foreach ($roles as $r) {
            if ((int) $r['id'] === $roleId) {
                $selectedRole = $r;
                break;
            }
        }

        if (isKepalaGudang() && ($selectedRole['name'] ?? '') !== 'karyawan') {
            redirect('users', 'Akses ditolak: Kepala Gudang hanya dapat membuat akun dengan role Karyawan Operasional Gudang.', 'danger');
        }

        if (!isMaintenance() && ($selectedRole['name'] ?? '') === 'maintenance') {
            redirect('users', 'Akses ditolak: Hanya akun Maintenance yang dapat membuat pengguna dengan hak akses Maintenance.', 'danger');
        }

        $payload = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'username' => trim($_POST['username'] ?? '') ?: null,
            'password' => $_POST['password'] ?? '',
            'role_id' => $roleId,
            'company_id' => !empty($_POST['company_id']) ? (int) $_POST['company_id'] : null,
        ];

        $res = $this->api->post('users', $payload);

        if (!empty($res['success'])) {
            redirect('users', 'Pengguna baru berhasil didaftarkan.');
        } else {
            redirect('users', $res['message'] ?? 'Gagal mendaftarkan pengguna.', 'danger');
        }
    }

    public function update(): void
    {
        if (!canManageUsers()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk mengelola pengguna & hak akses.', 'danger');
        }

        $id = (int) ($_POST['user_id'] ?? 0);
        if (!$id) {
            redirect('users');
        }

        $roleId = (int) ($_POST['role_id'] ?? 0);
        $roles = $this->api->get('roles')['data'] ?? [];
        $selectedRole = null;
        foreach ($roles as $r) {
            if ((int) $r['id'] === $roleId) {
                $selectedRole = $r;
                break;
            }
        }

        if (!isMaintenance()) {
            if (($selectedRole['name'] ?? '') === 'maintenance') {
                redirect('users', 'Akses ditolak: Anda tidak dapat memberikan hak akses Maintenance.', 'danger');
            }

            $allUsers = $this->api->get('users')['data'] ?? [];
            $targetUser = null;
            foreach ($allUsers as $u) {
                if ((int) $u['id'] === $id) {
                    $targetUser = $u;
                    break;
                }
            }
            $targetRole = $targetUser['role']['name'] ?? '';

            if ($targetRole === 'maintenance') {
                redirect('users', 'Akses ditolak: Akun Maintenance (MT) tidak dapat diubah.', 'danger');
            }

            if (isKepalaGudang()) {
                $myId = (int) (currentUser()['id'] ?? 0);
                if ($id !== $myId && !in_array($targetRole, ['karyawan', 'kepala_gudang'])) {
                    redirect('users', 'Akses ditolak: Kepala Gudang hanya dapat mengelola akun role sendiri dan Karyawan Operasional Gudang.', 'danger');
                }

                if (!in_array($selectedRole['name'] ?? '', ['karyawan', 'kepala_gudang'])) {
                    redirect('users', 'Akses ditolak: Kepala Gudang hanya dapat memilih role Kepala Gudang atau Karyawan Operasional Gudang.', 'danger');
                }
            }
        }

        $payload = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'username' => trim($_POST['username'] ?? '') ?: null,
            'role_id' => $roleId,
            'company_id' => !empty($_POST['company_id']) ? (int) $_POST['company_id'] : null,
            'is_active' => isset($_POST['is_active']) ? (int) $_POST['is_active'] : 1,
        ];

        if (!empty($_POST['password'])) {
            $payload['password'] = $_POST['password'];
        }

        $res = $this->api->put("users/{$id}", $payload);

        if (!empty($res['success'])) {
            redirect('users', 'Data pengguna [' . ($res['data']['name'] ?? '') . '] berhasil diperbarui!');
        } else {
            redirect('users', $res['message'] ?? 'Gagal memperbarui pengguna.', 'danger');
        }
    }

    public function destroy(): void
    {
        $id = (int) ($_POST['user_id'] ?? $_GET['id'] ?? 0);
        if (!$id) {
            redirect('users', 'ID pengguna tidak valid.', 'danger');
        }

        $myId = (int) (currentUser()['id'] ?? 0);
        if ($id === $myId) {
            redirect('users', 'Akses ditolak: Anda tidak dapat menghapus akun Anda sendiri.', 'danger');
        }

        $res = $this->api->delete("users/{$id}");

        if (!empty($res['success'])) {
            redirect('users', $res['message'] ?? 'Akun pengguna berhasil dihapus dari sistem.');
        } else {
            redirect('users', $res['message'] ?? 'Gagal menghapus akun pengguna.', 'danger');
        }
    }
}
