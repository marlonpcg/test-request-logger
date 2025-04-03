<?php

    namespace TestRequestLogger;

    require_once 'FileLogWriter.php';
    require_once 'FinalizableLogWriterInterface.php';
    
    class ArrayJsonLogWriter extends FileLogWriter implements FinalizableLogWriterInterface
    {
        private bool $firstWrite = true;
        private bool $hasWritten = false;
    
        public function __construct(string $filePath)
        {
            parent::__construct($filePath);
            // Do not write anything yet; defer until first write
        }
    
        public function write(array $data): void
        {
            if (!$this->hasWritten) {
                file_put_contents($this->logFile, "[\n", FILE_APPEND);
            }
    
            if (isset($data['error'])) {
                $data['error_type'] = $this->extractErrorType($data['error']);
                $data['error_code'] = $this->extractErrorCode($data['error']);
            }
    
            $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if (!$this->firstWrite) {
                file_put_contents($this->logFile, ",\n" . $encoded, FILE_APPEND);
            } else {
                file_put_contents($this->logFile, $encoded, FILE_APPEND);
                $this->firstWrite = false;
            }
    
            $this->hasWritten = true;
        }
    
        public function finalize(): void
        {
            if ($this->hasWritten) {
                file_put_contents($this->logFile, "\n]", FILE_APPEND);
            } elseif (file_exists($this->logFile)) {
                unlink($this->logFile); 
            }
        }
    
        private function extractErrorType(string $message): string
        {
            $code = $this->extractErrorCode($message);
            return match ($code) {
                E_ERROR, E_USER_ERROR       => 'Fatal Error',
                E_WARNING, E_USER_WARNING   => 'Warning',
                E_NOTICE, E_USER_NOTICE     => 'Notice',
                E_STRICT                    => 'Strict',
                E_DEPRECATED, E_USER_DEPRECATED => 'Deprecated',
                default => 'Unknown'
            };
        }
    
        private function extractErrorCode(string $message): int
        {
            if (preg_match('/^([0-9]+)/', $message, $matches)) {
                return (int)$matches[1];
            }
            return 0;
        }
    }
    