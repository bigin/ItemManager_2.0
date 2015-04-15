<div id="filterarea">
	<p class="label">[[lang/category_filter_title]]</p>
	<select class="text short" id="filterby" type="text" name="orderby">
		<option value="position" [[position]]>[[lang/position]]</option>
		<option value="name" [[name]]>[[lang/category_name]]</option>
		<option value="created" [[created]]>[[lang/date_created]]</option>
		<option value="updated" [[updated]]>[[lang/date_updated]]</option>
	</select>
	<select class="text short" id="option" type="text" name="option">
		<option value="asc" [[asc]] >ASC</option>
		<option value="desc" [[desc]] >DESC</option>
	</select>
	<p class="sm-label">[[lang/categories_per_page]]</p>
	<div id="im-nswitch">[[nswitch]]</div>
	<script>
	$(document).ready(function() {
		$('select').on('change', function() {
			var num = $('.active').children().attr('id');
			$.getList(num);
			return false;
		});

		$('.switchNumber').click(function(){
			var num = $(this).attr('id');
			$('.active').removeClass('active');
			$(this).parent().addClass('active');
			$.getList(num);
			return false;
		});

		$.getList = function(num) {
			var ftr = $('#filterby').val();
			var opt = $('#option').val();

			$.post("/admin/load.php?id=imanager&category&getcatlist="+num+"&filterby="+ftr+"&option="+opt,
			function(data, status){
				if(status = 'success') $('#im-catlist-body').replaceWith('<tbody id="im-catlist-body">'+data+'</tbody>');
			});
		}
	});

	</script>
</div>
