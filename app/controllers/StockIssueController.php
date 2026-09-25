<?php

namespace App\Controllers;

use App\Services\ApiService;

class StockIssueController
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
            'status' => $_GET['status'] ?? '',
        ];

        $res = $this->api->get('goods-issues', array_filter($params));
        $issues = $res['data'] ?? [];
        $meta = $res['meta'] ?? [];

        $companies = $this->api->get('companies')['data'] ?? [];

        include __DIR__ . '/../views/issues/index.php';
    }

    public function create(): void
    {
        $locations = $this->api->get('locations')['data'] ?? [];

        include __DIR__ . '/../views/issues/create.php';
    }

    public function store(): void
    {
        $rawItems = $_POST['items'] ?? [];
        $items = [];
        foreach ($rawItems as $row) {
            $qty = isset($row['qty_issued']) ? (float) str_replace(',', '.', trim((string) $row['qty_issued'])) : 0.0;
            if (!empty($row['item_id']) && $qty > 0) {
                $items[] = [
                    'item_id' => (int) $row['item_id'],
                    'warehouse_location_id' => !empty($row['warehouse_location_id']) ? (int) $row['warehouse_location_id'] : 1,
                    'qty_issued' => $qty,
                    'notes' => trim($row['notes'] ?? ''),
                ];
            }
        }

        if (empty($items)) {
            redirect('issues/create', 'Harap masukkan minimal 1 barang dengan kuantitas valid.', 'danger');
        }

        $attachments = [];
        // Handle handover photo upload
        if (!empty($_FILES['attachment_file']['tmp_name']) && is_uploaded_file($_FILES['attachment_file']['tmp_name'])) {
            $uploadRes = $this->api->post('uploads', ['type' => 'photo_handover'], ['file' => $_FILES['attachment_file']]);
            if (!empty($uploadRes['success']) && !empty($uploadRes['data'])) {
                $attachments[] = $uploadRes['data'];
            }
        }

        $companyId = !empty($_POST['company_id']) ? (int) $_POST['company_id'] : null;

        $payload = [
            'company_id' => $companyId,
            'project_name' => trim($_POST['project_name'] ?? ''),
            'requester_name' => trim($_POST['requester_name'] ?? ''),
            'recipient_name' => trim($_POST['recipient_name'] ?? ''),
            'issued_date' => $_POST['issued_date'] ?? date('Y-m-d'),
            'notes' => trim($_POST['notes'] ?? ''),
            'items' => $items,
            'attachments' => $attachments,
        ];

        $res = $this->api->post('goods-issues', $payload);

        if (!empty($res['success'])) {
            redirect('issues/show&id=' . $res['data']['id'], 'Dokumen pengeluaran berhasil diproses dan stok telah dikurangi!');
        } else {
            $msg = $res['message'] ?? 'Gagal memproses pengeluaran barang.';
            redirect('issues/create', $msg, 'danger');
        }
    }

    public function show(): void
    {
        $id = $_GET['id'] ?? null;
        if (!$id) redirect('issues');

        $res = $this->api->get("goods-issues/{$id}");
        if (empty($res['success'])) {
            redirect('issues', 'Dokumen pengeluaran tidak ditemukan.', 'danger');
        }

        $issue = $res['data'];
        include __DIR__ . '/../views/issues/show.php';
    }

    public function ajaxGet(): void
    {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']);
            exit;
        }

        $res = $this->api->get("goods-issues/{$id}");
        echo json_encode($res);
        exit;
    }
}
