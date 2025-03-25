<?php 
require_once __DIR__ . '/../../vendor/autoload.php';
require_once "../utils/scraper.php";

use services\FundsHandler;
$fundsHandler = new FundsHandler();

// Debug function to log detailed information
function debugLog($message) {
    error_log($message);
}

// get data sent from ajax
$funds = isset($_GET['funds']) ? $_GET['funds'] : '';
$investmentsInput = isset($_GET['investments']) ? $_GET['investments'] : '';

debugLog("Received Funds: " . $funds);
debugLog("Received Investments: " . $investmentsInput);

if (empty($funds)) {
    echo "<p>No funds provided</p>";
    exit;
}

// break string out into each fund and investment
$fundArray = array_map('trim', explode(',', $funds));
$investmentArray = array_map(function($investment) {
    // Remove commas and convert to float
    return floatval(str_replace(',', '', $investment));
}, explode(',', $investmentsInput));

debugLog("Fund Array: " . print_r($fundArray, true));
debugLog("Investment Array: " . print_r($investmentArray, true));

// Validate input
if (count($fundArray) !== count($investmentArray)) {
    echo "<p>Number of funds must match number of investments</p>";
    exit;
}

// Combine funds and investments into an associative array
$fundsInvestments = array_combine($fundArray, $investmentArray);

debugLog("Funds and Investments: " . print_r($fundsInvestments, true));

// initialize holdings
$allHoldings = [];

// loop through funds
foreach($fundsInvestments as $fund => $investment) {
    // Ensure investment is a valid number
    $investment = max(0, floatval($investment));
    
    // check if fund is in db
    $check = $fundsHandler->get_fund($fund);
    
    if (!$check) {
        // insert funds if not and then scrape 
        $fundsHandler->insert_fund($fund);
        
        // Get holdings from scraper and store them with fund name as key
        $fundHoldings = scrape($fund);
        if (!empty($fundHoldings)) {
            $allHoldings[$fund] = [
                'holdings' => $fundHoldings,
                'investment' => $investment
            ];
        }
    } else {
        // If fund exists, get holdings
        $fundHoldings = scrape($fund);
        if (!empty($fundHoldings)) {
            $allHoldings[$fund] = [
                'holdings' => $fundHoldings,
                'investment' => $investment
            ];
        }
    }
}

if (empty($allHoldings)) {
    echo "<p>No holdings found for the provided funds</p>";
    exit;
}

// Start HTML table generation
echo '<div class="results-container">';
echo '<table class="funds-table">';
echo '<thead><tr>
    <th>Fund</th>
    <th>Investment</th>
    <th>Rank</th>
    <th>Ticker</th>
    <th>Company</th>
    <th>Fund Percentage</th>
    <th>Total Fund Shares</th>
    <th>Your Shares</th>
    <th>Your Value</th>
</tr></thead><tbody>';

// track totals
$totalInvestmentValue = 0;
$totalFundsProcessed = 0;

// Generate table rows
foreach ($allHoldings as $fund => $fundData) {
    $holdings = $fundData['holdings'];
    $investment = $fundData['investment'];
    
    $fund_id = $fundsHandler->get_fund($fund)['id'];
    $totalFundsProcessed++;
    
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
        
        // Insert holding into database
        $fundsHandler->insert_holding($fund_id, $holding);
        
        // Accumulate total investment value
        $totalInvestmentValue += $sharesValue;
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($fund) . '</td>';
        echo '<td>$' . number_format($investment, 2) . '</td>';
        echo '<td>' . htmlspecialchars($holding['rank'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($holding['ticker'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($holding['company'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($holding['percentage'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($holding['shares'] ?? 'N/A') . '</td>';
        echo '<td>' . number_format($userShares, 2) . '</td>';
        echo '<td>$' . number_format($sharesValue, 2) . '</td>';
        echo '</tr>';
    }
}

echo '</tbody></table>';

// Generate summary
echo '<div class="summary">';
echo '<p>Total Funds Processed: ' . $totalFundsProcessed . '</p>';
echo '<p>Total Investment Value: $' . number_format($totalInvestmentValue, 2) . '</p>';
echo '</div>';
echo '</div>';
?>>
