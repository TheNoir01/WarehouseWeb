<?php

namespace App\Controllers;

use App\Services\ApiService;

class GoodsReceiptController
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

        $res = $this->api->get('goods-receipts', array_filter($params));
        $receipts = $res['data'] ?? [];
        $meta = $res['meta'] ?? [];

        $companies = $this->api->get('companies')['data'] ?? [];

        include __DIR__ . '/../views/receipts/index.php';
    }

    public function create(): void
    {
        if (!canManageMaster()) {
            redirect('receipts', 'Akses ditolak. Hanya Kepala Gudang / Admin yang dapat menginput barang masuk.', 'danger');
        }

        $companies = $this->api->get('companies')['data'] ?? [];
        $warehouses = $this->api->get('warehouses')['data'] ?? [];

        // Check if company code or id is requested (e.g. company=KJG or company=LNP)
        $companyCode = strtoupper(trim($_GET['company'] ?? ''));
        $companyId = !empty($_GET['company_id']) ? (int) $_GET['company_id'] : null;

        $targetCompany = null;
        if ($companyCode) {
            foreach ($companies as $comp) {
                if (strtoupper($comp['code']) === $companyCode) {
                    $targetCompany = $comp;
                    break;
                }
            }
        } elseif ($companyId) {
            foreach ($companies as $comp) {
                if ((int) $comp['id'] === $companyId) {
                    $targetCompany = $comp;
                    break;
                }
            }
        }

        // Default to first company (KJG) if not matched
        if (!$targetCompany && !empty($companies)) {
            $targetCompany = $companies[0];
        }

        $newItemId = !empty($_GET['new_item_id']) ? (int) $_GET['new_item_id'] : null;
        $newItem = null;
        if ($newItemId) {
            $singleRes = $this->api->get("items/{$newItemId}");
            if (!empty($singleRes['data'])) {
                $newItem = $singleRes['data'];
            }
        }

        include __DIR__ . '/../views/receipts/create.php';
    }

    public function store(): void
    {
        if (!canManageMaster()) {
            redirect('receipts', 'Akses ditolak.', 'danger');
        }

        $rawItems = $_POST['items'] ?? [];
        $items = [];
        $defaultLocId = 1;
        $locRes = $this->api->get('locations');
        if (!empty($locRes['data'])) {
            $defaultLocId = (int) $locRes['data'][0]['id'];
        }

        foreach ($rawItems as $row) {
            $qty = isset($row['qty']) ? (float) str_replace(',', '.', trim((string) $row['qty'])) : 0.0;
            if (!empty($row['item_id']) && $qty > 0) {
                $locId = !empty($row['warehouse_location_id']) ? (int) $row['warehouse_location_id'] : $defaultLocId;
                $items[] = [
                    'item_id' => (int) $row['item_id'],
                    'warehouse_location_id' => $locId,
                    'qty' => $qty,
                    'condition' => $row['condition'] ?? 'good',
                    'notes' => trim($row['notes'] ?? ''),
                ];
            }
        }

        if (empty($items)) {
            redirect('receipts/create', 'Harap masukkan minimal 1 barang dengan kuantitas valid.', 'danger');
        }

        $attachments = [];
        // Handle file upload if present
        if (!empty($_FILES['attachment_file']['tmp_name']) && is_uploaded_file($_FILES['attachment_file']['tmp_name'])) {
            $uploadRes = $this->api->post('uploads', ['type' => 'document'], ['file' => $_FILES['attachment_file']]);
            if (!empty($uploadRes['success']) && !empty($uploadRes['data'])) {
                $attachments[] = $uploadRes['data'];
            }
        }

        $supplierName = trim($_POST['supplier_name'] ?? '');
        $deliveryOrderNumber = trim($_POST['delivery_order_number'] ?? '');
        $warehouseId = !empty($_POST['warehouse_id']) ? (int) $_POST['warehouse_id'] : null;
        $supplierId = !empty($_POST['supplier_id']) ? (int) $_POST['supplier_id'] : null;

        $payload = [
            'company_id' => (int) ($_POST['company_id'] ?? 0),
            'supplier_name' => !empty($supplierName) ? $supplierName : null,
            'warehouse_id' => $warehouseId,
            'received_date' => $_POST['received_date'] ?? date('Y-m-d'),
            'notes' => trim($_POST['notes'] ?? ''),
            'items' => $items,
            'attachments' => $attachments,
        ];

        if ($supplierId) {
            $payload['supplier_id'] = $supplierId;
        }
        if (!empty($deliveryOrderNumber)) {
            $payload['delivery_order_number'] = $deliveryOrderNumber;
        }

        $res = $this->api->post('goods-receipts', $payload);

        if (!empty($res['success'])) {
            redirect('receipts/show&id=' . $res['data']['id'], 'Dokumen penerimaan berhasil disimpan dan saldo stok telah bertambah!');
        } else {
            $msg = $res['message'] ?? 'Gagal menyimpan penerimaan barang.';
            redirect('receipts/create', $msg, 'danger');
        }
    }

    public function show(): void
    {
        $id = $_GET['id'] ?? null;
        if (!$id) redirect('receipts');

        $res = $this->api->get("goods-receipts/{$id}");
        if (empty($res['success'])) {
            redirect('receipts', 'Dokumen penerimaan tidak ditemukan.', 'danger');
        }

        $receipt = $res['data'];
        include __DIR__ . '/../views/receipts/show.php';
    }

    public function exportExcel(): void
    {
        $res = $this->api->get('goods-receipts/export-data');
        $receipts = $res['data'] ?? [];

        // Month names in Indonesian
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        // Group receipts by Month (YYYY-MM) and Company Code
        $grouped = [];
        $allCompanies = ['KJG' => 'PT Karunia Jaya Global', 'LNP' => 'PT Lestari Nusantara Perkasa'];

        // Gather all distinct months
        $monthsFound = [];
        foreach ($receipts as $r) {
            $date = $r['received_date'] ?? substr($r['created_at'] ?? date('Y-m-d'), 0, 10);
            $ym = substr($date, 0, 7); // e.g. "2026-09"
            if ($ym) {
                $monthsFound[$ym] = true;
            }
        }

        // If no data exists, default to current month
        if (empty($monthsFound)) {
            $monthsFound[date('Y-m')] = true;
        }

        ksort($monthsFound); // sort months chronologically (earliest to latest)

        // Initialize groups for each month and company
        foreach (array_keys($monthsFound) as $ym) {
            foreach ($allCompanies as $cCode => $cName) {
                $grouped[$ym][$cCode] = [];
            }
        }

        // Distribute receipts items
        foreach ($receipts as $r) {
            $rawDate = (string) ($r['received_date'] ?? $r['created_at'] ?? date('Y-m-d'));
            $date = substr($rawDate, 0, 10);
            $ym = substr($date, 0, 7);
            $cCode = strtoupper($r['company']['code'] ?? 'KJG');
            if (!isset($allCompanies[$cCode])) {
                $allCompanies[$cCode] = $r['company']['name'] ?? $cCode;
            }
            if (!isset($grouped[$ym][$cCode])) {
                $grouped[$ym][$cCode] = [];
            }

            $docNum = $r['receipt_number'] ?? '-';
            $supplier = $r['supplier']['name'] ?? $r['supplier_name'] ?? '-';
            $ref = $r['delivery_order_number'] ?? '-';
            $receiver = $r['received_by']['name'] ?? '-';

            $items = $r['items'] ?? [];
            if (empty($items)) {
                $grouped[$ym][$cCode][] = [
                    'doc_num' => $docNum,
                    'date' => $date,
                    'company' => $cCode,
                    'supplier' => $supplier,
                    'ref' => $ref,
                    'item_code' => '-',
                    'item_name' => '(Tanpa Item)',
                    'qty' => 0,
                    'unit' => '-',
                    'condition' => '-',
                    'notes' => $r['notes'] ?? '-',
                    'receiver' => $receiver,
                ];
            } else {
                foreach ($items as $it) {
                    $itemObj = $it['item'] ?? [];
                    $grouped[$ym][$cCode][] = [
                        'doc_num' => $docNum,
                        'date' => $date,
                        'company' => $cCode,
                        'supplier' => $supplier,
                        'ref' => $ref,
                        'item_code' => $itemObj['item_code'] ?? '-',
                        'item_name' => $itemObj['name'] ?? '-',
                        'qty' => (float) ($it['qty'] ?? 0),
                        'unit' => $itemObj['unit']['code'] ?? $itemObj['unit']['name'] ?? '-',
                        'condition' => ucfirst($it['condition'] ?? 'good'),
                        'notes' => $it['notes'] ?? $r['notes'] ?? '-',
                        'receiver' => $receiver,
                    ];
                }
            }
        }

        $headers = [
            'No',
            'No. Dokumen',
            'Tanggal Terima',
            'PT Pemilik',
            'Supplier / Vendor',
            'No. Surat Jalan / PO',
            'Kode Barang',
            'Nama Barang',
            'Qty Masuk',
            'Satuan',
            'Kondisi',
            'Keterangan / Catatan',
            'Petugas Penerima',
        ];

        $colWidths = [6, 20, 15, 14, 26, 22, 16, 34, 14, 12, 12, 26, 20];

        $writer = new \App\Services\SimpleXlsxWriter();

        // Create sheets: per month, then per PT
        // e.g. Sheet 1: KJG - Sep 2026, Sheet 2: LNP - Sep 2026
        foreach ($grouped as $ym => $companiesData) {
            $parts = explode('-', $ym);
            $year = $parts[0] ?? date('Y');
            $mNum = isset($parts[1]) ? (int) $parts[1] : (int) date('m');
            $mLabel = ($monthNames[$mNum] ?? 'Bln') . ' ' . $year;

            foreach ($companiesData as $cCode => $rowsData) {
                $sheetTitle = "{$cCode} - {$mLabel}";
                $sheetRows = [];
                $no = 1;

                if (empty($rowsData)) {
                    $sheetRows[] = [
                        1,
                        '-',
                        '-',
                        $cCode,
                        '-',
                        '-',
                        '-',
                        'Tidak ada transaksi barang masuk pada periode ini',
                        0,
                        '-',
                        '-',
                        '-',
                        '-',
                    ];
                } else {
                    foreach ($rowsData as $row) {
                        $sheetRows[] = [
                            $no++,
                            $row['doc_num'],
                            $row['date'],
                            $row['company'],
                            $row['supplier'],
                            $row['ref'],
                            $row['item_code'],
                            $row['item_name'],
                            $row['qty'],
                            $row['unit'],
                            $row['condition'],
                            $row['notes'],
                            $row['receiver'],
                        ];
                    }
                }

                $writer->addSheet($sheetTitle, $headers, $sheetRows, $colWidths);
            }
        }

        $filename = 'Laporan_Barang_Masuk_' . date('Ymd_His') . '.xlsx';
        $writer->download($filename);
    }
}
