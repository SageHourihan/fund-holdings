<?php 
require_once __DIR__ . '/../../vendor/autoload.php';
use services\FundsHandler;
$fundsHandler = new FundsHandler();
// get data sent from ajax
$data = $_GET['funds'];

// break string out into each fund
$funds = explode(',', $data);

// initialize holdings
$holdings = [];

// loop through funds
foreach($funds as $fund){

    // insert fund
    $insert = $fundsHandler->insert_fund($fund);

    //url to scrape
    $url = "https://stockanalysis.com/quote/mutf/$fund/holdings/";

    // initialize curl session
    $ch = curl_init();

    // set options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KH TML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    // execute curl session and get html content
    $html = curl_exec($ch);

    //check for errors
    if(curl_errno($ch)){
        echo 'Error: ' . curl_error($ch) . PHP_EOL;
        exit(1);
    }

    // close curl
    curl_close($ch);
    
    // create dom object
    $dom = new DOMDocument();
    
    // load the html content
    @$dom->loadHTML($html);
    
    // create new dom xpath to query document
    $xpath = new DOMXPath($dom);
    
    // find all the table rows
    $rows = $xpath->query('//tr[contains(@class, "svelte-")]');
    
    // loop through rows
    foreach($rows as $row){
        // get all cells
        $cells = $row->getElementsByTagName('td');  
        
        // skip if not enough enough cells
        if($cells->length < 5) continue;
        
        // extract the ticker info
        $rank = trim($cells->item(0)->textContent);
        
        // check if ticker has a link
        $tickerCell = $cells->item(1);
        $ticker = trim($tickerCell->textContent);

        $tickerLink = $tickerCell->getElementsByTagName('a');
        $tickerUrl = "";
        if($tickerLink->length > 0){
            $tickerUrl = $tickerLink->item(0)->getAttribute('href');
        }
        
        $company = trim($cells->item(2)->textContent);
        $percentage = trim($cells->item(3)->textContent);

        // check if there is a shares column
        $shares = "";
        if($cells->length > 4){
            $shares = trim($cells->item(4)->textContent);
        }
    
        // add to holdings array
        $holdings[] = [
        'rank' => $rank,
        'ticker' => $ticker,
        'tickerUrl' => $tickerUrl,
        'company' => $company,
        'percentage' => $percentage,
        'shares' => $shares
        ];
        
    }
}

// print header
echo str_pad("Rank", 5) . " | |";
echo str_pad("Tickerk", 8) . " | ";
echo str_pad("Company", 40) . " | ";
echo str_pad("Percentage", 10) . " | ";
echo "Shares" . PHP_EOL;

echo str_repeat("-", 80) . PHP_EOL;

//print each holding formatted in table
foreach($holdings as $holding){
    echo str_pad($holding['rank'], 5) . " | ";
    echo str_pad($holding['ticker'], 8) . " | ";
    echo str_pad(substr($holding['company'], 0, 38), 40) . " | ";
    echo str_pad($holding['percentage'], 10) . " | ";
    echo $holding['shares'] . PHP_EOL;
}

// print summary
echo PHP_EOL . "Total holdings: " . count($holdings) . PHP_EOL;

