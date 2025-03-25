<?php 
require_once __DIR__ . '/../../vendor/autoload.php';
require_once "../utils/scraper.php";

use services\FundsHandler;
$fundsHandler = new FundsHandler();

// get data sent from ajax
$data = isset($_GET['funds']) ? $_GET['funds'] : '';

if (empty($data)) {
    echo "No funds provided";
    exit;
}

// break string out into each fund
$funds = explode(',', $data);

// initialize holdings
$allHoldings = [];

// loop through funds
foreach($funds as $fund) {
    $fund = trim($fund);
    
    if (empty($fund)) continue;
    
    // check if fund is in db
    $check = $fundsHandler->get_fund($fund);
    
    if (!$check) {
        // insert funds if not and then scrape 
        $fundsHandler->insert_fund($fund);
        
        // Get holdings from scraper and store them with fund name as key
        $fundHoldings = scrape($fund);
        if (!empty($fundHoldings)) {
            $allHoldings[$fund] = $fundHoldings;
        }
    } else {
        $fundHoldings = scrape($fund);
        if (!empty($fundHoldings)) {
            $allHoldings[$fund] = $fundHoldings;
        }
    }
}

if (empty($allHoldings)) {
    echo "No holdings found for the provided funds";
    exit;
}

// Start HTML table generation
echo '<table class="funds-table">';
echo '<thead><tr>
    <th>Fund</th>
    <th>Rank</th>
    <th>Ticker</th>
    <th>Company</th>
    <th>Percentage</th>
    <th>Shares</th>
</tr></thead><tbody>';

// track totals
$totalHoldings = 0;
$fundsProcessed = 0;

// Generate table rows
foreach ($allHoldings as $fund => $holdings) {
    $fund_id = $fundsHandler->get_fund($fund)['id'];
    $fundsProcessed++;
    
    foreach ($holdings as $holding) {
        // Insert holding into database
        $fundsHandler->insert_holding($fund_id, $holding);
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($fund) . '</td>';
        echo '<td>' . htmlspecialchars($holding['rank']) . '</td>';
        echo '<td>' . htmlspecialchars($holding['ticker']) . '</td>';
        echo '<td>' . htmlspecialchars($holding['company']) . '</td>';
        echo '<td>' . htmlspecialchars($holding['percentage']) . '</td>';
        echo '<td>' . htmlspecialchars($holding['shares']) . '</td>';
        echo '</tr>';
    }
    
    $totalHoldings += count($holdings);
}

echo '</tbody></table>';

// Generate summary as a separate table or div
echo '<div class="summary">';
echo '<p>Total funds processed: ' . $fundsProcessed . '</p>';
echo '<p>Total holdings: ' . $totalHoldings . '</p>';
echo '</div>';
?>
