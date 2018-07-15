<?php
class FieldFilepicker implements FieldInterface
{
	public $properties;
	protected $tpl;

	public function __construct(TemplateEngine $tpl)
	{
		$this->tpl = $tpl;
		$this->name = null;
		$this->class = null;
		$this->id = null;
		$this->value = null;
		$this->style = null;
		$this->configs = new stdClass();
	}


	public function render($sanitize=false)
	{
		if(is_null($this->name))
			return false;

		$itemeditor = $this->tpl->getTemplates('field');
		$textfield = $this->tpl->getTemplate('filepicker', $itemeditor);

		$type = isset($this->configs->type) ? $this->configs->type : 'images';

		$output = $this->tpl->render($textfield, array(
				'name' => $this->name,
				'class' => $this->class,
				'style' => !empty($this->style) ? ' style="'.$this->style.'" ' : '',
				'visible_class' => (($type != 'images') ? ' class="hidden" ' : ''),
				'id' => $this->id,
				'value' => !empty($sanitize) ? $this->sanitize($this->value) : $this->value), true, array()
		);

		$output .= '
			<script type="text/javascript">

			function fill_image_'.$this->id.'(url) {
				console.log(url);
				$("#'.$this->id.'").val(url);
				get_thumb_'.$this->id.'();
			}
			$(function() {
				$("#btn_'.$this->id.'").click(function(e) {
					e.preventDefault();
					window.open("'.
					IM_SITE_URL.'plugins/i18n_customfields/browser/filebrowser.php?func=fill_image_'.$this->id.'&type='.$type.'",
					"browser", "width=800,height=500,left=100,top=100,scrollbars=yes");
				});

				function get_thumb_'.$this->id.'() {
					setTimeout(function() {
						var thumb = $("#'.$this->id.'").val();
						if(thumb.startsWith("'.IM_SITE_URL.'" + "data/uploads/")) {
							var file_path = $("#'.$this->id.'").val();
							var file_name = file_path.substr(file_path.lastIndexOf("/") + 1);
							var file_dir = file_path.replace("'.IM_SITE_URL.'" + "data/uploads", "").replace(file_name, "");
							thumb = "'.IM_SITE_URL.'" + "data/thumbs" + file_dir + "/thumbsm." + file_name;
						} else {
							thumb = thumb.replace("/thumbnail.", "/thumbsm.");
						}
						if(thumb.match(/\.(gif|jp?g|tiff|png)$/gi) != null) {
							$("#thumb_'.$this->id.'").attr("src", thumb);
						} else {
							$("#thumb_'.$this->id.'").attr("src", "");
						}
					}, 400);
				}
				get_thumb_'.$this->id.'();
			});

			</script>';

		return $output;
	}
	protected function sanitize($value){return imanager('sanitizer')->text($value);}

	public function getConfigFieldtype(){
		/* ok, get our dropdown field, infotext and area templates */
		$tpltext = $this->tpl->getTemplate('text', $this->tpl->getTemplates('field'));
		$tplinfotext = $this->tpl->getTemplate('infotext', $this->tpl->getTemplates('itemeditor'));
		$tplarea = $this->tpl->getTemplate('fieldarea', $this->tpl->getTemplates('itemeditor'));

		// let's load accepted value
		$type = isset($this->configs->type) ? $this->configs->type : 'images';

		// render textfied <input name="[[name]]" type="text" class="[[class]]" id="[[id]]" value="[[value]]"[[style]]/>
		$textfied = $this->tpl->render($tpltext, array(
				// NOTE: The PREFIX must always be used as a part of the field name
				'name' => self::PREFIX . 'type',
				'class' => '',
				'id' => '',
				'value' => $type
			)
		);

		// render infotext template <p class="field-info">[[infotext]]</p>
		$infotext = $this->tpl->render($tplinfotext, array(
				'infotext' => '<i class="fa fa-info-circle"></i>
					File types, example: files or images')
		);

		// let's merge the pieces and return the output
		return $this->tpl->render($tplarea, array(
				'fieldid' =>  '',
				'label' => 'Enter file type here',
				'infotext' => $infotext,
				'area-class' => 'fieldarea',
				'label-class' => '',
				'required' => '',
				'field' => $textfied), true
		);
	}
}