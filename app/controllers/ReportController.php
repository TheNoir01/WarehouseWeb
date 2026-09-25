<?php

namespace App\Controllers;

use App\Services\ApiService;

class ReportController
{
    protected ApiService $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function stock(): void
    {
        if (!canViewReports()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk melihat laporan stok per PT.', 'danger');
        }

        $params = [
            'page' => $_GET['page'] ?? 1,
            'search' => $_GET['search'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
        ];

        $res = $this->api->get('stock', array_filter($params));
        $balances = $res['data'] ?? [];
        $meta = $res['meta'] ?? [];

        $companies = $this->api->get('companies')['data'] ?? [];

        include __DIR__ . '/../views/reports/stock.php';
    }

    public function movements(): void
    {
        if (!canViewReports()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk melihat mutasi stok.', 'danger');
        }

        $params = [
            'page' => $_GET['page'] ?? 1,
            'search' => $_GET['search'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
            'movement_type' => $_GET['movement_type'] ?? '',
        ];

        $res = $this->api->get('stock/movements', array_filter($params));
        $movements = $res['data'] ?? [];
        $meta = $res['meta'] ?? [];

        $companies = $this->api->get('companies')['data'] ?? [];

        include __DIR__ . '/../views/reports/movements.php';
    }

    public function stockExportExcel(): void
    {
        if (!canViewReports()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk mengunduh laporan stok per PT.', 'danger');
        }

        $params = [
            'all' => 1,
            'search' => $_GET['search'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
        ];

        $res = $this->api->get('stock', array_filter($params, fn($v) => $v !== null && $v !== ''));
        $balances = $res['data'] ?? [];

        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $allCompanies = ['KJG' => 'PT Karunia Jaya Global', 'LNP' => 'PT Lestari Nusantara Perkasa'];

        $currentYm = date('Y-m');
        $grouped = [
            $currentYm => [
                'KJG' => [],
                'LNP' => [],
            ]
        ];

        foreach ($balances as $b) {
            $rawDate = (string) ($b['updated_at'] ?? $b['created_at'] ?? date('Y-m-d'));
            $date = substr($rawDate, 0, 10);
            $cCode = strtoupper($b['company']['code'] ?? 'KJG');
            if (!isset($allCompanies[$cCode])) {
                $allCompanies[$cCode] = $b['company']['name'] ?? $cCode;
            }
            if (!isset($grouped[$currentYm][$cCode])) {
                $grouped[$currentYm][$cCode] = [];
            }

            $item = $b['item'] ?? [];
            $stock = (float) ($b['qty'] ?? 0);
            $min = (float) ($item['minimum_stock'] ?? 0);
            $status = ($stock <= 0) ? 'HABIS' : (($min > 0 && $stock <= $min) ? 'MENIPIS' : 'TERSEDIA');

            $grouped[$currentYm][$cCode][] = [
                'company' => $cCode,
                'item_code' => $item['item_code'] ?? '-',
                'item_name' => $item['name'] ?? '-',
                'category' => $item['category']['name'] ?? '-',
                'unit' => $item['unit']['code'] ?? $item['unit']['name'] ?? '-',
                'qty' => $stock,
                'status' => $status,
                'supplier' => $item['suppliers_summary'] ?? '-',
                'last_updated' => $date,
            ];
        }

        $headers = [
            'No',
            'PT Pemilik',
            'ID / Kode Barang',
            'Nama Barang & Spesifikasi',
            'Kategori',
            'Satuan',
            'Total Stok',
            'Status',
            'Keterangan Supplier',
            'Terakhir Diperbarui',
        ];

        $colWidths = [6, 14, 16, 36, 18, 12, 16, 14, 28, 18];

        $writer = new \App\Services\SimpleXlsxWriter();

        foreach ($grouped as $ym => $companiesData) {
            $parts = explode('-', $ym);
            $year = $parts[0] ?? date('Y');
            $mNum = isset($parts[1]) ? (int) $parts[1] : (int) date('m');
            $mLabel = ($monthNames[$mNum] ?? 'Bln') . ' ' . $year;

            foreach ($companiesData as $cCode => $rowsData) {
                // Natural ascending sort by item_code (e.g. KJG1, KJG2, ... / LNP1, LNP2, ...)
                usort($rowsData, function ($a, $b) {
                    return strnatcasecmp($a['item_code'] ?? '', $b['item_code'] ?? '');
                });

                $sheetTitle = "{$cCode} - {$mLabel}";
                $sheetRows = [];
                $no = 1;

                if (empty($rowsData)) {
                    $sheetRows[] = [
                        1,
                        $cCode,
                        '-',
                        'Tidak ada data saldo stok pada periode ini',
                        '-',
                        '-',
                        0,
                        '-',
                        '-',
                        '-',
                    ];
                } else {
                    foreach ($rowsData as $row) {
                        $sheetRows[] = [
                            $no++,
                            $row['company'],
                            $row['item_code'],
                            $row['item_name'],
                            $row['category'],
                            $row['unit'],
                            $row['qty'],
                            $row['status'],
                            $row['supplier'],
                            $row['last_updated'],
                        ];
                    }
                }

                $writer->addSheet($sheetTitle, $headers, $sheetRows, $colWidths);
            }
        }

        $filename = 'Laporan_Saldo_Stok_' . date('Ymd_His') . '.xlsx';
        $writer->download($filename);
    }

    public function movementsExportExcel(): void
    {
        if (!canViewReports()) {
            redirect('dashboard', 'Akses ditolak: Anda tidak memiliki izin untuk mengunduh mutasi stok.', 'danger');
        }

        $params = [
            'all' => 1,
            'search' => $_GET['search'] ?? '',
            'company_id' => $_GET['company_id'] ?? '',
            'movement_type' => $_GET['movement_type'] ?? '',
        ];

        $res = $this->api->get('stock/movements', array_filter($params, fn($v) => $v !== null && $v !== ''));
        $movements = $res['data'] ?? [];

        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $allCompanies = ['KJG' => 'PT Karunia Jaya Global', 'LNP' => 'PT Lestari Nusantara Perkasa'];

        $grouped = [];
        $monthsFound = [];
        foreach ($movements as $m) {
            $rawDate = (string) ($m['created_at'] ?? date('Y-m-d'));
            $ym = substr($rawDate, 0, 7);
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

        foreach ($movements as $m) {
            $rawDate = (string) ($m['created_at'] ?? date('Y-m-d'));
            $date = substr($rawDate, 0, 10);
            $ym = substr($date, 0, 7);
            $cCode = strtoupper($m['company']['code'] ?? 'KJG');
            if (!isset($allCompanies[$cCode])) {
                $allCompanies[$cCode] = $m['company']['name'] ?? $cCode;
            }
            if (!isset($grouped[$ym][$cCode])) {
                $grouped[$ym][$cCode] = [];
            }

            $item = $m['item'] ?? [];
            $grouped[$ym][$cCode][] = [
                'time' => substr($rawDate, 0, 19),
                'company' => $cCode,
                'type' => $m['movement_type'] ?? '-',
                'ref' => $m['reference_number'] ?? '-',
                'item_code' => $item['item_code'] ?? '-',
                'item_name' => $item['name'] ?? '-',
                'qty_change' => (float) ($m['qty'] ?? 0),
                'balance_after' => (float) ($m['balance_after'] ?? 0),
                'unit' => $item['unit']['code'] ?? $item['unit']['name'] ?? '-',
                'operator' => $m['user']['name'] ?? '-',
                'notes' => $m['notes'] ?? '-',
            ];
        }

        $headers = [
            'No',
            'Waktu Transaksi',
            'PT Pemilik',
            'Jenis Mutasi',
            'No. Referensi',
            'Kode Barang',
            'Nama Barang',
            'Perubahan Qty',
            'Saldo Akhir',
            'Satuan',
            'Operator',
            'Catatan',
        ];

        $colWidths = [6, 20, 14, 16, 22, 16, 32, 16, 14, 12, 18, 25];

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
                        $cCode,
                        '-',
                        '-',
                        '-',
                        'Tidak ada mutasi stok pada periode ini',
                        0,
                        0,
                        '-',
                        '-',
                        '-',
                    ];
                } else {
                    foreach ($rowsData as $row) {
                        $sheetRows[] = [
                            $no++,
                            $row['time'],
                            $row['company'],
                            $row['type'],
                            $row['ref'],
                            $row['item_code'],
                            $row['item_name'],
                            $row['qty_change'],
                            $row['balance_after'],
                            $row['unit'],
                            $row['operator'],
                            $row['notes'],
                        ];
                    }
                }

                $writer->addSheet($sheetTitle, $headers, $sheetRows, $colWidths);
            }
        }

        $filename = 'Laporan_Mutasi_Stok_' . date('Ymd_His') . '.xlsx';
        $writer->download($filename);
    }
}
