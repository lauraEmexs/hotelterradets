<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<?php //print_r ($data_struct); ?>
<style>
	.select2-container { width:100%; }
	#seo-section .CodeMirror {height: auto}
	#seo-section { display: none;}
	.CodeMirror {height: 500px;}

	a.quick-edit {
		position: absolute;
		top: 25px;
		right: -20px;
		font-size: 20px;
	}

</style>
<?php //print_r($data_struct);die;?>
<?php
$ids_codemirror=array();
$ckeditor_inputs = [];
?>

<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-book fa-fw"></i> <?php echo ($id && $id != 0) ? "Editar" : "Nuevo"; ?> <?php echo $title_section;?>

		<?php if ($preview_link) : ?>
			<div class="box-tools pull-right">
				<?php
				echo ($id)
						? anchor($preview_link, '<i class="fa fa-eye"></i>', array('class' => 'dedit-pages-view-web', 'target' => '_blank'))
						: ''; ?>
			</div>
		<?php endif; ?>

	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-lg-12">
                <?php $session = \Config\Services::session();
                if($session->getFlashdata('message')):?>
                    <?php echo $session->getFlashdata('message')?>
                <?php endif;?>
				<?php echo form_open_multipart('admin/dynamic/dupdate/'. $slug . $domain_url_params, array('role'=>'form', 'onsubmit'=>'return checkSize();')); ?>
				<!-- Hidden elements -->
				<?php echo form_hidden('language', $idioma);?>
				<?php echo form_hidden('domain', $domain); ?>
				<?php foreach($data_struct as $struct) { ?>
					<?php if($struct['type']=='hidden' && $struct['name']!='language' && $struct['name']!='domain') { ?>
						<?php echo form_hidden($struct['name'], isset($data->{$struct['name']})?$data->{$struct['name']}:$struct['default']);?>
					<?php } ?>
				<?php } ?>

				<!-- Language Elements -->
				<div class="row">
					<div class="col-lg-6">
						<label>Idioma de esta p&aacute;gina</label>
						<p><img src="<?php echo base_url('assets/themes/adminlte/flags/blank.png'); ?>" class="flag flag-<?php echo ($idioma=='en')?'us':$idioma;?>" alt="<?php echo $idioma;?>" /> <?php echo $idioma_name;?></p>
					</div>
					<?php if($idioma!=$idioma_original) { ?>
						<div class="col-lg-6">
							<label>Esta es una traducci&oacute;n de:</label>
							<p><img src="<?php echo base_url('assets/themes/adminlte/flags/blank.png');?>" class="flag flag-<?php echo ($idioma_original=='en')?'us':$idioma_original;?>" alt="<?php echo $idioma_original;?>" /> <?php echo $idioma_original_name;?></p>
						</div>
					<?php } ?>
				</div>


				<?php echo form_hidden('date_fecha', "entra");?>

				<!-- Others elements -->
				<div class="row">
					<?php foreach($data_struct as $struct) {
					if ($struct['name'] == 'text_meta_keywords') {
						continue;
					}
					?>


					<?php if($struct['name']=='auto_slug' || $struct['name']=='slug') { ?>
				</div><hr><h3 id="seo-header">SEO <i class="fa fa-angle-down" aria-hidden="true"></i></h3><div class="row" id="seo-section">
			<?php } ?>


			<?php if($struct['name']=='multiple_gallery') { ?>
			</div><hr><h3>Galerías de fotos</h3><div class="row">
			<?php } ?>

			<?php if(($struct['type']=='text' || $struct['name'] =='slug') && $struct['name'] !='text_slug') { ?>
				<div class="col-lg-12">
					<div class="form-group">
						<?php echo form_label($struct['label'],$struct['name']);?>
						<?php echo form_input(array('name'=>$struct['name'],'id'=>$struct['name'],'value'=>isset($data->{$struct['name']})?$data->{$struct['name']}:'','type'=>'text','class'=>'form-control') );?>
						<?php
						if (in_array($struct['name'], ['text_button_url', 'text_read_more_url', 'text_reserve_button_url'])) {
							echo '<p class="text-muted">https://, ##MOTOR##, ##URL_PAG_123##, mailto:info@hotelpalace.com, tel:+34...</p>';
						}
						?>
						<?php echo form_error($struct['name']);?>
					</div>
				</div>
				<?php if ($slug == 'documents' ) { ?>
					<div style="clear:both"></div>
				<?php } ?>
			<?php }elseif($struct['name']=='text_slug') {  ?>
				<div class="col-lg-3">
					<div class="form-group">
						<?php echo form_label($struct['label'],$struct['name']);?>
						<?php $data_input=array('name'=>$struct['name'],'id'=>$struct['name'],'value'=>isset($data->{$struct['name']})?$data->{$struct['name']}:'','type'=>'text','class'=>'form-control'); ?>
						<?php if($this->session->userdata('logged_user')->group!='admin') $data_input['readonly']=true; ?>
						<?php echo form_input($data_input);?>
						<?php echo form_error($struct['name']);?>
					</div>
				</div>
			<?php } ?>

			<?php if($struct['type']=='auto') { ?>
				<div class="col-lg-3">
					<div class="form-group">
						<?php echo form_label($struct['label'],$struct['name']);?>
						<?php $data_input=array('name'=>$struct['name'],'id'=>$struct['name'],'value'=>isset($data->{$struct['name']})?$data->{$struct['name']}:'','type'=>'text','class'=>'form-control'); ?>
						<?php if($struct['name']=='auto_slug') $data_input['readonly']=true; ?>
						<?php echo form_input($data_input);?>
						<?php echo form_error($struct['name']);?>
					</div>
				</div>
			<?php } ?>

			<?php if($struct['type']=='number') { ?>
				<div class="col-lg-3">
					<div class="form-group">
						<?php echo form_label($struct['label'],$struct['name']);?>
						<?php echo form_input(array('name'=>$struct['name'],'id'=>$struct['name'],'value'=>isset($data->{$struct['name']})?$data->{$struct['name']}:'','type'=>'number','class'=>'form-control','min'=>'0','step'=>'1') );?>
						<?php echo form_error($struct['name']);?>
					</div>
				</div>
			<?php } ?>

			<?php if($struct['type']=='image') { ?>
			</div>
				<div class="row">
					<div class="col-lg-3">
						<div class="form-group">
							<?php echo form_label($struct['label'],$struct['name'],array('style'=>'display:block'));?>
							<?php if(isset($data->{$struct['name']}) && trim($data->{$struct['name']})!='' ) {?>
								<img loading="lazy" class="thumbnail img-responsive" style="display:inline"; src="<?php echo base_url();?>/<?php echo $data->{$struct['name']};?>"/>
								<button type="button" style="display:inline;vertical-align:bottom;margin:20px;" class="btn btn-warning" onclick="document.getElementById('<?php echo $struct['name'];?>_hidden').value='xDELETEx';alert('Fet. Recorda guardar el canvis perque tingui efecte.');">Borrar imatge</button>
							<?php } ?>
							<?php echo form_input(array('name'=>$struct['name'].'_hidden','id'=>$struct['name'].'_hidden','value'=>isset($data->{$struct['name']})?$data->{$struct['name']}:'','type'=>'hidden') );?>
							<?php echo form_input(array('name'=>$struct['name'],'id'=>$struct['name'],'value'=>isset($data->{$struct['name']})?$data->{$struct['name']}:'','type'=>'file','class'=>'form-control file-to-check')); ?>
							<?php echo form_error($struct['name']);?>
						</div>
					</div>
				</div>
				<div class="row">
					<?php } ?>

					<?php if ($struct['type'] == 'document') { ?>
						<div class="col-lg-3">
							<div class="form-group">
								<?php echo form_label($struct['label'], $struct['name'], array('style' => 'display:block')); ?>
								<?php if (isset($data->{$struct['name']}) && trim($data->{$struct['name']}) != '') {
									$file_name = explode('/', $data->{$struct['name']});
									$file_name = $file_name[count($file_name) - 1];
									?>
									<p>Actual documento adjuntado: <b>
											<a href="<?php echo base_url() . $data->{$struct['name']}; ?>"
											   target="_blank"><?php echo $file_name; ?></a></b>
									</p>
									<?php echo form_label('¿Borrar?', $struct['name'] . '_delete'); ?>
									<?php echo form_checkbox(array('name' => $struct['name'] . '_delete', 'id' => $struct['name'] . '_delete', 'value' => 'xDELETEx', 'checked' => false, 'style' => 'margin:10px;')); ?>
									<?php echo form_error($struct['name']); ?>
								<?php } ?>
								<?php echo form_input(array('name' => $struct['name'] . '_hidden', 'id' => $struct['name'] . '_hidden', 'value' => isset($data->{$struct['name']}) ? $data->{$struct['name']} : '', 'type' => 'hidden')); ?>
								<?php echo form_input(array('name' => $struct['name'], 'id' => $struct['name'], 'value' => isset($data->{$struct['name']}) ? $data->{$struct['name']} : '', 'type' => 'file', 'class' => 'form-control')); ?>
								<?php echo form_error($struct['name']); ?>
							</div>
						</div>
					<?php } ?>

					<?php if($struct['type']=='siz') { ?>
						<div class="col-lg-3">
							<div class="form-group">
								<?php echo form_label($struct['label'],$struct['name'],array('style'=>'display:block'));?>
								<?php if(isset($data->{$struct['name']}) && trim($data->{$struct['name']})!='' ) {
									$file_name = explode('/', $data->{$struct['name']});
									$file_name = $file_name[count($file_name)-1];
									?>
									<p>Actual documento adjuntado: <b>
											<a href="<?php echo base_url() . $data->{$struct['name']}; ?>" target="_blank"><?php echo $file_name; ?></a></b>
									</p>
									<?php echo form_label('¿Borrar?', $struct['name'].'_delete');?>
									<?php echo form_checkbox(array('name' => $struct['name'] .'_delete','id'=>$struct['name'] .'_delete', 'value'=>'xDELETEx', 'checked'=> false ,'style'=>'margin:10px;') );?>
									<?php echo form_error($struct['name']);?>
								<?php } ?>
								<?php echo form_input(array('name'=>$struct['name'].'_hidden', 'id'=>$struct['name'].'_hidden','value'=>isset($data->{$struct['name']})?$data->{$struct['name']}:'','type'=>'hidden') );?>
								<?php echo form_input(array('name'=>$struct['name'],'id'=>$struct['name'],'value'=>isset($data->{$struct['name']})?$data->{$struct['name']}:'','type'=>'file','class'=>'form-control') );?>
								<?php echo form_error($struct['name']);?>
							</div>
						</div>
					<?php } ?>

					<?php if($struct['type']=='checkbox') { ?>
						<div class="col-lg-2">
							<div class="form-group">
								<?php echo form_label($struct['label'],$struct['name']);?>
								<?php echo form_checkbox(array('name'=>$struct['name'],'id'=>$struct['name'],'value'=>'1', 'checked'=>(isset($data->{$struct['name']}) && ($data->{$struct['name']}))?true:false ,'style'=>'margin:10px;') );?>
								<?php echo form_error($struct['name']);?>
							</div>
						</div>
					<?php } ?>

					<?php if($struct['type']=='textarea') { ?>
						<?php if( ($this->session->userdata('logged_user')->group=='admin') || TRUE) { $ckeditor_inputs[]=$struct['name']; ?>
							<div class="col-lg-12">
								<div class="form-group">
									<?php echo form_label($struct['label'],$struct['name']);?>
									<?php echo form_textarea(array('name'=>$struct['name'],'id'=>$struct['name'],'value'=>isset($data->{$struct['name']})?$data->{$struct['name']}:'','type'=>'file','class'=>'form-control') );?>
									<?php if($struct['name']=='textarea_page') { ?> <span>Urls especiales: Páginas estáticas: ##URL_PAG_ID##, Galerías: ##URL_IMG_ID##, ##MOTOR##</span> <?php } ?>
								</div>
							</div>
						<?php } else { ?>
							<div class="col-lg-12">
								<div class="form-group">
									<?php echo form_label($struct['label'],$struct['name']);?>
									<?php echo $this->ckeditor->editor($struct['name'],isset($data->{$struct['name']})?$data->{$struct['name']}:'',$config_mini); ?>
									<?php if($struct['name']=='textarea_page') { ?> <span>Urls especiales: Páginas estáticas: ##URL_PAG_ID##, Galerías: ##URL_IMG_ID##, ##MOTOR##</span> <?php } ?>
								</div>
							</div>
						<?php } ?>
					<?php } ?>

					<?php if($struct['type']=='select' && $struct['name']=='dynamic_gallery') {  ?>

						<div class="col-lg-4">
							<div class="form-group">
								<?php echo form_label($struct['label'],$struct['name']);?>
								<select class="form-control" name="<?php echo $struct['name'];?>" id="<?php echo $struct['name'];?>">
									<?php $k = 0;?>
									<!--<option value="<?php echo (is_object($data) && $k==$data->{$struct['name']}) ? $k : 0;?>" <?php echo (is_object($data) && $k==$data->{$struct['name']})?"SELECTED":"";?>> --- </option>-->
									<?php
									foreach($struct['content'] as $k=>$v) {
										?>
										<option value="<?php echo $k;?>" <?php echo (is_object($data) && $k==$data->{$struct['name']})?"SELECTED":"";?>><?php echo $v;?></option>
									<?php } ?>
								</select>
								<?php echo form_error($struct['name']);?>
							</div>
						</div>

					<?php } elseif($struct['type']=='select') { ?>
						<div class="col-lg-4">
							<div class="form-group">
								<?php echo form_label($struct['label'],$struct['name']);?>
								<?php echo form_dropdown($struct['name'],$struct['content'],isset($data->{$struct['name']})?$data->{$struct['name']}:$struct['default'],'class=form-control');?>
								<?php echo form_error($struct['name']);?>
							</div>
						</div>
					<?php } ?>

					<?php if($struct['type']=='enum') { ?>
						<div class="col-lg-4">
							<div class="form-group">
								<?php echo form_label($struct['label'],$struct['name']);?>
								<?php echo form_dropdown($struct['name'],$struct['content'],isset($data->{$struct['name']})?$data->{$struct['name']}:$struct['default'],'class=form-control');?>
								<?php echo form_error($struct['name']);?>
							</div>
						</div>
					<?php } ?>

					<?php if($struct['type']=='multiselect') { ?>
						<div class="col-lg-12">
							<div class="form-group">
								<?php echo form_label($struct['label'],$struct['name']);?><br/>
								<?php if($struct['name']=='multiple_gallery') { $select_gallery_array=explode(",",$data->{$struct['name']}); ?>
									<input type="hidden" id="multiple_gallery" name="multiple_gallery" value="<?php echo $data->{$struct['name']};?>"/>

									<?php
									$selected_gallery=array();
									foreach($select_gallery_array as $idg) {
										if(!empty($content_galleries[$idg]))
											$selected_gallery[]='{id:"'.$idg.'", text: "'.$content_galleries[$idg]['title'].'"}';
									}
									?>

									<select id="mp_gallery" name="mp_gallery[]" class="select2 select2-offscreen" multiple="multiple" tabindex="-1">
										<?php foreach($content_galleries as $idg => $gal) {  ?>
											<option value="<?php echo $idg;?>" data-img="<?php echo $gal['img'];?>" ><?php echo $gal['title'];?></option>
										<?php } ?>
									</select>
								<?php } else {

									//we need to re-orden options with selected ones
									if(isset($data->{$struct['name']}) && $data->{$struct['name']} != ''){
										$selected = $selected_tmp = explode(",", $data->{$struct['name']});
										$values = array();

										while ($id = array_shift($selected_tmp)){
											$values[$id] = $struct['content'][$id];
											unset($struct['content'][$id]);
										}

										$values = $values + $struct['content'];
									}else{
										$values = $struct['content'];
										$selected = array();
									}
									echo form_multiselect(
											$struct['name'].'[]',
											$values,
											$selected,
											'class="autoselect2 select2"'
									);
								}
								?>
								<?php echo form_error($struct['name']);?>
							</div>
						</div>
					<?php } ?>

					<?php if($struct['type']=='date' && $struct['name']!='text_slug') { ?>
						<div class="col-lg-6">
							<div class="form-group">
								<?php echo form_label($struct['label'],$struct['name']);?>
								<div class='input-group date' name="datetimepicker1" id='datetimepicker1'>
                                    <input type='text' name="<?= $struct['name'] ?>" value="<?= htmlentities(isset($data->{$struct['name']})?$data->{$struct['name']}:$struct['default']) ?>" class="form-control" />
									<span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
								</div>
								<?php echo form_error($struct['name']);?>
								<script type="text/javascript">
									$(function () {
										// Moment
										moment().format();
										console.log("<?php echo $data->{$struct['name']} ?>");
										var day = moment("<?php echo $data->{$struct['name']} ?>");
										$('#datetimepicker1').datetimepicker({
											date:day,
										}).on('dp.show dp.change', function () {
											console.log("nueva fecha: "+$('#datetimepicker1').data("DateTimePicker").date());
											$("input[name*='date_fecha']").val($('#datetimepicker1').data("DateTimePicker").date());
											//$("input[name*='date_fecha']").text($('#datetimepicker1').data("DateTimePicker").date());
											//$("#fecha_oculta").val($('#datetimepicker1').data("DateTimePicker").date());
										});
									});
								</script>
							</div>
						</div>
					<?php } ?>
					<?php if($struct['type']=='calendar') { ?>
						<div class="col-lg-6">
							<div class="form-group">
								<?php
								echo form_label($struct['label'], $struct['name']);
								$form_params = [
										'name' => $struct['name'],
										'id' => $struct['name'],
										'value' => isset($data->{$struct['name']})?$data->{$struct['name']}:'',
										'type' => 'text',
										'class' => 'form-control',
								];
								echo form_input($form_params);
								echo form_error($struct['name']);
								?>
								<script>
									$("input[name='<?php echo $struct['name']; ?>']:visible").datepicker({
										format: 'dd/mm/yyyy',
										autoclose: true
									});
								</script>
							</div>
						</div>
					<?php } ?>
					<?php if($struct['type']=='time') { ?>
						<div class="col-lg-6">
							<div class="form-group">
								<?php
								echo form_label($struct['label'], $struct['name']);
								$form_params = [
										'name' => $struct['name'],
										'id' => $struct['name'],
										'value' => isset($data->{$struct['name']})?$data->{$struct['name']}:'',
										'type' => 'text',
										'class' => 'form-control',
								];
								echo form_input($form_params);
								echo form_error($struct['name']);
								?>
								<script>
									$("input[name='<?php echo $struct['name']; ?>']:visible").timepicker({
										timeFormat: 'H:mm',
										interval: 10,
										minTime: '8',
										maxTime: '22:00pm',
										//defaultTime: '11',
										//startTime: '10:00',
										dynamic: false,
										dropdown: true,
										scrollbar: true
									});
								</script>
							</div>
						</div>
					<?php } ?>

					<?php if ($struct['type']=='separator') { ?>
						<div class="col-lg-12">
							<div class="form-group">
								<h3><?= $struct['label'] ?></h3>
							</div>
						</div>
					<?php } ?>

					<?php } ?>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<?php echo form_button(array('name'=>'save_view', 'type'=>'submit', 'class'=>'btn bg-black btn-flat','content' => '<i class="fa fa-save fa-fw"></i> Guardar')); ?>
						<?php echo form_button(array('name'=>'save_exit', 'type'=>'submit', 'class'=>'btn bg-black btn-flat','content' => '<i class="fa fa-save fa-fw"></i> Guardar y salir')); ?>
						<?php echo ($id != 0 && $idioma != $idioma_original) ? form_button(array('name'=>'delete_translation', 'type'=>'submit', 'class'=>'btn bg-danger btn-flat pull-right', 'onclick' => "return confirm('¿Quieres eliminar esta entrada?')", 'content' => '<i class="fa fa-save fa-fw"></i> Eliminar')) : ''; ?>
					</div>
				</div>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function() {
		// SEO section Toggle
		$('#seo-header').click(function () {
			$("#seo-section").slideToggle();
		});
		/*$('#mp_gallery').select2({
				placeholder : '',
				formatResult: function(item,container) {
					if(item.element[0].dataset.img){
						return "<img width='100px' height='100px' src='"+item.element[0].dataset.img+"'/><span>"+item.text+"</span>";
					}else
						return item.text;
				}
			}
		).on("change",function(e){
			var elements_selected=$('#mp_gallery').select2("data");
			var salida='';
			for(i=0;i<elements_selected.length;i++){
				salida+=elements_selected[i].id+',';
			}
			salida=(salida=='')?salida:salida.slice(0,-1);
			$('#multiple_gallery').val(salida);
		});
		$('#mp_gallery').select2("data",[<?php echo (isset($selected_gallery)) ? implode(",", $selected_gallery) : null;?>]);

        $('.autoselect2').select2(
            {
                placeholder : '',
                width: '100%',
                formatResult: function(item,container) {
                    if(item.element[0].dataset.img){
                        return "<img width='100px' height='100px' src='"+item.element[0].dataset.img+"'/><span>"+item.text+"</span>";
                    }else
                        return item.text;
                }
            }
        );

        $('.autoselect2').on('change', function(e) {
            if(!e.added){
                return;
            }
            //var element = $(this).find('[value="' + e.params.data.id + '"]');
            var element = e.added.element;
            $(this).append(element);
            $(this).trigger('change');
        });*/

		function initialize_select2(selector) {
			if (selector === undefined) {
				selector = '.autoselect2';
			}

			$(selector).select2({
				placeholder: '',
				width: '100%',
				formatResult: function (item, container) {
					if (item.element[0].dataset.img) {
						return "<img loading='lazy' width='100px' height='100px' src='" + item.element[0].dataset.img + "'/><span>" + item.text + "</span>";
					} else
						return item.text;
				}
			});

			/* Make it sortable and keep selected order */
			$(selector).parent().find("ul.select2-selection__rendered").sortable({
				containment: 'parent',
				update: function() {
					orderSortedValues($(this));
				}
			});

			orderSortedValues = function(selector) {
				$(selector).parent().find("ul.select2-selection__rendered").children("li[title]").each(function(i, obj){
					var element = $(selector).parents('.form-group').find("option").filter(function () { return $(this).html() === obj.title; });

					moveElementToEndOfParent(element)
				});
			};

			moveElementToEndOfParent = function(element) {
				var parent = element.parent();
				element.detach();
				parent.append(element);
			};

			/* Stop automatic ordering */
			$(selector).on("select2:select", function (evt) {
				var id = evt.params.data.id;
				var element = $(this).children("option[value="+id+"]");

				moveElementToEndOfParent(element);

				$(this).trigger("change");
			});
			/* End sortable */
		}
		initialize_select2();

		//$("#dynamic_gallery").msDropDown();

		<?php foreach($ids_codemirror as $id) { ?>
		get_editor('<?php echo $id;?>');
		<?php } ?>


		/* Universal edit button for dynamic content */
		let quick_edit = function() {
			if ($(this).attr('multiple') == 'multiple') {
				return;
			}
			$(this).next('.quick-edit').detach();
			let slug = $(this).attr('name');
			let item_id = $(this).find('option:selected').val();
			if (item_id == 0) return;
			let href = '../../'+ slug.replace("dynamic_", "").split('_')[0] +'/<?= $idioma ?>/'+ item_id +'<?= $domain_url_params ?>';
			$(this).after('<a href="'+ href +'" target="_blank" class="quick-edit" title="Editar"><i class="fa fa-pencil fa-fw"></i></a>');
		};

		$("select[name^='dynamic_']").each(quick_edit);
		//$("select[name^='dynamic_']").on('change', quick_edit);

		/* Universal add button for dynamic content */
		let quick_add = function() {
			let slug = $(this).attr('name');
			if (slug.includes("multiple_") || slug.includes("dynamic_"))
				if ($(this).attr('multiple') == 'multiple') {
					slug = slug.replace("multiple_", "");
					slug = slug.replace("[]", "");
				} else {
					slug = slug.replace("dynamic_", "");
				}
			let href = '../../../dlist/'+ slug/*.split('_')[0]*/ +'/<?= $domain_url_params ?>';
			$(this).next('.quick-add').detach();
			$(this).parent().find('label').after('<a href="'+ href +'" target="_blank" class="quick-add" title="Ver contenido"><i class="fa fa-plus-circle fa-fw"></i></a>');
		};
		//$("select").each(quick_add);
		$("select[name^='dynamic_'], select[name^='multiple_']").each(quick_add);
		//$("select[name^='dynamic_'], select[name^='multiple_']").on('change', quick_add);
		/* end */
	});
	function checkSize(){
		var peso = $('.file-to-check')[0].files[0].size;
		if(peso > <?php echo $this->selected_domain_preferences->max_upload_size; ?>000){
			alert("Imagen demasiado grande, tamaño máximo: <?php echo $this->selected_domain_preferences->max_upload_size; ?>KB");
			return false;
		}
		return true;
	}
	function get_editor(editor_id){
		editor = CodeMirror.fromTextArea(document.getElementById(editor_id), {
			lineNumbers: true,
			matchBrackets: true,
			mode: "text/html",
			indentUnit: 2,
			indentWithTabs: true,
			enterMode: "keep",
			tabMode: "shift"
		});
	}


	<?php foreach($ckeditor_inputs as $ckeditor_id) : ?>

	CKEDITOR.replace( '<?php echo $ckeditor_id; ?>', {
		language: '<?php echo config('App')->defaultLangGestor; ?>',
		stylesSet: 'my_styles:<?php echo base_url(); ?>/assets/ckeditor/my_styles.js?2',
		customConfig: '<?php echo base_url(); ?>/assets/ckeditor/my_config.js'
	});

	<?php endforeach; ?>


	/* Switch for button multitype */
	initialize_button_type_switch();
	function initialize_button_type_switch() {
		$types_selector = '.button-type-video, .button-type-slider, .button-type-image, .button-type-document, ' +
				'.button-type-link, .button-type-external, .button-type-anchor, .button-type-modal';
		$($types_selector).hide();
		$(".button-type-selector select").each(function() {
			$(this).closest('.row').find('.' + this.value).show();
		});
		$('.button-type-selector select').on('change', function() {
			$(this).closest('.row').find($types_selector).hide();
			$(this).closest('.row').find('.' + this.value).show();
		});
	}

</script>

<?= $this->endSection() ?>