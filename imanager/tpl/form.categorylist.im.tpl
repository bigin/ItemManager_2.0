<div class="manager-wrapper">
<h3 class="menuglava">[[lang/add_category]]</h3>
<form class="largeform" action="load.php?id=imanager&category&category_edit" method="post" accept-charset="utf-8">
<div>
	<p><label for="page-url">[[lang/add_new_category]]:</label>
	<input class="text-fields-left text" id="im-catinput" type="text" name="new_category" value="" /></p>
	[[filter]]
	[[header]]
	<table id="im-catlist" class="highlight">
		<thead>
		<tr>
			<th>&nbsp;</th>
			<th>[[lang/position_table]]</th>
			<th>[[lang/name_table]]</th>
			<th>[[lang/items_table]]</th>
			<th>&nbsp;</th>
		</tr>
		</thead>
		<tbody id="im-catlist-body">
			[[value]]
		</tbody>
	</table>
	[[pagination]]
	<p class="im-buttonwrapper"><span><input class="submit" type="submit" name="category_edit" value="[[lang/add_category_submit]]" /></span></p>
</div>
</form>
</div>