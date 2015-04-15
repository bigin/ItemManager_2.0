<div id="filterarea">
	<p class="label">[[lang/item_filter_title]]</p>
	<select class="text short" id="filterby" name="orderby">
		<option value="position" [[position]]>[[lang/position]]</option>
		<option value="name" [[name]]>[[lang/item_name]]</option>
		<option value="label" [[label]]>[[lang/item_label]]</option>
		<option value="active" [[active]]>[[lang/item_active]]</option>
		<option value="created" [[created]]>[[lang/date_created]]</option>
		<option value="updated" [[updated]]>[[lang/date_updated]]</option>
	</select>
	<select class="text short" id="option" name="option">
		<option value="asc" [[asc]] >ASC</option>
		<option value="desc" [[desc]] >DESC</option>
	</select>

	<select class="short" id="filterbyfield" name="orderbyfield">
		<option></option>
		[[fieldoptions]]
	</select>
	<select class="short" id="filter" name="filter">
		<option value="eq" [[eq]] >=</option>
		<option value="geq" [[geq]] >&gt;=</option>
		<option value="leq" [[leq]] >&lt;=</option>
		<option value="g" [[g]] >&gt;</option>
		<option value="l" [[l]] >&lt;</option>
	</select>
	<input class="short" id="filtervalue" type="text" name="filtervalue" value="[[filtervalue]]">

	<p class="sm-label">[[lang/items_per_page]]</p>
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

			$('#filtervalue').blur(function() {
				var num = $('.active').children().attr('id');
				$.getList(num);
				return false;
			});

			$('#filtervalue').keypress(function (e) {
				if(e.which == 13) {
					var num = $('.active').children().attr('id');
					$.getList(num);
					return false;
				}
			});

			$.getList = function(num) {
				var ftr = $('#filterby').val();
				var opt = $('#option').val();
				var ordf = $('#filterbyfield').val();
				var flr = $('#filter').val();
				var flrv = $('#filtervalue').val();

				$.post("/admin/load.php?id=imanager&getitemlist="+num+"&filterby="+ftr+"&option="+opt+"&filterbyfield="+ordf+"&filter="+flr+"&filtervalue="+flrv,
						function(data, status){
							console.log(data);
							if(status = 'success' && data) $('#im-itemlist-body').replaceWith('<tbody id="im-itemlist-body">'+data+'</tbody>');
						});
			}
		});

	</script>
</div>