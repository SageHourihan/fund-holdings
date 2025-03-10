$(document).ready(function() {
    $('#submit').on('click', function(){
        let funds = $('#funds').val();
        $.ajax ({
            url: '../src/api/getFunds.php',
            type: 'GET',
            data: {funds:funds},
            success: function(response){
                console.log(response);
            },
            error: function(xhr, status, error){
                console.log("AJAX Error: ", xhr,  status, error);
            }
        });
    })
});
