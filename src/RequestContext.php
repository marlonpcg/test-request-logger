<?php

    namespace TestRequestLogger;
    class RequestContext
    {
        public ?string $testId;
        public string $requestUri;
        public string $method;
        public string $ip;
        public float $startTime;

        public function __construct()
        {
            $this->testId = $this->resolveTestId();
            $this->requestUri = $_SERVER['REQUEST_URI'] ?? '/';
            $this->method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
            $this->ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
            $this->startTime = microtime(true);
        }

        private function resolveTestId(): ?string
        {
            $id = $_SERVER['HTTP_X_TESTREQUESTLOGGERID'] ?? $_GET['TestRequestLoggerId'] ?? $_POST['TestRequestLoggerId'] ?? null;
            return $id ? $this->sanitizeTestId($id) : null;
        }

        private function sanitizeTestId(string $id): string
        {
            return preg_replace('/[^a-zA-Z0-9_\-]/', '', $id);
        }

        public function getSafeFileName(): string
        {
            $path = parse_url($this->requestUri, PHP_URL_PATH) ?? '';
            $query = $_SERVER['QUERY_STRING'] ?? '';
            $fullPath = $path . ($query ? '?' . $query : '');
            $name = trim($fullPath, '/') ?: 'index';
            return str_replace(['/', '?', '&', '=', '.php'], '_', $name);
        }

        public function getElapsedTime(): float
        {
            return round((microtime(true) - $this->startTime) * 1000, 2);
        }
    }
