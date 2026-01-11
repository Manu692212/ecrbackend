<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Get all data from academic_councils table
    $data = DB::table('academic_councils')->get();
    
    // Open file for writing
    $file = fopen('academic_council.csv', 'w');
    
    if ($data->isEmpty()) {
        // Get column names from table schema
        $columns = DB::select("DESCRIBE academic_councils");
        $headers = [];
        foreach ($columns as $column) {
            $headers[] = $column->Field;
        }
        fputcsv($file, $headers);
        echo "Created academic_council.csv with headers only (no data in table).\n";
    } else {
        // Get column headers from first row
        $headers = array_keys((array) $data->first());
        fputcsv($file, $headers);
        
        // Write data rows
        foreach ($data as $row) {
            fputcsv($file, (array) $row);
        }
        echo "Successfully exported " . $data->count() . " rows to academic_council.csv\n";
    }
    
    fclose($file);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
