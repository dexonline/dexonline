$(document).ready(function(){
    // toggle checked/unchecked for all checkboxes in list
    $('input[name="checkbox-all"]').on('change', function(){
        var status = $(this).is(':checked');
        $('input[type="checkbox"]', $(".checkbox-inline")).prop('checked', status);
        $('#chng').text(status ? allDef : "0");
        $('#de').prop('hidden', !status);
    });
    
    // counting unchecked definitions, changing some fields accordingly
    $('input[name="checkbox-def"]').on('change', function(){
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
    
    // toggle between DeletionsOnly, InsertionsOnly and All modifications
    $('input[name="radiodiff"]').click( function(){
        var selValue = $(this).val();
        $('#panel-body').find('.defWrapper').removeClass().addClass('defWrapper').addClass(selValue);
    });
    
    $('[name="backButton"]').click(function() {
      window.location.href = 'index.php';  
    });

    // getting the array for unchecked definitions to be excluded from replace
    $('[name="saveButton"]').click(function() {
        var uncheckedIds = $('#panel-body').find('input[name="checkbox-def"]').filter(':visible:enabled').not(':checked').map(function() { 
          return this.id; 
        }).get().join(',');
        $('input[name="excludedIds"]').val(uncheckedIds);
    });
    
    // setting variables
    var allDef =  parseInt($('#chng').text());
    var leftDef = 0;

});

