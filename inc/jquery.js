var operatingsys = (function() {
    var operatingsys = null;
    jQuery.ajax({
        'async': false,
        'global': false,
        'url': OneandOneParams.path + "inc/os.json",
        'dataType': "json",
        'success': function (data) {
            operatingsys = data;
        }
    });
    return operatingsys;
})();
jQuery(document).ready( function ($) 
{
    $('#server_table').DataTable();
    $('#image').empty(); // empty the drop down (if necessarry)
    $(operatingsys).each(
		function(iIndex, sElement) 
		{
			var friendlyname = sElement.osVersion + " " + sElement.architecture + 'Bit';
			if(sElement.name.indexOf('Plesk') != -1){
				friendlyname = friendlyname + ' with Plesk';
			}
			if(sElement.name.indexOf('wordpress') != -1){
				return true;
			}
			if(sElement.name.indexOf('magento') != -1){
				return true;
			}
			if(sElement.name.indexOf('drupal') != -1){
				return true;
			}
			if(sElement.name.indexOf('iso') != -1){
				return true;
			}
			if(sElement.name.indexOf('min') != -1){
				return true;
			}
			if(sElement.name.indexOf('SQL') != -1){
				return true;
			}
			$('#image').append('<option value="sElement.id">'+ friendlyname +'</option>');
		}
	);
    $("#image").html($('#image option').sort(
		function(x, y) {
			return $(x).text() < $(y).text() ? -1 : 1;
		}
	));
    
} );

jQuery( document ).on( 'click', '#NEWSERVER-BTN', function() {
    var data = {
        action: '1and1_newserver',
        myvar:'something'
    };

    jQuery.post( ajaxurl, data, function(response) {
        console.log(response);
        // handle response from the AJAX request.
    });
});