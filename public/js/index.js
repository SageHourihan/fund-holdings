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
                $('#export-excel').show();                
                // Clear previous content and insert the new table
                $('#body').html(response);
                
                // Add sorting and filtering to the new table
                initializeTableSorting();
                
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

    // Function to initialize table sorting and filtering
    function initializeTableSorting() {
        // Add input for filtering
        $('.results-container').prepend('<input type="text" id="searchInput" placeholder="Search table...">');

        // Global sorting variable to track sort direction
        let sortDirections = {};

        // Add click event to table headers for sorting
        $('.funds-table th').each(function(index) {
            $(this).css('cursor', 'pointer')
                   .append('<span class="sort-icon">&#9660;</span>');
            
            // Initialize sort directions
            sortDirections[index] = 'asc';

            $(this).click(function() {
                sortTable(index);
            });
        });

        // Search/Filter functionality
        $('#searchInput').on('keyup', function() {
            let value = $(this).val().toLowerCase();
            $(".funds-table tr:not(:first)").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Table sorting function
        function sortTable(colIndex) {
            let table = $('.funds-table');
            let rows = table.find('tr:gt(0)').toArray();

            // Toggle sort direction
            let currentDirection = sortDirections[colIndex];
            let newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            sortDirections[colIndex] = newDirection;

            rows.sort(function(a, b) {
                let aColText = $(a).find('td').eq(colIndex).text();
                let bColText = $(b).find('td').eq(colIndex).text();

                // Remove $ and , for numeric sorting
                let aValue = aColText.replace(/[$,]/g, '');
                let bValue = bColText.replace(/[$,]/g, '');

                // Determine if it's a number or string
                let aNum = parseFloat(aValue);
                let bNum = parseFloat(bValue);

                // Check if it's a valid number
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return newDirection === 'asc' ? aNum - bNum : bNum - aNum;
                }

                // String comparison for non-numeric columns
                return newDirection === 'asc' 
                    ? aColText.localeCompare(bColText) 
                    : bColText.localeCompare(aColText);
            });

            // Reinsert sorted rows
            table.find('tbody').empty().append(rows);

            // Update sort icons
            updateSortIcons(colIndex, newDirection);
        }

        // Update sort icons to show current sort direction
        function updateSortIcons(sortedColIndex, direction) {
            $('.funds-table th .sort-icon').text('');
            let icon = direction === 'asc' ? '&#9650;' : '&#9660;';
            $('.funds-table th').eq(sortedColIndex).find('.sort-icon').html(icon);
        }
    }

     let top = $("#top");
  
    // When the user scrolls down 20px from the top of the document, show the button
    $(window).scroll(function() {
        if ($(document).scrollTop() > 20) {
            top.show();
        } else {
            top.hide();
        }
    });
  
    // When the user clicks on the button, scroll to the top of the document
    $("#top").click(function() {
        $("html, body").animate({scrollTop: 0}, "fast");
        return false;
    });
});
