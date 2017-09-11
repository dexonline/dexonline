$(document).ready(function(){
    $('input[name="checkbox-all"]').on('change', function(){
        var status = $(this).is(':checked');
        $('input[type="checkbox"]', $(".checkbox-inline")).prop('checked', status);
        $('#chng').text(status ? allDef : "0");
        $('#de').prop('hidden', !status);
    });

    $('input[name="checkbox"]').on('change', function(){
        var unchecked = $(this).closest('#panel-body').find('input[type="checkbox"]').filter(':visible:enabled').not(':checked');
        $(this).closest('.panel-admin').find('input[name="checkbox-all"]').prop('checked', function() {
			return ( 0 === unchecked.length );
		});
        leftDef = allDef - unchecked.length;
        $('#chng').text(leftDef);
        $('#de').prop('hidden', leftDef === 0 ? true : function() { 
            return hideAmountPreposition(leftDef.toString());
        });
    });

    $('input[name="radiodiff"]').click( function(){
        var selValue = $(this).val();
        $('#panel-body').find('.defWrapper').removeClass().addClass('defWrapper').addClass(selValue);

    });
    
    $('input[type="checkbox"]').prop('checked', true);
    
    $('[name="backButton"]').click(function(event) {
      window.location.href = 'index.php';  
    });
    
    var allDef =  parseInt($('#chng').text());
    var leftDef = 0;

});

