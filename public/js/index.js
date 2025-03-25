$(document).ready(function() {
    $('#submit').on('click', function(){
        let funds = $('#funds').val();

        // show loader
        $('#loader').show();

        $.ajax ({
            url: '../src/api/getFunds.php',
            type: 'GET',
            data: {funds:funds},
            success: function(response){
                //console.log(response);
                $('#loader').hide();
                $('#body').html(response); 
            },
            error: function(xhr, status, error){
                console.log("AJAX Error: ", xhr,  status, error);
            }
        });
    })
});
