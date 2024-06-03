<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function import()
    {
        // Load the Excel file
        $filePath = storage_path('app/users.xlsx');
        $data = Excel::toArray([], $filePath);

        // Extract the relevant sheet
        $sheetData = $data[0]; // Assuming data is on the first sheet

        // Chunk size
        $chunkSize = 100;

        // Skip header row
        $headerRow = array_shift($sheetData);

        // Calculate chunks
        $chunks = array_chunk($sheetData, $chunkSize);

        // Process each chunk
        foreach ($chunks as $chunk) {
            // Start transaction
            DB::beginTransaction();

            try {
                $bulkInsertData = [];

                foreach ($chunk as $row) {
                    // Check if the row has enough columns
                    if (count($row) >= 3) {
                        // Prepare data for bulk insert
                        $bulkInsertData[] = [
                            'segment' => $row[0], // Assuming the first column is 'Segment'
                            'country' => $row[1], // Assuming the second column is 'Country'
                            'product' => $row[2], // Assuming the third column is 'Product'
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                // Perform bulk insert
                Product::insert($bulkInsertData);
                echo "connection established"."<br>";

                // Commit transaction
                DB::commit();

                // Print success message for the chunk
                \Log::info('Chunk of ' . count($bulkInsertData) . ' rows imported successfully.');
            } catch (\Exception $e) {
                // Rollback transaction on error
                DB::rollback();

                // Log or handle the error as needed
                \Log::error('Error importing chunk: ' . $e->getMessage());
            }
            echo "chanked data loaded successfully"."<br>";
        }

        // Return a success message
        return 'Data imported successfully.';
    }
}
