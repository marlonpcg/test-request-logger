<?php

    namespace TestRequestLogger;
    
    require_once 'LogWriterInterface.php';
    require_once 'ArrayJsonLogWriter.php';
    require_once 'RequestContext.php';
    require_once 'TestRequestLogger.php';
    
    class TestRequestLoggerInit
    {
        private static bool $initialized = false;
    
        /**
         * @param string $baseLogDir Directory where logs will be stored.
         * @param string|array $acceptedErrorTypes 'all' or a list like 'fatal', 'warning', 'notice', 'strict', 'deprecated', 'unknown'.
         */
        public static function init(string $baseLogDir = __DIR__ . '/../output/', string|array $acceptedErrorTypes = 'all'): void
        {
            if (self::$initialized) {
                throw new \LogicException("TestRequestLogger has already been initialized.");
            }
            self::$initialized = true;
    
            $context = new RequestContext();
    
            if ($context->testId === null) {
                return;
            }
    
            $testDir = rtrim($baseLogDir, '/') . '/' . $context->testId;
            if (!is_dir($testDir) && !mkdir($testDir, 0777, true) && !is_dir($testDir)) {
                throw new \RuntimeException("Cannot create log directory: $testDir");
            }
    
            $timestamp = date('Ymd_His');
            $logFilePath = $testDir . '/' . $context->getSafeFileName() . "_{$timestamp}.json";
    
            $writer = new ArrayJsonLogWriter($logFilePath);
            new TestRequestLogger($writer, $context, $acceptedErrorTypes);
    
            if ($writer instanceof FinalizableLogWriterInterface) {
                register_shutdown_function(fn() => $writer->finalize());
            }
        }
    }
    