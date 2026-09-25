<?php

namespace App\Controllers;

use App\Services\ApiService;

class MasterController
{
    protected ApiService $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    // --- CATEGORIES & TYPES ---
    public function categories(): void
    {
        $categories = $this->api->get('categories')['data'] ?? [];
        include __DIR__ . '/../views/masters/categories.php';
    }

    public function categoriesStore(): void
    {
        $res = $this->api->post('categories', [
            'name' => trim($_POST['name'] ?? ''),
        ]);
        if (!empty($res['success'])) {
            redirect('masters/categories', 'Kategori berhasil ditambahkan.');
        } else {
            redirect('masters/categories', $res['message'] ?? 'Gagal menambahkan kategori.', 'danger');
        }
    }

    public function categoriesUpdate(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $res = $this->api->put("categories/{$id}", [
            'name' => trim($_POST['name'] ?? ''),
        ]);
        if (!empty($res['success'])) {
            redirect('masters/categories', 'Kategori berhasil diperbarui.');
        } else {
            redirect('masters/categories', $res['message'] ?? 'Gagal memperbarui kategori.', 'danger');
        }
    }

    public function categoriesDestroy(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $res = $this->api->delete("categories/{$id}");
        if (!empty($res['success'])) {
            redirect('masters/categories', 'Kategori berhasil dihapus.');
        } else {
            redirect('masters/categories', $res['message'] ?? 'Gagal menghapus kategori.', 'danger');
        }
    }

    public function typesStore(): void
    {
        $res = $this->api->post('types', [
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'name' => trim($_POST['name'] ?? ''),
        ]);
        if (!empty($res['success'])) {
            redirect('masters/categories', 'Jenis barang berhasil ditambahkan.');
        } else {
            redirect('masters/categories', $res['message'] ?? 'Gagal menambahkan jenis barang.', 'danger');
        }
    }

    // --- UNITS ---
    public function units(): void
    {
        $units = $this->api->get('units')['data'] ?? [];
        include __DIR__ . '/../views/masters/units.php';
    }

    public function unitsStore(): void
    {
        $res = $this->api->post('units', [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
        ]);
        if (!empty($res['success'])) {
            redirect('masters/units', 'Satuan berhasil ditambahkan.');
        } else {
            redirect('masters/units', $res['message'] ?? 'Gagal menambahkan satuan.', 'danger');
        }
    }

    public function unitsUpdate(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $res = $this->api->put("units/{$id}", [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
        ]);
        if (!empty($res['success'])) {
            redirect('masters/units', 'Satuan berhasil diperbarui.');
        } else {
            redirect('masters/units', $res['message'] ?? 'Gagal memperbarui satuan.', 'danger');
        }
    }

    public function unitsDestroy(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $res = $this->api->delete("units/{$id}");
        if (!empty($res['success'])) {
            redirect('masters/units', 'Satuan berhasil dihapus.');
        } else {
            redirect('masters/units', $res['message'] ?? 'Gagal menghapus satuan.', 'danger');
        }
    }

    // --- SUPPLIERS ---
    public function suppliers(): void
    {
        $suppliers = $this->api->get('suppliers')['data'] ?? [];
        include __DIR__ . '/../views/masters/suppliers.php';
    }

    public function suppliersStore(): void
    {
        $res = $this->api->post('suppliers', [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? '') ?: null,
            'email' => trim($_POST['email'] ?? '') ?: null,
            'address' => trim($_POST['address'] ?? '') ?: null,
        ]);
        if (!empty($res['success'])) {
            redirect('masters/suppliers', 'Supplier berhasil ditambahkan.');
        } else {
            redirect('masters/suppliers', $res['message'] ?? 'Gagal menambahkan supplier.', 'danger');
        }
    }

    public function suppliersUpdate(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $res = $this->api->put("suppliers/{$id}", [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? '') ?: null,
            'email' => trim($_POST['email'] ?? '') ?: null,
            'address' => trim($_POST['address'] ?? '') ?: null,
        ]);
        if (!empty($res['success'])) {
            redirect('masters/suppliers', 'Supplier berhasil diperbarui.');
        } else {
            redirect('masters/suppliers', $res['message'] ?? 'Gagal memperbarui supplier.', 'danger');
        }
    }

    public function suppliersDestroy(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $res = $this->api->delete("suppliers/{$id}");
        if (!empty($res['success'])) {
            redirect('masters/suppliers', 'Supplier berhasil dihapus.');
        } else {
            redirect('masters/suppliers', $res['message'] ?? 'Gagal menghapus supplier.', 'danger');
        }
    }

    // --- LOCATIONS ---
    public function locations(): void
    {
        $warehouses = $this->api->get('warehouses')['data'] ?? [];
        $locations = $this->api->get('locations')['data'] ?? [];
        include __DIR__ . '/../views/masters/locations.php';
    }

    public function locationsStore(): void
    {
        $res = $this->api->post('locations', [
            'warehouse_id' => (int) ($_POST['warehouse_id'] ?? 0),
            'code' => trim($_POST['code'] ?? ''),
            'zone' => trim($_POST['zone'] ?? '') ?: null,
            'rack' => trim($_POST['rack'] ?? '') ?: null,
            'shelf' => trim($_POST['shelf'] ?? '') ?: null,
            'description' => trim($_POST['description'] ?? '') ?: null,
        ]);
        if (!empty($res['success'])) {
            redirect('masters/locations', 'Lokasi rak berhasil ditambahkan.');
        } else {
            redirect('masters/locations', $res['message'] ?? 'Gagal menambahkan lokasi rak.', 'danger');
        }
    }

    public function locationsUpdate(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $res = $this->api->put("locations/{$id}", [
            'warehouse_id' => (int) ($_POST['warehouse_id'] ?? 0),
            'code' => trim($_POST['code'] ?? ''),
            'zone' => trim($_POST['zone'] ?? '') ?: null,
            'rack' => trim($_POST['rack'] ?? '') ?: null,
            'shelf' => trim($_POST['shelf'] ?? '') ?: null,
            'description' => trim($_POST['description'] ?? '') ?: null,
        ]);
        if (!empty($res['success'])) {
            redirect('masters/locations', 'Lokasi rak berhasil diperbarui.');
        } else {
            redirect('masters/locations', $res['message'] ?? 'Gagal memperbarui lokasi rak.', 'danger');
        }
    }

    public function locationsDestroy(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $res = $this->api->delete("locations/{$id}");
        if (!empty($res['success'])) {
            redirect('masters/locations', 'Lokasi rak berhasil dihapus.');
        } else {
            redirect('masters/locations', $res['message'] ?? 'Gagal menghapus lokasi rak.', 'danger');
        }
    }

    // --- COMPANIES ---
    public function companies(): void
    {
        $companies = $this->api->get('companies')['data'] ?? [];
        include __DIR__ . '/../views/masters/companies.php';
    }

    public function companiesStore(): void
    {
        $res = $this->api->post('companies', [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? '') ?: null,
            'address' => trim($_POST['address'] ?? '') ?: null,
        ]);
        if (!empty($res['success'])) {
            redirect('masters/companies', 'PT Pemilik berhasil didaftarkan.');
        } else {
            redirect('masters/companies', $res['message'] ?? 'Gagal mendaftarkan PT.', 'danger');
        }
    }

    public function companiesUpdate(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $res = $this->api->put("companies/{$id}", [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? '') ?: null,
            'address' => trim($_POST['address'] ?? '') ?: null,
        ]);
        if (!empty($res['success'])) {
            redirect('masters/companies', 'Data PT berhasil diperbarui.');
        } else {
            redirect('masters/companies', $res['message'] ?? 'Gagal memperbarui PT.', 'danger');
        }
    }

    public function companiesDestroy(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $res = $this->api->delete("companies/{$id}");
        if (!empty($res['success'])) {
            redirect('masters/companies', 'PT berhasil dihapus.');
        } else {
            redirect('masters/companies', $res['message'] ?? 'Gagal menghapus PT.', 'danger');
        }
    }
}
