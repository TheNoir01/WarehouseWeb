<?php

namespace App\Services;

class ApiService
{
    protected string $baseUrl;
    protected ?string $token;

    public function __construct(?string $token = null)
    {
        $this->baseUrl = rtrim(API_BASE_URL, '/');
        $this->token = $token ?? apiToken();
    }

    public function request(string $method, string $endpoint, array $data = [], array $files = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $ch = curl_init();

        $headers = [
            'Accept: application/json',
        ];

        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        $method = strtoupper($method);

        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($files)) {
                // Multipart form data
                $postData = $data;
                foreach ($files as $field => $file) {
                    if (is_array($file) && isset($file['tmp_name']) && file_exists($file['tmp_name'])) {
                        $postData[$field] = new \CURLFile($file['tmp_name'], $file['type'], $file['name']);
                    }
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            } else {
                // JSON payload
                $json = json_encode($data);
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Content-Length: ' . strlen($json);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            }
        } elseif ($method === 'PUT' || $method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            $json = json_encode($data);
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($json);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke API backend (' . $curlError . '). Pastikan service Laravel sedang aktif.',
                'errors' => null,
                'status' => 503,
            ];
        }

        $decoded = json_decode($response, true);
        if ($decoded === null) {
            return [
                'success' => false,
                'message' => 'Respon dari server tidak valid (' . substr(strip_tags($response), 0, 100) . ')',
                'status' => $httpCode,
            ];
        }

        $decoded['status'] = $httpCode;

        // Auto handle token expired
        if ($httpCode === 401 && !empty($_SESSION['token'])) {
            unset($_SESSION['token'], $_SESSION['user']);
            redirect('login', 'Sesi Anda telah berakhir. Silakan login kembali.', 'warning');
        }

        return $decoded;
    }

    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, $params);
    }

    public function post(string $endpoint, array $data = [], array $files = []): array
    {
        return $this->request('POST', $endpoint, $data, $files);
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, $data);
    }

    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }
}
