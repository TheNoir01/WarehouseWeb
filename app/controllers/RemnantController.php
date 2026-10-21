<?php

namespace App\Controllers;

use App\Services\ApiService;

class RemnantController
{
    protected ApiService $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    private function mapRemnantStatus(?string $status): string
    {
        $status = strtolower(trim((string)$status));
        $map = [
            'tersedia' => 'available',
            'terpakai' => 'used',
            'scrap' => 'scrapped',
            'available' => 'available',
            'used' => 'used',
            'scrapped' => 'scrapped',
        ];
        return $map[$status] ?? $status;
    }

    public function index(): void
    {
        $rawStatus = $_GET['status'] ?? '';
        $params = [
            'page' => $_GET['page'] ?? 1,
            'search' => $_GET['search'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
            'status' => $this->mapRemnantStatus($rawStatus),
        ];

        $res = $this->api->get('remnants', array_filter($params));
        $remnants = $res['data'] ?? [];
        $meta = $res['meta'] ?? [];

        $companies = $this->api->get('companies')['data'] ?? [];

        include __DIR__ . '/../views/remnants/index.php';
    }

    public function show(): void
    {
        $id = $_GET['id'] ?? null;
        if (!$id) redirect('remnants');

        $res = $this->api->get("remnants/{$id}");
        if (empty($res['success'])) {
            redirect('remnants', 'Material sisa tidak ditemukan.', 'danger');
        }

        $remnantData = $res['data'];
        $traceability = $remnantData['traceability'] ?? [];

        include __DIR__ . '/../views/remnants/show.php';
    }

    public function update(): void
    {
        if (!canManageMaster()) {
            redirect('remnants', 'Akses ditolak.', 'danger');
        }

        $id = $_GET['id'] ?? null;
        if (!$id) redirect('remnants');

        $status = $this->mapRemnantStatus($_POST['status'] ?? 'available');

        $res = $this->api->put("remnants/{$id}", [
            'status' => $status,
        ]);

        if (!empty($res['success'])) {
            redirect('remnants/show&id=' . $id, 'Status material sisa berhasil diperbarui.');
        } else {
            redirect('remnants/show&id=' . $id, 'Gagal memperbarui status material sisa.', 'danger');
        }
    }
}
