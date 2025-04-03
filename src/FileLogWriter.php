<?php

    namespace TestRequestLogger;    
    
    require_once 'LogWriterInterface.php';

    class FileLogWriter implements LogWriterInterface
    {
        protected  string $logFile;
    
        public function __construct(string $filePath)
        {
            $this->logFile = $filePath;
    
            if (!file_exists($filePath)) {
                file_put_contents($this->logFile, "[\n");
            } else {
                $contents = file_get_contents($this->logFile);
                $contents = rtrim($contents);
                if (str_ends_with($contents, ']')) {
                    $contents = rtrim(substr($contents, 0, -1), ", \n");
                    file_put_contents($this->logFile, $contents);
                }
            }
        }
    
        public function write(array $data): void
        {
            $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
            $existing = file_get_contents($this->logFile);
            
            if (preg_match('/\\{/', $existing)) {
                file_put_contents($this->logFile, ",\n$encoded", FILE_APPEND);
            } else {
                file_put_contents($this->logFile, "$encoded", FILE_APPEND);
            }
        }
    
        public function finalize(): void
        {
            file_put_contents($this->logFile, "\n]", FILE_APPEND);
        }
    }
    