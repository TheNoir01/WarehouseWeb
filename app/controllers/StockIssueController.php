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
        $statusParam = trim($_GET['status'] ?? '');
        $mappedStatus = match ($statusParam) {
            'Belum Kembali', 'open' => 'open',
            'Sebagian Kembali', 'partially_returned' => 'partially_returned',
            'Sudah Kembali', 'fully_returned' => 'fully_returned',
            default => $statusParam,
        };

        $params = [
            'page' => $_GET['page'] ?? 1,
            'search' => $_GET['search'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
            'status' => $mappedStatus,
        ];

        $res = $this->api->get('goods-issues', array_filter($params, fn($v) => $v !== '' && $v !== null));
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

    public function ajaxSearch(): void
    {
        header('Content-Type: application/json');
        $q = trim($_GET['q'] ?? '');
        $res = $this->api->get('goods-issues', [
            'search' => $q,
            'per_page' => 30
        ]);
        echo json_encode($res);
        exit;
    }

    public function exportExcel(): void
    {
        $res = $this->api->get('goods-issues/export-data');
        $issues = $res['data'] ?? [];

        // Month names in Indonesian
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        // Group issues by Month (YYYY-MM) and Company Code
        $grouped = [];
        $allCompanies = ['KJG' => 'PT Karunia Jaya Global', 'LNP' => 'PT Lestari Nusantara Perkasa'];

        // Gather all distinct months
        $monthsFound = [];
        foreach ($issues as $iss) {
            $date = $iss['issued_date'] ?? substr($iss['created_at'] ?? date('Y-m-d'), 0, 10);
            $ym = substr($date, 0, 7);
            if ($ym) {
                $monthsFound[$ym] = true;
            }
        }

        if (empty($monthsFound)) {
            $monthsFound[date('Y-m')] = true;
        }

        ksort($monthsFound);

        foreach (array_keys($monthsFound) as $ym) {
            foreach ($allCompanies as $cCode => $cName) {
                $grouped[$ym][$cCode] = [];
            }
        }

        foreach ($issues as $iss) {
            $rawDate = (string) ($iss['issued_date'] ?? $iss['created_at'] ?? date('Y-m-d'));
            $date = substr($rawDate, 0, 10);
            $ym = substr($date, 0, 7);
            $cCode = strtoupper($iss['company']['code'] ?? 'KJG');
            if (!isset($allCompanies[$cCode])) {
                $allCompanies[$cCode] = $iss['company']['name'] ?? $cCode;
            }
            if (!isset($grouped[$ym][$cCode])) {
                $grouped[$ym][$cCode] = [];
            }

            $docNum = $iss['issue_number'] ?? '-';
            $project = $iss['project_name'] ?? '-';
            $requester = $iss['requester_name'] ?? '-';
            $recipient = $iss['recipient_name'] ?? '-';
            $status = match ($iss['status'] ?? '') {
                'open' => 'Belum Kembali',
                'partially_returned' => 'Sebagian Kembali',
                'fully_returned' => 'Sudah Kembali',
                default => $iss['status'] ?? '-',
            };
            $issuedBy = $iss['issued_by']['name'] ?? '-';

            $items = $iss['items'] ?? [];
            if (empty($items)) {
                $grouped[$ym][$cCode][] = [
                    'doc_num' => $docNum,
                    'date' => $date,
                    'company' => $cCode,
                    'project' => $project,
                    'requester' => $requester,
                    'recipient' => $recipient,
                    'item_code' => '-',
                    'item_name' => '(Tanpa Item)',
                    'qty' => 0,
                    'unit' => '-',
                    'location' => '-',
                    'status' => $status,
                    'notes' => $iss['notes'] ?? '-',
                    'issued_by' => $issuedBy,
                ];
            } else {
                foreach ($items as $it) {
                    $itemObj = $it['item'] ?? [];
                    $qty = (float) ($it['qty_issued'] ?? 0);
                    $unitName = $itemObj['unit']['name'] ?? ($it['unit'] ?? '-');
                    $locationName = $it['location']['name'] ?? '-';
                    $itemNotes = trim((string) ($it['notes'] ?? ''));
                    $rowNote = $itemNotes !== '' ? $itemNotes : ($iss['notes'] ?? '-');

                    $grouped[$ym][$cCode][] = [
                        'doc_num' => $docNum,
                        'date' => $date,
                        'company' => $cCode,
                        'project' => $project,
                        'requester' => $requester,
                        'recipient' => $recipient,
                        'item_code' => $itemObj['item_code'] ?? '-',
                        'item_name' => $itemObj['name'] ?? '-',
                        'qty' => $qty,
                        'unit' => $unitName,
                        'location' => $locationName,
                        'status' => $status,
                        'notes' => $rowNote,
                        'issued_by' => $issuedBy,
                    ];
                }
            }
        }

        $headers = [
            'No.',
            'No. Dokumen',
            'Tanggal Keluar',
            'PT Pemilik',
            'Proyek / Keperluan',
            'Divisi / Bagian',
            'Penerima / Pengambil',
            'Kode Barang',
            'Nama Barang',
            'Qty Keluar',
            'Satuan',
            'Lokasi Gudang',
            'Status Retur',
            'Keterangan / Catatan',
            'Petugas Lapangan',
        ];

        $colWidths = [6, 20, 15, 14, 25, 18, 22, 16, 32, 14, 10, 18, 16, 25, 20];

        $writer = new \App\Services\SimpleXlsxWriter();

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
                        '-',
                        'Tidak ada transaksi barang keluar pada periode ini',
                        0,
                        '-',
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
                            $row['project'],
                            $row['requester'],
                            $row['recipient'],
                            $row['item_code'],
                            $row['item_name'],
                            $row['qty'],
                            $row['unit'],
                            $row['location'],
                            $row['status'],
                            $row['notes'],
                            $row['issued_by'],
                        ];
                    }
                }

                $writer->addSheet($sheetTitle, $headers, $sheetRows, $colWidths);
            }
        }

        $filename = 'Laporan_Barang_Keluar_' . date('d-m-Y') . '.xlsx';
        $writer->download($filename);
    }
}

