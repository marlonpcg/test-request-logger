<?php

    /**
     * --------------------------------------------------------------
     * Configuration: enable or disable error display
     * --------------------------------------------------------------
     */

    // example with enabled error display 
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // example with disabled error display 
    // error_reporting(0);
    // ini_set('display_errors', 0);

    /**
     * --------------------------------------------------------------
     * Requirement: check if TestRequestLoggerId was provided
     * --------------------------------------------------------------
     */

    $hasLoggerId =
        isset($_GET['TestRequestLoggerId']) ||
        isset($_POST['TestRequestLoggerId']) ||
        isset(getallheaders()['TestRequestLoggerId']);

    if (!$hasLoggerId) {
        echo "<div style='color: red; font-weight: bold; font-family: monospace;'>
            ⚠️ Warning: Logging will not work. You must provide a TestRequestLoggerId via GET, POST or Header.
        </div>";
    }

    /**
     * --------------------------------------------------------------
     * Integration: initialize the logger
     * --------------------------------------------------------------
     */

    require_once 'src/TestRequestLoggerInit.php';

    use TestRequestLogger\TestRequestLoggerInit;

    TestRequestLoggerInit::init(acceptedErrorTypes: 'all');

    /**
     * --------------------------------------------------------------
     * Demo: trigger common PHP errors for testing
     * --------------------------------------------------------------
     */

    // Notice: session already started
    session_start();
    session_start();

    // Warning: file not found
    fopen('not_found.txt', 'r');

    // User Warning: simulate exception handling
    try {
        throw new Exception("Test exception");
    } catch (Exception $e) {
        trigger_error("Caught exception: " . $e->getMessage(), E_USER_WARNING);
    }

    // User Fatal Error
    trigger_error("Simulated fatal error", E_USER_ERROR);
