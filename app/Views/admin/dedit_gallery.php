<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<?php
//print_r($gallery_images);
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-book fa-fw"></i> <?php echo ($id) ? "Editar" : "Nuevo"; ?> <?php echo $title_section; ?>
    </div>
    <div class="panel-body">
        <?php $session = \Config\Services::session();
        if($session->getFlashdata('message')):?>
            <?php echo $session->getFlashdata('message')?>
        <?php endif;?>

        <div class="row">
            <div class="col-lg-12">

                <?php echo form_open_multipart('admin/dynamic/dupdate/' . $slug . '?domain=' . $domain, array('role' => 'form', 'id' => 'gallery-form')); ?>
                <!-- Hidden elements -->
                <?php echo form_hidden('language', $idioma); ?>
                <?php echo form_hidden('domain', $domain); ?>
                <?php foreach($data_struct as $struct) { ?>
                    <?php if($struct['type']=='hidden' && $struct['name']!='language' && $struct['name']!='domain') { ?>
                        <?php echo form_hidden($struct['name'], isset($data->{$struct['name']})?$data->{$struct['name']}:($struct['default']?$struct['default']:''));?>
                    <?php } ?>
                <?php } ?>

                <div class="row">
                    <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_title')); ?>
                </div>

				<?php if ($this->data['idioma_original'] == $idioma) : ?>
                <div id="gallery-images">
                    <?php foreach ($gallery_images as $image_id => $gallery_image_translations) : ?>
                        <div class="row image-row">

                            <div class="col-md-3">
                                <?php
                                $form_field_name = 'image['. $image_id .']';
                                $objects = [
                                    $form_field_name => [
                                        'label' => 'Imagen',
                                        'value' => isset($gallery_image_translations[$idioma]) ? $gallery_image_translations[$idioma]->url : '',
                                    ]
                                ];
                                echo view('admin/input/image', ['col' => 12, 'name' => $form_field_name, 'objects' => $objects]); ?>
                                <input type="hidden" class="position-input"
                                       name="img_position[<?php echo $image_id; ?>]"
                                       value="<?php echo isset($gallery_image_translations[$idioma]) ? $gallery_image_translations[$idioma]->position : ''; ?>" />
                            </div>

                            <div class="col-md-2">
                                <label>Filename</label>
                                <?php foreach ($languages as $lang) {
                                    if (isset($gallery_image_translations[$lang->id])) {
                                        $old_file_name = explode('/', $gallery_image_translations[$lang->id]->url);
                                        $old_file_name = array_values(array_slice($old_file_name, -1))[0];
                                    } else {
                                        $old_file_name = '';
                                    }

                                    ?>
                                    <div class="row">
                                        <div class="col-xs-1">
                                            <img src="<?php echo base_url('assets/themes/adminlte/flags/blank.png'); ?>"
                                                 class="flag flag-<?php echo(($lang->id == "en") ? "us" : $lang->id) ?>"
                                                 style="margin-top: 10px"
                                            />
                                        </div>
                                        <div class="col-xs-10">
                                            <input type="text" class="form-control"
                                                   name="filename[<?php echo $image_id; ?>][<?php echo $lang->id; ?>]"
                                                   value="<?php echo $old_file_name; ?>" />
                                            <input type="hidden"
                                                   name="old_url[<?php echo $image_id; ?>][<?php echo $lang->id; ?>]"
                                                   value="<?php echo isset($gallery_image_translations[$lang->id]) ? $gallery_image_translations[$lang->id]->url : ''; ?>" />
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="col-md-2">
                                <label>Title</label>
                                <?php foreach ($languages as $lang) { ?>
                                    <input type="text" class="form-control" id=""
                                           name="title[<?php echo $image_id; ?>][<?php echo $lang->id; ?>]"
                                           value="<?php echo isset($gallery_image_translations[$lang->id]) ? $gallery_image_translations[$lang->id]->text_title : ''; ?>" />
                                <?php } ?>
                            </div>

                            <div class="col-md-2">
                                <label>Alt</label>
                                <?php foreach ($languages as $lang) { ?>
                                    <input type="text" class="form-control" id=""
                                           name="alt[<?php echo $image_id; ?>][<?php echo $lang->id; ?>]"
                                           value="<?php echo isset($gallery_image_translations[$lang->id]) ? $gallery_image_translations[$lang->id]->text_alt : ''; ?>" />
                                <?php } ?>
                            </div>
                            <div class="col-md-3">
                                <label>Detalles de la imagen</label>
                                <p title="Pulsar sobre la ID para copiar">ID imagen: <u style="cursor: copy" onclick="copyImageAnchor('##URL_IMG_<?php echo $image_id; ?>##');">##URL_IMG_<?php echo $image_id; ?>##</u></p>
                                <p>Peso de la imagen:
                                    <i><?php echo isset($gallery_image_translations[$idioma]) ? $gallery_image_translations[$idioma]->file_size : ''; ?>kb</i></p>
                                <p>Tamaño de la imagen:
                                    <i><?php echo isset($gallery_image_translations[$idioma]) ? $gallery_image_translations[$idioma]->image_width : ''; ?>px -
                                        <?php echo isset($gallery_image_translations[$idioma]) ? $gallery_image_translations[$idioma]->image_height : ''; ?>px</i></p>
                                <p>Desactivar "width", "height", "title", "alt": &nbsp;
                                    <input type="checkbox" name="seo[<?php echo $image_id; ?>]"
                                        <?php echo (isset($gallery_image_translations[$idioma]) && $gallery_image_translations[$idioma]->is_hide_seo) ? 'checked' : ''; ?> >
                                </p>
                            </div>
                        </div>

                    <?php
                    endforeach;
                    ?>

                    <div class="row image-row new-image">
                        <div class="col-md-3">
                            <?php
                            $form_field_name = 'image[new_1]';
                            $objects = [
                                $form_field_name => [
                                    'label' => 'Nueva imagen',
                                    'value' => '',
                                ]
                            ];
                            echo view('admin/input/image', ['col' => 12, 'name' => $form_field_name, 'objects' => $objects]); ?>
                            <input type="hidden" class="position-input" name="img_position[new_1]" />
                        </div>
                        <div class="col-md-2">
                            <label>Filename</label>
                            <?php
                            foreach ($languages as $lang) {
                                ?><div class="row" style="clear:both">
                                <div class="col-xs-1">
                                    <img src="<?php echo base_url('assets/themes/adminlte/flags/blank.png'); ?>"
                                         class="flag flag-<?php echo(($lang->id == "en") ? "us" : $lang->id) ?>"
                                         style="margin-top: 10px"
                                    />
                                </div>
                                <div class="col-xs-10">
                                    <input type="text" class="form-control" id="" name="filename[new_1][<?php echo $lang->id; ?>]" value=""/>
                                </div></div>
                            <?php } ?>
                        </div>
                        <div class="col-md-2">
                            <label>Title</label>
                            <?php foreach ($languages as $lang) { ?>
                                <input type="text" class="form-control" id="" name="title[new_1][<?php echo $lang->id; ?>]" value=""/>
                            <?php } ?>
                        </div>
                        <div class="col-md-2">
                            <label>Alt</label>
                            <?php foreach ($languages as $lang) { ?>
                                <input type="text" class="form-control" id="" name="alt[new_1][<?php echo $lang->id; ?>]" value=""/>
                            <?php } ?>
                        </div>
                        <div class="col-md-3">
                            <label>Detalles de la imagen</label>
                            <p>Peso de la imagen: <i>aparecerá después de guardar</i></p>
                            <p>Tamaño de la imagen: <i>aparecerá después de guardar</i></p>
                            <p>Desactivar "width", "height", "title", "alt": &nbsp; <input type="checkbox" name="seo[new_1]" ></p>
                        </div>
                    </div>
                    <!-- End #gallery_images -->
                </div>
					
                <div>
                    <div class="row image-row multiple-images" style="display: none; padding: 40px 0px">
                        <div class="col-md-12">
                        <div class="col-md-12">
                            <label>Files</label>
                            
						<?php echo form_input(array(
							'name'=> "multiple-images[]",
							'id' => "multiple-images",
							'type' => 'file',
							'class' => 'form-control file-to-check',
							'multiple' => 'multiple'
						)); ?>
						<p>Peso máximo total <?= ini_get('post_max_size') ?>B</p>
                        </div>
                        </div>
                    </div>
				</div>
				<?php endif; ?>

                <div class="row" style="margin-top:20px;">
                    <div class="col-lg-12">
                        <?php echo form_button(array('id' => 'add-new-image', 'type' => 'button', 'class' => 'btn bg-black btn-flat', 'content' => '<i class="fa fa-plus"></i> Añadir imagen')) ?>
                        <?php echo form_button(array('id' => 'add-multiple-images', 'type' => 'button', 'class' => 'btn bg-black btn-flat', 'content' => '<i class="fa fa-plus"></i> Añadir múltiples imágenes')) ?>
                        <?php echo form_button(array('id' => 'btnordenar', 'type' => 'button', 'class' => 'btn bg-black btn-flat', 'content' => '<i class="fa fa-arrows"></i> Ordenar')) ?>

                        <?php echo form_button(array('name'=>'save_view', 'type'=>'submit', 'class'=>'btn bg-black btn-flat','content' => '<i class="fa fa-save fa-fw"></i> Guardar')); ?>
                        <?php echo form_button(array('name'=>'save_exit', 'type'=>'submit', 'class'=>'btn bg-black btn-flat','content' => '<i class="fa fa-save fa-fw"></i> Guardar y salir')); ?>

                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<style>
    .ui-sortable-handle {
        cursor: move;
    }
    .image-row {
        padding: 10px 0px;
    }
    .image-row:nth-child(odd) {
        background-color:#f5f5f5;
    }
</style>

<script>
    var group;
    $(document).ready(function () {
        $('#add-multiple-images').click(function () {
			$('.multiple-images').toggle ();
		});
        $('#btnordenar').click(function () {
            group = $("#gallery-images").sortable({
                group: 'ordenable',
                delay: 500,
                stop: function (event, ui) {
                    var data = group.sortable("toArray", {attribute: 'data-id'});

                    $('#order_images').val(data.join(','));
                    console.log(data.join(','));
                }
            });
            group.disableSelection();
            alert('Arrastra las filas para ordenar las imágenes y luego pulsa en Guardar para guardar los cambios');
            $(this).hide();
        });

        size_li = $("#ordenable>div").length;
        size_hid = $("#ordenable > div").filter(":hidden").length;
        x = size_li - size_hid;
        $('#ordenable>div:lt(' + x + ')').show();
        $('#loadMore').click(function () {
            x = (x + 1 <= size_li) ? x + 1 : size_li;
            $('#ordenable>div:lt(' + x + ')').show();
            $("html, body").animate({scrollTop: $(document).height() - $(window).height()});
        });

        var newCnt = 0;
        $('#add-new-image').on('click', function() {
            var $newImageRow = $('#gallery-images .new-image:last-child').clone();
            $newImageRow.find('input, textarea, checkbox').val('');
            /* we need to increment fields names new_* */
            $newImageRow.find('input, textarea, checkbox').each(function() {
                var name = $(this).attr('name');
                if (name !== undefined) {
                    var index = parseInt(name.substring(name.indexOf('new_') + 4, name.indexOf(']')));
                    var nextIndex = index + 1;
                    name = name.replace('[new_'+ index +']', '[new_'+ nextIndex +']');
                    $(this).attr('name', name);
                }
            });

            $newImageRow.appendTo('#gallery-images');
            newCnt += 1;
            if (newCnt == 5) {
                $('#add-new-image').hide();
            }
        });

        $('.delete-image-button').on('click', function() {
            var inputName = $(this).data('id');
            $('input[name="'+ inputName +'"]').val('');
        });


        $('.file-to-check').on('change', function(evt) {
            if ((this.files[0].size/1024) > <?php echo $selected_domain_preferences->max_upload_size; ?>) {
                $(this).parents('.form-group').addClass('has-error');
                $(this).next().removeClass('hidden');
            } else {
                $(this).parents('.form-group').removeClass('has-error');
                $(this).next().addClass('hidden');
            }
        });

        $('#gallery-form').on('submit', function(e) {
            /* Check uploaded sizes */
            $('.file-to-check').each(function(i, v) {
                if (this.files[0] && (this.files[0].size/1024) > <?php echo $selected_domain_preferences->max_upload_size; ?>) {
                    $(this).parents('.form-group').addClass('has-error');
                    $(this).next().removeClass('hidden');
                    e.preventDefault();
                    return false;
                }
            });

            /* Fill position inputs */
            $('.position-input').each(function(i, v) {
                $(this).val(i);
            });
        });

    });

    async function copyImageAnchor(text) {
        try {
            await navigator.clipboard.writeText(text);
        } catch (err) {
            console.error('Failed to copy: ', err);
        }
    }
</script>



<?= $this->endSection() ?>