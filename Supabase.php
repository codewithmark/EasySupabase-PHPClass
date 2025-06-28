<?php
class Supabase {
    private $baseUrl;
    private $apiKey;
    private $headers;

    public function __construct($url, $apiKey, $token = null) {
        $this->baseUrl = rtrim($url, '/') . '/rest/v1/';
        $this->apiKey = $apiKey;

        $authHeader = $token ? "Authorization: Bearer {$token}" : "Authorization: Bearer {$apiKey}";

        $this->headers = [
            "apikey: {$this->apiKey}",
            $authHeader,
            "Content-Type: application/json"
        ];
    }

    private function request($method, $endpoint, $data = null, $queryString = '') {
        $url = $this->baseUrl . $endpoint . $queryString;
        $opts = [
            "http" => [
                "method" => strtoupper($method),
                "header" => implode("\r\n", $this->headers),
                "ignore_errors" => true
            ]
        ];
        if ($data !== null) {
            $opts["http"]["content"] = json_encode($data);
        }
        $context = stream_context_create($opts);
        $response = file_get_contents($url, false, $context);
        return json_decode($response, true);
    }

    public function select($table, $columns = '*', $filters = [], $extras = []) {
        $params = ["select=" . urlencode($columns)];

        foreach ($filters as $col => $condition) {
            foreach ($condition as $op => $val) {
                if ($op === 'in' && is_array($val)) {
                    $val = '(' . implode(',', $val) . ')';
                }
                $params[] = "{$col}=" . urlencode("{$op}.{$val}");
            }
        }

        if (isset($extras['order'])) {
            $params[] = 'order=' . urlencode($extras['order']);
            unset($extras['order']);
        }

        foreach ($extras as $key => $value) {
            $params[] = "{$key}=" . urlencode($value);
        }

        return $this->request('GET', $table, null, '?' . implode('&', $params));
    }

    public function insert($table, $data) {
        $isBulk = is_array($data) && isset($data[0]) && is_array($data[0]);
        $headers = $this->headers;

        if ($isBulk) {
            $headers[] = 'Prefer: return=representation';
        }

        $opts = [
            "http" => [
                "method" => "POST",
                "header" => implode("\r\n", $headers),
                "content" => json_encode($data),
                "ignore_errors" => true
            ]
        ];

        $url = $this->baseUrl . $table;
        $context = stream_context_create($opts);
        $response = file_get_contents($url, false, $context);
        return json_decode($response, true);
    }

    public function bulkInsert($table, $rows, $chunkSize = 500) {
        $result = [];
        $chunks = array_chunk($rows, $chunkSize);
        foreach ($chunks as $chunk) {
            $response = $this->insert($table, $chunk);
            if (is_array($response)) {
                $result = array_merge($result, $response);
            }
        }
        return $result;
    }

    public function update($table, $match, $data) {
        $query = '?' . http_build_query($this->buildFilter($match));
        $this->headers[] = 'Prefer: return=representation';
        return $this->request('PATCH', $table . $query, $data);
    }

    public function delete($table, $match) {
        $query = '?' . http_build_query($this->buildFilter($match));
        return $this->request('DELETE', $table . $query);
    }

    private function buildFilter($match) {
        $filters = [];
        foreach ($match as $col => $val) {
            $filters[$col] = "eq.{$val}";
        }
        return $filters;
    }

    public function exists($table, $column, $value) {
        $result = $this->select($table, $column, [
            $column => ['eq' => $value]
        ], ['limit' => 1]);

        return !empty($result);
    }

    public function count($table, $filters = []) {
        $result = $this->select($table, 'id', $filters);
        return is_array($result) ? count($result) : 0;
    }

    public function findOne($table, $filters = [], $columns = '*') {
        $result = $this->select($table, $columns, $filters, ['limit' => 1]);
        return !empty($result) ? $result[0] : null;
    }

    public function findOrCreate($table, $filters, $data = null) {
        $found = $this->findOne($table, $filters);
        if ($found) {
            return $found;
        }

        $insertData = $data ?? $filters;
        $created = $this->insert($table, $insertData);
        return is_array($created) && isset($created[0]) ? $created[0] : null;
    }

    public function updateOrCreate($table, $matchConditions, $data) {
        $filters = [];
        foreach ($matchConditions as $col => $val) {
            $filters[$col] = ['eq' => $val];
        }

        $existing = $this->findOne($table, $filters);

        if ($existing) {
            $updated = $this->update($table, $matchConditions, $data);
            return is_array($updated) && isset($updated[0]) ? $updated[0] : null;
        } else {
            $insertData = array_merge($matchConditions, $data);
            $created = $this->insert($table, $insertData);
            return is_array($created) && isset($created[0]) ? $created[0] : null;
        }
    }
}
?>
