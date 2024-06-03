<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function import()
    {
        // Load the Excel file
        $filePath = storage_path('app\users.xlsx');
        $data = Excel::toArray([], $filePath);

        // Extract the relevant sheet
        $sheetData = $data[0]; // Assuming data is on the first sheet

        $chunkSize = 100;

        // Skip header row
        $headerRow = array_shift($sheetData);

        // Calculate chunks
        $chunks = array_chunk($sheetData, $chunkSize);

        // Process each chunk
        foreach ($chunks as $chunk) {
            // Start transaction
            \DB::beginTransaction();

            try {
                foreach ($chunk as $row) {
                    // Check if the row has enough columns
                    if (count($row) >= 3) {
                        // Create or update product
                        $product = Product::create([
                            'segment' => $row[0], // Assuming the first column is 'Segment'
                            'country' => $row[1], // Assuming the second column is 'Country'
                            'product' => $row[2], // Assuming the third column is 'Product'
                        ]);
                        echo "connection established";

                    }
                }
                
                // Commit transaction
                \DB::commit();
                
                // Print success message for the chunk
                \Log::info('Chunk of ' . count($chunk) . ' rows imported successfully.');
                
            } catch (\Exception $e) {
                // Rollback transaction on error
                \DB::rollback();

                // Log or handle the error as needed
                \Log::error('Error importing chunk: ' . $e->getMessage());
            }
            echo "chanked data loaded successfully"."<br>";
        }

        // Print final success message
        \Log::info('All data imported successfully.');

        // Return a success message
        return 'Data imported successfully.';
    }
}
