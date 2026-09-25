<?php

namespace App\Controllers;

use App\Services\ApiService;

class ReturnController
{
    protected ApiService $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index(): void
    {
        $params = [
            'page' => $_GET['page'] ?? 1,
            'search' => $_GET['search'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
        ];

        $res = $this->api->get('returns', array_filter($params));
        $returns = $res['data'] ?? [];
        $meta = $res['meta'] ?? [];

        $companies = $this->api->get('companies')['data'] ?? [];

        include __DIR__ . '/../views/returns/index.php';
    }

    public function create(): void
    {
        $companies = $this->api->get('companies')['data'] ?? [];
        $locations = $this->api->get('locations')['data'] ?? [];
        
        // Load open/partially returned issues
        $issuesRes = $this->api->get('goods-issues', ['per_page' => 50]);
        $issues = $issuesRes['data'] ?? [];

        include __DIR__ . '/../views/returns/create.php';
    }

    public function store(): void
    {
        $rawItems = $_POST['items'] ?? [];
        $items = [];
        foreach ($rawItems as $row) {
            $qty = isset($row['qty_returned']) ? (float) str_replace(',', '.', trim((string) $row['qty_returned'])) : 0.0;
            if (!empty($row['stock_issue_item_id']) && $qty > 0) {
                $itemPayload = [
                    'stock_issue_item_id' => (int) $row['stock_issue_item_id'],
                    'warehouse_location_id' => (int) $row['warehouse_location_id'],
                    'qty_returned' => $qty,
                    'return_status' => $row['return_status'] ?? 'sisa',
                    'condition' => $row['condition'] ?? 'good',
                    'notes' => trim($row['notes'] ?? ''),
                ];

                if ($itemPayload['return_status'] === 'sisa_material' && !empty($row['material_remnant'])) {
                    $itemPayload['material_remnant'] = [
                        'shape_condition' => $row['material_remnant']['shape_condition'] ?? 'Tidak Beraturan',
                        'dimension_description' => $row['material_remnant']['dimension_description'] ?? 'Sisa Potongan',
                        'estimated_area' => !empty($row['material_remnant']['estimated_area']) ? (float)$row['material_remnant']['estimated_area'] : null,
                        'estimated_weight' => !empty($row['material_remnant']['estimated_weight']) ? (float)$row['material_remnant']['estimated_weight'] : null,
                    ];
                }

                $items[] = $itemPayload;
            }
        }

        if (empty($items)) {
            redirect('returns/create', 'Harap masukkan minimal 1 barang dengan kuantitas valid.', 'danger');
        }

        $attachments = [];
        if (!empty($_FILES['attachment_file']['tmp_name']) && is_uploaded_file($_FILES['attachment_file']['tmp_name'])) {
            $uploadRes = $this->api->post('uploads', ['type' => 'photo_return'], ['file' => $_FILES['attachment_file']]);
            if (!empty($uploadRes['success']) && !empty($uploadRes['data'])) {
                $attachments[] = $uploadRes['data'];
            }
        }

        $payload = [
            'stock_issue_id' => (int) ($_POST['stock_issue_id'] ?? 0),
            'returned_by_name' => trim($_POST['returned_by_name'] ?? ''),
            'returned_date' => $_POST['returned_date'] ?? date('Y-m-d'),
            'notes' => trim($_POST['notes'] ?? ''),
            'items' => $items,
            'attachments' => $attachments,
        ];

        $res = $this->api->post('returns', $payload);

        if (!empty($res['success'])) {
            redirect('returns/show&id=' . $res['data']['id'], 'Dokumen pengembalian berhasil diproses dan stok telah ditambahkan kembali!');
        } else {
            $msg = $res['message'] ?? 'Gagal memproses pengembalian barang.';
            redirect('returns/create', $msg, 'danger');
        }
    }

    public function show(): void
    {
        $id = $_GET['id'] ?? null;
        if (!$id) redirect('returns');

        $res = $this->api->get("returns/{$id}");
        if (empty($res['success'])) {
            redirect('returns', 'Dokumen pengembalian tidak ditemukan.', 'danger');
        }

        $return = $res['data'];
        include __DIR__ . '/../views/returns/show.php';
    }
}
