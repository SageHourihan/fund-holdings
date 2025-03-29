$(document).ready(function() {
    // Hide the export button initially
    $('#export-excel').hide();
    // Hide the top button initially
    $("#top").hide();

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

                // Apply Tailwind classes to make the table less condensed
                applyTailwindTableStyles();

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
                $('#body').html('<p class="text-red-600 font-medium text-lg">Error fetching funds data.</p>');
            }
        });
    });

    // Function to apply Tailwind classes to the table
    function applyTailwindTableStyles() {
        // Style the main table container - make it full width with overflow handling
        $('.results-container').addClass('mt-6 bg-white rounded-lg shadow-md w-full overflow-x-auto p-6');

        // Style the table itself - ensure it's full width and handles responsiveness
        $('.funds-table').addClass('w-full border-collapse table-auto min-w-full');

        // Style table headers
        $('.funds-table th').addClass('px-6 py-4 bg-gray-100 text-left text-sm font-medium text-gray-600 uppercase tracking-wider border-b');

        // Style table cells - less condensed with more padding
        $('.funds-table td').addClass('px-6 py-4 text-sm text-gray-900 border-b border-gray-200');

        // Remove whitespace-nowrap to allow text wrapping for better mobile display
        // except for critical columns that should stay on one line
        $('.funds-table td:nth-child(3)').addClass('whitespace-nowrap'); // Rank
        $('.funds-table td:nth-child(4)').addClass('whitespace-nowrap'); // Ticker
        $('.funds-table td:nth-child(8)').addClass('whitespace-nowrap'); // Your Shares
        $('.funds-table td:nth-child(9)').addClass('whitespace-nowrap'); // Your Value

        // Allow company names to wrap
        $('.funds-table td:nth-child(5)').addClass('max-w-xs'); // Company column

        // Apply specific styles to numeric columns (alignment, etc.)
        $('.funds-table td:nth-child(2)').addClass('text-right'); // Investment
        $('.funds-table td:nth-child(3)').addClass('text-center'); // Rank
        $('.funds-table td:nth-child(6)').addClass('text-right'); // Fund Percentage
        $('.funds-table td:nth-child(7)').addClass('text-right'); // Total Fund Shares
        $('.funds-table td:nth-child(8)').addClass('text-right'); // Your Shares
        $('.funds-table td:nth-child(9)').addClass('text-right'); // Your Value

        // Style alternating rows for better readability
        $('.funds-table tr:nth-child(even)').addClass('bg-gray-50');

        // Add hover effect on rows
        $('.funds-table tr:not(:first-child)').addClass('hover:bg-blue-50 transition-colors duration-150');

        // Style the summary section
        $('.summary').addClass('mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200');
        $('.summary p').addClass('text-lg font-medium text-blue-800 mb-2');

        // Apply custom styling for monetary and percentage values
        $('.funds-table td').each(function() {
            const text = $(this).text().trim();

            // Style monetary values (those starting with $)
            if (text.startsWith('$')) {
                $(this).addClass('font-medium');
            }

            // Style percentage values (those containing %)
            if (text.includes('%')) {
                $(this).addClass('font-medium');
            }
        });
    }

    $('#export-excel').on('click', function() {
        // Get the current funds and investments from the previous AJAX call
        let funds = $('#funds').val();
        let investments = $('#investments').val() || '';

        // Redirect to the same page with export parameter
        window.location.href = '../src/api/getFunds.php?export=excel&funds=' + encodeURIComponent(funds) + '&investments=' + encodeURIComponent(investments);
    });

    // Function to initialize table sorting and filtering
    function initializeTableSorting() {
        // Remove existing search input if any
        $('#searchInput').remove();

        // Add input for filtering with Tailwind styling
        $('.results-container').prepend('<input type="text" id="searchInput" placeholder="Search table..." class="w-full mb-4 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">');

        // Global sorting variable to track sort direction
        let sortDirections = {};

        // Add click event to table headers for sorting
        $('.funds-table th').each(function(index) {
            $(this).css('cursor', 'pointer')
                   .append('<span class="sort-icon ml-2">&#9660;</span>');

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

// Custom jQuery extension for contains case insensitive
$.extend($.expr[':'], {
    'contains': function(elem, i, match, array) {
        return (elem.textContent || elem.innerText || '').toLowerCase()
            .indexOf((match[3] || "").toLowerCase()) >= 0;
    }
});
