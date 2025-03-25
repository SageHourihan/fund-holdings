<?php
namespace services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelExportService {
    public function exportFundHoldingsToExcel($allHoldings) {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set column headers
        $headers = [
            'Fund', 
            'Investment', 
            'Rank', 
            'Ticker', 
            'Company', 
            'Fund Percentage', 
            'Total Fund Shares', 
            'Your Shares', 
            'Your Value'
        ];

        // Write headers
        foreach ($headers as $col => $header) {
            $cell = Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet->setCellValue($cell, $header);
        }

        // Track total investment value
        $totalInvestmentValue = 0;
        $rowIndex = 2; // Start from second row (after headers)

        // Populate data
        foreach ($allHoldings as $fund => $fundData) {
            $holdings = $fundData['holdings'];
            $investment = $fundData['investment'];

            foreach ($holdings as $holding) {
                // Safely parse percentage
                $percentageDecimal = 0;
                if (isset($holding['percentage']) && is_string($holding['percentage'])) {
                    $percentageDecimal = floatval(preg_replace('/[^0-9.]/', '', $holding['percentage'])) / 100;
                }

                // Safely parse total shares
                $totalSharesInFund = 0;
                if (isset($holding['shares']) && is_string($holding['shares'])) {
                    $totalSharesInFund = floatval(preg_replace('/[^0-9.]/', '', $holding['shares']));
                }

                // Calculate shares owned based on percentage and investment
                $userShares = round(max(0, $percentageDecimal * $totalSharesInFund), 2);

                // Calculate value of shares (using percentage of investment)
                $sharesValue = round(max(0, $percentageDecimal * $investment), 2);
                
                // Accumulate total investment value
                $totalInvestmentValue += $sharesValue;

                // Populate row using column letters
                $sheet->setCellValue('A' . $rowIndex, $fund);
                $sheet->setCellValue('B' . $rowIndex, $investment);
                $sheet->setCellValue('C' . $rowIndex, $holding['rank'] ?? 'N/A');
                $sheet->setCellValue('D' . $rowIndex, $holding['ticker'] ?? 'N/A');
                $sheet->setCellValue('E' . $rowIndex, $holding['company'] ?? 'N/A');
                $sheet->setCellValue('F' . $rowIndex, $holding['percentage'] ?? 'N/A');
                $sheet->setCellValue('G' . $rowIndex, $holding['shares'] ?? 'N/A');
                $sheet->setCellValue('H' . $rowIndex, $userShares);
                $sheet->setCellValue('I' . $rowIndex, $sharesValue);

                $rowIndex++;
            }
        }

        // Add total investment value to the bottom
        $sheet->setCellValue('A' . $rowIndex, 'Total Investment Value');
        $sheet->setCellValue('I' . $rowIndex, $totalInvestmentValue);

        // Auto-size columns
        for ($col = 'A'; $col <= 'I'; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Style the headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ]
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

        // Prepare the file
        $writer = new Xlsx($spreadsheet);
        $filename = 'fund_holdings_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // Save to a temporary file
        $temp_file = tempnam(sys_get_temp_dir(), 'fund_holdings_');
        $writer->save($temp_file);

        // Return the filename and temp file path
        return ['filename' => $filename, 'file' => $temp_file];
    }
}
