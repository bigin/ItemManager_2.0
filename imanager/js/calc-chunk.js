//<script src='../plugins/imanager/js/numeral.min.js'></script>
//	<script>
	$.getList = function(){
	// German format
	var float_price = $('#retail_price').val().replace('.', '').replace(',', '.');
	var tax = float_price * ('0.'+$('#tax').val());
	var final = parseFloat(tax) + parseFloat(float_price);
	numeral.language('de');
	var output = numeral(final).format('0,0.00');
	$('#final_price').val(output);
};

$(document).ready(function() {
	// german notation
	numeral.language('de', {
		delimiters: {
			thousands: '.',
			decimal: ','
		},
		abbreviations: {
			thousand: 'k',
			million: 'm',
			billion: 'b',
			trillion: 't'
		},
		ordinal : function (number) {
			return number === 1 ? 'er' : 'ème';
		},
		currency: {
			symbol: '€'
		}
	});
	$('#retail_price').on('input', function() {$.getList()})
	$('#tax').change(function () {$.getList();});
});
//</script>
