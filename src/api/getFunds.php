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
        // If fund exists, you might want to get its data from the database
        // This part depends on your FundsHandler implementation
        // For now, we'll just scrape it again to ensure the script works
        // TODO: pull from db
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

// print header
echo str_pad("Fund", 10) . " | ";
echo str_pad("Rank", 5) . " | ";
echo str_pad("Ticker", 8) . " | ";
echo str_pad("Company", 40) . " | ";
echo str_pad("Percentage", 10) . " | ";
echo "Shares" . PHP_EOL;

echo str_repeat("-", 90) . PHP_EOL;

// print each holding formatted in table
$totalHoldings = 0;

//TODO: insert holdings into db
foreach ($allHoldings as $fund => $holdings) {
    foreach ($holdings as $holding) {
        echo str_pad($fund, 10) . " | ";
        echo str_pad($holding['rank'], 5) . " | ";
        echo str_pad($holding['ticker'], 8) . " | ";
        echo str_pad(substr($holding['company'], 0, 38), 40) . " | ";
        echo str_pad($holding['percentage'], 10) . " | ";
        echo $holding['shares'] . PHP_EOL;
    }
    $totalHoldings += count($holdings);
}

// print summary
echo PHP_EOL . "Total funds processed: " . count($allHoldings) . PHP_EOL;
echo "Total holdings: " . $totalHoldings . PHP_EOL;
