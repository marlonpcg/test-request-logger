<?php

    namespace TestRequestLogger;
    interface LogWriterInterface
    {
        public function write(array $data): void;
    }
