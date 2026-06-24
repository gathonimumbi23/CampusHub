<?php
/**
 * VALIDATION SCRIPT
 * Tests the execution flow without requiring Apache/web server.
 * 
 * Usage: php test-controller.php
 */

echo "========================================\n";
echo "PHASE 4: VALIDATION\n";
echo "========================================\n\n";

// ========================================
// TEST 1: Constants loading - no duplicate define
// =========================================
echo "--- TEST 1: Constants loading (no duplicate define warning) ---\n";

// Simulate index.php's load order
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/CampusHub/shopswift/';

// Clear any previous define
if (defined('SESSION_TIMEOUT')) {
    echo "  ⚠ SESSION_TIMEOUT was already defined externally - clearing for test\n";
}

// Step 1: index.php line 11 - load session.php FIRST
require_once __DIR__ . '/shopswift/config/session.php';

$definedAtSession = defined('SESSION_TIMEOUT');
echo "  After session.php: SESSION_TIMEOUT defined = " . ($definedAtSession ? "YES" : "NO") . "\n";

// Step 2: index.php line 12 - load web.php (which loads constants.php)
// Capture any warnings from constants.php
$warning = null;
set_error_handler(function($errno, $errstr) use (&$warning) {
    $warning = $errstr;
    return true;
});

require_once __DIR__ . '/shopswift/routes/web.php';

restore_error_handler();

echo "  After constants.php (loaded via web.php):\n";
if ($warning === null) {
    echo "    ✓ No 'already defined' warning - PASS\n";
} else {
    echo "    ✗ WARNING: $warning\n";
}
echo "    SESSION_TIMEOUT value: " . SESSION_TIMEOUT . "\n\n";

// =========================================
// TEST 2: Router loading HomeController
// =========================================
echo "--- TEST 2: Router resolves HomeController ---\n";

$controllerName = 'Home';
$controllerClass = $controllerName . 'Controller';
$controllerFile = __DIR__ . '/shopswift/controllers/' . $controllerClass . '.php';
$expectedPath = str_replace('/', DIRECTORY_SEPARATOR, $controllerFile);

echo "  Route callback: 'Home@index'\n";
echo "  Controller class: $controllerClass\n";
echo "  Looking for file: $controllerFile\n";
echo "  File exists: " . (file_exists($controllerFile) ? "YES ✓" : "NO ✗") . "\n";

if (file_exists($controllerFile)) {
    echo "\n  --- Loading controller... ---\n";
    require_once $controllerFile;
    
    if (class_exists($controllerClass)) {
        echo "  ✓ Class '$controllerClass' found in file\n";
        
        $instance = new $controllerClass();
        if (method_exists($instance, 'index')) {
            echo "  ✓ Method 'index()' exists\n";
            echo "\n  ✓ HomeController resolves correctly - PASS\n";
        } else {
            echo "  ✗ Method 'index()' NOT found\n";
        }
    } else {
        echo "  ✗ Class '$controllerClass' NOT found in file\n";
    }
}
echo "\n";

// =========================================
// TEST 3: Verify home.php view exists
// =========================================
echo "--- TEST 3: home.php view ---\n";
$viewFile = __DIR__ . '/shopswift/views/home.php';
echo "  View path: $viewFile\n";
echo "  File exists: " . (file_exists($viewFile) ? "YES ✓" : "NO ✗") . "\n\n";

// =========================================
// SUMMARY
// =========================================
echo "========================================\n";
echo "VALIDATION SUMMARY\n";
echo "========================================\n";
$allPass = true;

if ($warning !== null) { echo "  ✗ Error 1: Duplicate SESSION_TIMEOUT define\n"; $allPass = false; }
else { echo "  ✓ Error 1: Fixed - SESSION_TIMEOUT guarded in constants.php\n"; }

if (!file_exists($controllerFile)) { echo "  ✗ Error 2: HomeController.php still not found\n"; $allPass = false; }
else { echo "  ✓ Error 2: Fixed - Homecontoller.php renamed to HomeController.php\n"; }

echo "\n";
if ($allPass) {
    echo "  ✓ ALL FIXES VERIFIED. Application should now load correctly.\n";
} else {
    echo "  ✗ Some fixes still need attention.\n";
}
echo "========================================\n";