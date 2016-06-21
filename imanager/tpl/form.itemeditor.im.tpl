<div class="manager-wrapper">
	<h3 class="menuglava">[[lang/item-menu-titel]]</h3>
	<form class="largeform" action="load.php?id=imanager&edit=[[action_edit]]&page=[[action_page]]" method="post" enctype="multipart/form-data" accept-charset="utf-8">
	<input name="iid" type="hidden" value="[[item-id]]" >
	<input name="page" type="hidden" value="[[back-page]]" >
	<input name="categoryid" type="hidden" value="[[currentcategory]]">
	<input name="timestamp" type="hidden" value="[[timestamp]]">
	<div>
		<div class="fieldarea">
			<label for="catid" class="im-left">[[lang/item_id]]</label>
			<p id="catid" class="im-cat-info">[[item-id]]</p>
		</div>
		<div class="fieldarea">
			<label for="title">[[lang/title]]</label>
			<p><input id="title" class="im-title" name="name" type="text" value="[[itemname]]" placeholder="[[lang/fill_me]]" /></p>
		</div>
		<div class="fieldarea">
			<label for="position">[[lang/position]]</label>
			<p><input id="position" name="position" type="number" value="[[position]]" /></p>
		</div>
		<div class="fieldarea">
			<label for="active">[[lang/enabled]]</label>
			<p><input id="active" name="active" type="checkbox" value="1"[[checked]]/></p>
		</div>
		[[custom-fields]]

		<div class="fieldarea">
			<label for="itemcreated" >[[lang/item_created]]</label>
			[[infotext]]
			<p>[[created]]</p>
		</div>

		<div class="fieldarea">
			<label for="itemcreated" >[[lang/item_updated]]</label>
			[[infotext]]
			<p>[[updated]]</p>
		</div>

		<p><input name="submit" type="submit" class="submit" value="[[lang/savebutton]]" /></p>
	</div>
	</form>
</div>
