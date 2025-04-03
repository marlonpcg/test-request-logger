<?php

    namespace TestRequestLogger;

    require_once 'LogWriterInterface.php';
    interface FinalizableLogWriterInterface extends LogWriterInterface
    {
        public function finalize(): void;
    }
