<div class="manager-wrapper">
	<h3 class="menuglava">[[lang/item-menu-titel]]</h3>
	<form class="largeform" action="load.php?id=imanager&edit" method="post" enctype="multipart/form-data" accept-charset="utf-8">
	<input name="id" type="hidden" value="[[item-id]]" >
	<input name="page" type="hidden" value="[[back-page]]" >
	<input name="categoryid" type="hidden" value="[[currentcategory]]">
	<input name="timestamp" type="hidden" value="[[timestamp]]">
	<div>
		<div class="fieldarea">
			<label for="title">[[lang/title]]</label>
			<p><input id="title" class="im-title" name="name" type="text" value="[[itemname]]" placeholder="[[lang/fill_me]]" /></p>
		</div>
		[[custom-fields]]
		<p><input name="submit" type="submit" class="submit" value="[[lang/savebutton]]" /></p>
	</div>
	</form>
</div>
