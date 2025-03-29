<?php include_once 'views/header.html' ?>
<?php include_once 'views/navbar.html' ?>
 <body>
    <div id="loader" class="loader" style="display:none;">
        <div class="spinner"></div>
    </div>
    <input id='funds' type='text' placeholder="Enter fund tickers (comma-separated)"></input>
    <input id='investments' type='text' placeholder="Corresponding investments (comma-separated)">
    <button id='submit'>Submit</button>
    <button id="export-excel" class="btn btn-primary">Export to Excel</button>
    <div id='body'></div>
    <button id="top">Go To Top</button>
</body>
</html>
