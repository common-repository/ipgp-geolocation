(function( $ ) {

//Add countries to the rule list
$( document ).ready(function() {
	
	$( "#ipgpgeo_addcountry" ).click(function() {
		
		var code = $( "#ipgpgeo_allcountries" ).val();
		var country = $( "#ipgpgeo_allcountries  option:selected" ).text();
	  // console.log( code + country);
	   
		var activecountries = 	 $( "#ipgpgeo_activecountries" ).val();
		if(activecountries) {
			activecountries = activecountries + ',' + code;		
		}
		else {
			activecountries = code;		
		}
		$( "#ipgpgeo_activecountries" ).val(activecountries);
		
		var span = $('<span />').html(country + ' <a href="javascript:;" class="ipgpgeo_countryremove" data-code="' + code + '" >x</a>   ');
		$( "#ipgpgeo_activecountriesdiv" ).append(span);
		
		$( ".ipgpgeo_countryremove" ).click(function() {
		
			var code = $(this).data("code");
			console.log(code);
			var activecountries = 	 $( "#ipgpgeo_activecountries" ).val();
			if(activecountries) {
				 activecountries = (function(list, value, separator) {
  					  separator = separator || ",";
					  var values = list.split(separator);
					  for(var i = 0 ; i < values.length ; i++) {
					    if(values[i] == value) {
					      values.splice(i, 1);
					      return values.join(separator);
					    }
					  }
					  return list;
					})(activecountries, code, ',');	 
			}
			else {
				activecountries = '';		
			}
			$( "#ipgpgeo_activecountries" ).val(activecountries);
		
			$(this).parent().fadeOut(300);
	
		return false;
		});
		
	  return false;
	});
	

	
});


})( jQuery );