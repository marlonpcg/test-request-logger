# TestRequestLogger

**TestRequestLogger** is a lightweight, pluggable error logging system for PHP applications. It captures PHP errors (including fatal, warning, notice, and custom errors) during web requests and logs them in structured JSON format. Ideal for testing and debugging, especially in automated test suites.

---

## 🧠 Motivation

This tool was created to support **projects that are still under construction**, where the codebase is frequently changing and errors may occur unpredictably. It is especially useful for projects that **have not yet reached the maturity level required for final QA testing**.

In these early stages of development, where multiple developers are actively working and contributing code, unnoticed PHP errors such as warnings, notices, or fatals may be generated during web **requests**. 

**TestRequestLogger** provides an automatic and structured way to detect these errors early by logging them per request and grouping them by test ID. It helps teams quickly identify and review problems before formal QA begins.

> It was built to help answer a simple question:
>
> 🔍 *“Did this request execute cleanly, without any PHP errors?”*

---

## 🚀 Features

- 🔍 Captures all major PHP error types:
  - `Fatal`, `Warning`, `Notice`, `Strict`, `Deprecated`, `User-defined`
- 🧠 Grouped by **TestRequestLoggerId** (from header, GET or POST)
- 🗂 One structured JSON log **per request**
- 📁 Output directory organized by test ID and timestamp
- 🧩 Fully extensible:
  - Plug your own writers (e.g., file, database, remote API)
- ✅ Option to log all errors or only specific types (e.g., only `fatal`)
- 💡 Automatically appends metadata:
  - `timestamp`, `IP`, `method`, `error_code`, `error_type`, etc.
- 💾 Generates **valid JSON arrays**, ready for parsing or integration

---

## 📦 Installation

### ✅ Option 1: Install via Composer (Recommended)

```bash
composer require marlonpcg/test-request-logger
```

### ✅ Option 2: Clone from GitHub (for development or testing)

```bash
git clone https://github.com/marlonpcg/test-request-logger.git
cd test-request-logger
composer install
```

---

## 🧩 Usage

### ▶️ Standard usage with Composer

```php
require_once __DIR__ . '/vendor/autoload.php';

use TestRequestLogger\TestRequestLoggerInit;

// Log all error types (default)
TestRequestLoggerInit::init();

// Or log only specific types
TestRequestLoggerInit::init(acceptedErrorTypes: 'fatal');
TestRequestLoggerInit::init(acceptedErrorTypes: ['warning', 'fatal']);
```

### 👷 Local development usage (without Composer)

```php
require_once __DIR__ . '/test-request-logger/src/TestRequestLoggerInit.php';

use TestRequestLogger\TestRequestLoggerInit;

TestRequestLoggerInit::init();
```

---

## ▶️ How to test via URL

You can test the logger by running a script (like `demo.php`) via browser, curl, or Postman and providing a test ID:

### Example 1: Browser / Curl

```bash
http://localhost/test-request-logger/demo.php?TestRequestLoggerId=1
```

This will generate a JSON file in the folder:

```
/output/1/demo_YYYYMMDD_HHMMSS.json
```

### Example 2: Using custom headers (e.g. Postman)

**Header:**
```
TestRequestLoggerId: 2
```

**Request URL:**
```
http://localhost/test-request-logger/demo.php
```

This allows automated tools to test multiple pages and track logs by test ID.

---

## 📝 Log Output

Each request creates a JSON file under:

```
/output/{TestRequestLoggerId}/{script-name}_{timestamp}.json
```

The file contains an array of JSON objects:

```json
[
  {
    "TestRequestLoggerId": "123",
    "request": "/demo.php",
    "timestamp": "2025-04-03 14:21:01",
    "method": "GET",
    "type": "error",
    "error": "8 - Undefined variable $x in demo.php:12",
    "error_type": "Notice",
    "error_code": 8
  },
  ...
]
```

---

## 🧪 Demo

Use the provided `demo.php` script:

```php
// Configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if TestRequestLoggerId was provided
$hasLoggerId =
    isset($_GET['TestRequestLoggerId']) ||
    isset($_POST['TestRequestLoggerId']) ||
    isset(getallheaders()['TestRequestLoggerId']);

if (!$hasLoggerId) {
    echo "<div style='color: red; font-weight: bold; font-family: monospace;'>
        ⚠️ Warning: Logging will not work. You must provide a TestRequestLoggerId via GET, POST or Header.
    </div>";
}

// Logger Integration
require_once 'test-request-logger/src/TestRequestLoggerInit.php';
use TestRequestLogger\TestRequestLoggerInit;
TestRequestLoggerInit::init(acceptedErrorTypes: 'all');

// Trigger test errors
session_start();
session_start();
fopen('not_found.txt', 'r');

try {
    throw new Exception("Test exception");
} catch (Exception $e) {
    trigger_error("Caught exception: " . $e->getMessage(), E_USER_WARNING);
}

trigger_error("Simulated fatal error", E_USER_ERROR);
```

---

## 🙋 Who is this for?

- Teams building PHP applications that are still under construction or early development stages
- Projects that haven't yet reached QA maturity but require a way to monitor runtime stability
- Developers working in a collaborative environment where code changes are frequent
- Test engineers and automation tools that need to track PHP errors across multiple requests
- Anyone who wants to ensure that every request executes without hidden PHP warnings, notices, or fatals

---

## 📄 License
MIT License

**Author**: Marlon Pinheiro Claro Gomes (<marlonpcg@gmail.com>)
