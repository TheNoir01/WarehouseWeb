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

        $payload = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'username' => trim($_POST['username'] ?? '') ?: null,
            'password' => $_POST['password'] ?? '',
            'role_id' => (int) ($_POST['role_id'] ?? 0),
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

        $payload = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'username' => trim($_POST['username'] ?? '') ?: null,
            'role_id' => (int) ($_POST['role_id'] ?? 0),
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
}
