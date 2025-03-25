$(document).ready(function() {
    $('#submit').on('click', function(){
        let funds = $('#funds').val();
        let investments = $('#investments').val() || '';
        
        // Validate inputs
        if (!funds) {
            alert('Please enter fund tickers');
            return;
        }
        
        // Validate that number of funds matches number of investments
        let fundArray = funds.split(',').map(f => f.trim());
        
        // Modify investment parsing to handle comma-separated numbers
        let investmentArray = investments ? 
            investments.split(',').map(i => {
                // Remove all non-numeric characters and convert to float
                let cleanInvestment = parseFloat(i.replace(/[^\d.-]/g, ''));
                return isNaN(cleanInvestment) ? 0 : cleanInvestment;
            }) : 
            fundArray.map(() => 0);
        
        if (fundArray.length !== investmentArray.length) {
            alert('Number of funds must match number of investments');
            return;
        }
        
        // Convert investments to a clean comma-separated string
        let cleanInvestments = investmentArray.join(',');
        
        // Show loader
        $('#loader').show();
        
        $.ajax({
            url: '../src/api/getFunds.php',
            type: 'GET',
            data: {
                funds: funds,
                investments: cleanInvestments
            },
            success: function(response){
                // Hide loader
                $('#loader').hide();
                
                // Clear previous content and insert the new table
                $('#body').html(response);
                
                // Optional: Add debug logging
                console.log("Sent Funds:", funds);
                console.log("Sent Investments:", cleanInvestments);
            },
            error: function(xhr, status, error){
                // Hide loader
                $('#loader').hide();
                
                console.log("AJAX Error: ", xhr, status, error);
                $('#body').html('<p>Error fetching funds data.</p>');
            }
        });
    });

    $('#export-excel').on('click', function() {
    // Get the current funds and investments from the previous AJAX call
    let funds = $('#funds').val();
    let investments = $('#investments').val() || '';
    
    // Redirect to the same page with export parameter
    window.location.href = '../src/api/getFunds.php?export=excel&funds=' + encodeURIComponent(funds) + '&investments=' + encodeURIComponent(investments);
});
});
