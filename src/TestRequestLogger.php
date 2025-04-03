<?php

    namespace TestRequestLogger;

    require_once 'LogWriterInterface.php';
    require_once 'RequestContext.php';
    
    class TestRequestLogger
    {
        private LogWriterInterface $writer;
        private RequestContext $context;
        private ?string $lastErrorMessage = null;
        private array $acceptedTypes = [];
    
        public function __construct(LogWriterInterface $writer, RequestContext $context, string|array $acceptedErrorTypes = 'all')
        {
            $this->writer = $writer;
            $this->context = $context;
    
            $this->acceptedTypes = $this->normalizeAcceptedTypes($acceptedErrorTypes);
    
            set_error_handler([$this, 'handleError']);
            register_shutdown_function([$this, 'handleShutdown']);
        }
    
        public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
        {
            $type = $this->resolveType($errno);
            if (!in_array($type, $this->acceptedTypes)) {
                return false;
            }
    
            $message = "$errno - $errstr in $errfile:$errline";
            $this->lastErrorMessage = $message;
    
            $this->writer->write([
                'TestRequestLoggerId' => $this->context->testId,
                'request' => $this->context->requestUri,
                'timestamp' => date('Y-m-d H:i:s'),
                'ip' => $this->context->ip,
                'method' => $this->context->method,
                'type' => 'error',
                'error' => $message,
                'error_code' => $errno,
                'error_type' => $type
            ]);
    
            return false;
        }
    
        public function handleShutdown(): void
        {
            $error = error_get_last();
            if ($error) {
                $type = $this->resolveType($error['type']);
                $message = "{$error['type']} - {$error['message']} in {$error['file']}:{$error['line']}";
    
                if ($this->lastErrorMessage !== $message && in_array($type, $this->acceptedTypes)) {
                    $this->writer->write([
                        'TestRequestLoggerId' => $this->context->testId,
                        'request' => $this->context->requestUri,
                        'timestamp' => date('Y-m-d H:i:s'),
                        'ip' => $this->context->ip,
                        'method' => $this->context->method,
                        'duration_ms' => $this->context->getElapsedTime(),
                        'type' => 'fatal',
                        'error' => $message,
                        'error_code' => $error['type'],
                        'error_type' => $type
                    ]);
                }
            }
        }
    
        private function resolveType(int $code): string
        {
            return match ($code) {
                E_ERROR, E_USER_ERROR       => 'fatal',
                E_WARNING, E_USER_WARNING   => 'warning',
                E_NOTICE, E_USER_NOTICE     => 'notice',
                E_STRICT                    => 'strict',
                E_DEPRECATED, E_USER_DEPRECATED => 'deprecated',
                default => 'unknown'
            };
        }
    
        private function normalizeAcceptedTypes(string|array $types): array
        {
            if (is_array($types) && in_array('all', $types)) {
                throw new \InvalidArgumentException("Use 'all' as a string, not as an array element.");
            }
        
            if ($types === 'all') {
                return ['fatal', 'warning', 'notice', 'strict', 'deprecated', 'unknown'];
            }
        
            if (is_string($types)) {
                return [strtolower($types)];
            }
        
            return array_map('strtolower', $types);
        }
    }
