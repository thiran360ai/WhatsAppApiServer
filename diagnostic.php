<?php

// Save this as diagnostic.php in your project root

// Function to recursively search for text in files
function findTextInFiles($directory, $searchText, $fileExtensions = ['php'], $exclusions = ['vendor', 'node_modules', 'storage']) {
    $results = [];
    
    // Get all files in the directory
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($files as $file) {
        // Skip excluded directories
        $relativePath = str_replace($directory . DIRECTORY_SEPARATOR, '', $file->getPathname());
        $shouldExclude = false;
        
        foreach ($exclusions as $exclusion) {
            if (strpos($relativePath, $exclusion . DIRECTORY_SEPARATOR) === 0) {
                $shouldExclude = true;
                break;
            }
        }
        
        if ($shouldExclude) {
            continue;
        }
        
        // Check if file has the desired extension
        $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
        if (!in_array($extension, $fileExtensions)) {
            continue;
        }
        
        // Search for text in file
        $content = file_get_contents($file->getPathname());
        if (stripos($content, $searchText) !== false) {
            $results[] = [
                'file' => $relativePath,
                'line_numbers' => findLineNumbers($content, $searchText)
            ];
        }
    }
    
    return $results;
}

// Function to find line numbers for occurrences
function findLineNumbers($content, $searchText) {
    $lines = explode("\n", $content);
    $lineNumbers = [];
    
    foreach ($lines as $number => $line) {
        if (stripos($line, $searchText) !== false) {
            $lineNumbers[] = $number + 1;
        }
    }
    
    return $lineNumbers;
}

// Check environment variables
echo "Checking .env file for 'mysqls':\n";
$envContent = file_get_contents('.env');
if (stripos($envContent, 'mysqls') !== false) {
    echo "Found 'mysqls' in .env file!\n";
    $lines = explode("\n", $envContent);
    foreach ($lines as $number => $line) {
        if (stripos($line, 'mysqls') !== false) {
            echo "Line " . ($number + 1) . ": " . $line . "\n";
        }
    }
} else {
    echo "No occurrences of 'mysqls' found in .env file.\n";
}

// Check cache files
echo "\nChecking cached configuration files:\n";
$bootstrapCachePath = __DIR__ . '/bootstrap/cache';
if (is_dir($bootstrapCachePath)) {
    $cacheFiles = glob($bootstrapCachePath . '/*.php');
    foreach ($cacheFiles as $file) {
        $content = file_get_contents($file);
        if (stripos($content, 'mysqls') !== false) {
            echo "Found 'mysqls' in " . basename($file) . "!\n";
        }
    }
} else {
    echo "Bootstrap cache directory not found.\n";
}

// Search in config directory
echo "\nSearching for 'mysqls' in configuration files:\n";
$configResults = findTextInFiles(__DIR__ . '/config', 'mysqls');
if (!empty($configResults)) {
    foreach ($configResults as $result) {
        echo "File: " . $result['file'] . "\n";
        echo "Line(s): " . implode(', ', $result['line_numbers']) . "\n";
    }
} else {
    echo "No occurrences of 'mysqls' found in config files.\n";
}

// Search in key application files
echo "\nSearching for 'mysqls' in key application files:\n";
$appResults = findTextInFiles(__DIR__, 'mysqls', ['php'], ['vendor', 'node_modules', 'storage', 'bootstrap/cache']);
if (!empty($appResults)) {
    foreach ($appResults as $result) {
        echo "File: " . $result['file'] . "\n";
        echo "Line(s): " . implode(', ', $result['line_numbers']) . "\n";
    }
} else {
    echo "No occurrences of 'mysqls' found in key application files.\n";
}

// Check in cached config
if (file_exists(__DIR__ . '/bootstrap/cache/config.php')) {
    echo "\nInspecting cached config.php file:\n";
    
    $cachedConfig = require __DIR__ . '/bootstrap/cache/config.php';
    
    // Check database connections
    if (isset($cachedConfig['database']['connections'])) {
        echo "Database connections in cached config:\n";
        print_r(array_keys($cachedConfig['database']['connections']));
    }
    
    // Check session configuration
    if (isset($cachedConfig['session'])) {
        echo "\nSession configuration in cached config:\n";
        echo "Driver: " . $cachedConfig['session']['driver'] . "\n";
        echo "Connection: " . ($cachedConfig['session']['connection'] ?? 'null') . "\n";
    }
}

echo "\nDiagnostic complete.\n";