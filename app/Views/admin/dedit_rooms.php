<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<?php
/**
 * @var $slug
 * @var $domain
 * @var $id
 * @var $idioma
 * @var $data
 */

$with_blocks = config('App')->dynamicWithBlocks;
$with_seo = config('App')->dynamicWithSeo;

$with_dynamic = ['pages'];

$ids_codemirror=array();
$ckeditor_inputs = [];
?>
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
        ul.nav.nav-tabs {
            margin-bottom: 20px;
        }
    </style>

<?php if (in_array($slug, $with_blocks)) : ?>
    <div class="row">
        <div class="col-md-9">
            <?php endif; ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-book fa-fw"></i> <?php echo ($id) ? "Editar" : "Nuevo"; ?> <?php echo $title_section;?>
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
                    <?php $session = \Config\Services::session();
                    if($session->getFlashdata('message')):?>
                        <?php echo $session->getFlashdata('message')?>
                    <?php endif;?>
                    <?php echo form_open_multipart('admin/dynamic/dupdate/'. $slug . $domain_url_params, array('role'=>'form', 'onsubmit'=>'return checkSize();')); ?>
                    <!-- Hidden elements -->
                    <?php echo form_hidden('language', $idioma); ?>
                    <?php echo form_hidden('domain', $domain); ?>
                    <?php echo form_hidden('parent_id', ((is_object($data) && isset($data->parent_id)) ? $data->parent_id : '') ); ?>
                    <?php if (isset($original_language_blocks)) {
                        echo form_hidden('original_language_blocks', true);
                    }?>

                    <?php foreach($data_struct as $struct) { ?>
                        <?php if($struct['type']=='hidden' && $struct['name']!='language' && $struct['name']!='domain') { ?>
                            <?php echo form_hidden($struct['name'], isset($data->{$struct['name']})?$data->{$struct['name']}:''.$struct['default']);?>
                        <?php } ?>
                    <?php } ?>

                    <!-- Language Elements -->
                    <div class="row">
                        <div class="col-lg-3">
                            <label>Idioma de esta p&aacute;gina</label>
                            <p><img src="<?php echo base_url('assets/themes/adminlte/flags/blank.png'); ?>"
                                    class="flag flag-<?php echo ($idioma=='en')?'us':$idioma;?>" alt="<?php echo $idioma;?>" /> <?php echo $idioma_name;?></p>
                        </div>
                        <?php if ($idioma != $idioma_original) { ?>
                            <div class="col-lg-3">
                                <label>Esta es una traducci&oacute;n de:</label>
                                <p><img src="<?php echo base_url('assets/themes/adminlte/flags/blank.png'); ?>"
                                        class="flag flag-<?php echo ($idioma_original=='en')?'us':$idioma_original;?>" alt="<?php echo $idioma_original;?>" /> <?php echo $idioma_original_name;?></p>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Main Elements -->
                    <div class="row">
                        <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_title')); ?>
                        <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_title_menu')); ?>
                        <?php //echo view('admin/input/enum', array('col' => 2, 'name' => 'show_in_menu', 'values' => [0 => 'No', 1 => 'Si'])); ?>
                        <?php //echo view('admin/input/enum', array('col' => 2, 'name' => 'status', 'values' => ['PAUSED' => 'Pausado', 'ACTIVED' => 'Activo'])); ?>
                        <?php
                        if (isset($data->text_full_url)) {
                            if ($data->text_full_url != '') {
                                $data->type = 'url';
                            } else {
                                $data->type = 'page';
                            }
                        }
                        if (in_array($slug, $with_dynamic)) {
                            echo view('admin/input/enum', array('col' => 2, 'name' => 'type', 'label' => 'Tipo', 'values' => ['page' => 'Pagina', 'url' => 'Menu link',]));
                        }

                        ?>
                        <?php /*echo view('admin/input/enum', array('col' => 4, 'name' => 'parent_id', 'values' => $all_pages));*/ ?>

                    </div>

                    <?php if (in_array($slug, $with_dynamic)) : ?>
                        <div class="row" id="full-url" style="display: none;">
                            <?php echo view('admin/input/text', array('col' => 9, 'name' => 'text_full_url')); ?>
                        </div>
                    <?php endif; ?>

                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tabMain" data-toggle="tab">Campos contenido</a></li>
                        <?php if (in_array($slug, $with_blocks)) : ?>
                            <li><a href="#tabBloques" data-toggle="tab">CMS Bloques</a></li>
                        <?php endif;?>
                        <?php if (in_array($slug, $with_seo)) : ?>
                            <li><a href="#tabSeo" data-toggle="tab">SEO</a></li>
                        <?php endif;?>
                        <?php if (in_array($slug, $with_dynamic) && $logged_user->group == 'admin') : ?>
                            <li><a href="#tabDynamic" data-toggle="tab">Contenido dinámico</a></li>
                        <?php endif;?>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="tabMain">
                            <!-- Others elements -->
                            <div class="row">
                                <?php echo view('admin/input/select', array('col' => 4, 'name' => 'enum_content_type')); ?>
                                <?php echo view('admin/input/text', array('col' => 8, 'name' => 'text_video')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/text', array('col' => 8, 'name' => 'text_title_web')); ?>
                                <?php echo view('admin/input/select', array('col' => 4, 'name' => 'enum_title_tag')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/text', array('col' => 8, 'name' => 'text_titlesection')); ?>
                                <?php echo view('admin/input/select', array('col' => 4, 'name' => 'enum_titlesection_tag')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/textarea', array('col' => 6, 'name' => 'textarea_description')); ?>
                                <?php echo view('admin/input/textarea', array('col' => 6, 'name' => 'textarea_description_2')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/image', array('col' =>6, 'name' => 'image_principal')); ?>
                                <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_image_alt')); ?>
                                <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_image_title')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/image', array('col' =>6, 'name' => 'image_principal_2')); ?>
                                <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_image_alt_2')); ?>
                                <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_image_title_2')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/enum', array('col' => '4 button-type-selector', 'name' => 'enum_button_type_1', 'values' => ['external' => 'Página externa', 'page' => 'Página', 'pdf' => 'PDF', 'reserve' => 'Reserva', 'email' => 'Email', 'tel' => 'Teléfono'])); ?>
                                <?php echo view('admin/input/text', array('col' => '4', 'name' => 'text_button_title_1')); ?>
                                <?php echo view('admin/input/text', array('col' => '4 external email tel reserve', 'name' => 'text_button_url_1')); ?>
                                <?php echo view('admin/input/select', array('col' => '4 page', 'name' => 'dynamic_pages_1')); ?>
                                <?php echo view('admin/input/document', array('col' => '4 pdf', 'name' => 'document_sheet_1')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/enum', array('col' => '4 button-type-selector', 'name' => 'enum_button_type_3', 'values' => ['external' => 'Página externa', 'page' => 'Página', 'pdf' => 'PDF', 'reserve' => 'Reserva', 'email' => 'Email', 'tel' => 'Teléfono'])); ?>
                                <?php echo view('admin/input/text', array('col' => '4', 'name' => 'text_button_title_3')); ?>
                                <?php echo view('admin/input/text', array('col' => '4 external email tel reserve', 'name' => 'text_button_url_3')); ?>
                                <?php echo view('admin/input/select', array('col' => '4 page', 'name' => 'dynamic_pages_3')); ?>
                                <?php echo view('admin/input/document', array('col' => '4 pdf', 'name' => 'document_sheet_3')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/textarea', array('col' => 12, 'name' => 'textarea_description_5')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/multiselect', array('col' => 12, 'name' => 'multiple_roomservices')); ?>
                            </div>
                            <h3>Texto y dos imágenes</h3>
                            <div class="row">
                                <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_titlesection_2')); ?>
                                <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_title_2')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/textarea', array('col' => 6, 'name' => 'textarea_description_3')); ?>
                                <?php echo view('admin/input/textarea', array('col' => 6, 'name' => 'textarea_description_4')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/enum', array('col' => '4 button-type-selector', 'name' => 'enum_button_type_4', 'values' => ['external' => 'Página externa', 'page' => 'Página', 'pdf' => 'PDF', 'reserve' => 'Reserva', 'email' => 'Email', 'tel' => 'Teléfono'])); ?>
                                <?php echo view('admin/input/text', array('col' => '4', 'name' => 'text_button_title_4')); ?>
                                <?php echo view('admin/input/text', array('col' => '4 external email tel reserve', 'name' => 'text_button_url_4')); ?>
                                <?php echo view('admin/input/select', array('col' => '4 page', 'name' => 'dynamic_pages_4')); ?>
                                <?php echo view('admin/input/document', array('col' => '4 pdf', 'name' => 'document_sheet_4')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/enum', array('col' => '4 button-type-selector', 'name' => 'enum_button_type_5', 'values' => ['external' => 'Página externa', 'page' => 'Página', 'pdf' => 'PDF', 'reserve' => 'Reserva', 'email' => 'Email', 'tel' => 'Teléfono'])); ?>
                                <?php echo view('admin/input/text', array('col' => '4', 'name' => 'text_button_title_5')); ?>
                                <?php echo view('admin/input/text', array('col' => '4 external email tel reserve', 'name' => 'text_button_url_5')); ?>
                                <?php echo view('admin/input/select', array('col' => '4 page', 'name' => 'dynamic_pages_5')); ?>
                                <?php echo view('admin/input/document', array('col' => '4 pdf', 'name' => 'document_sheet_5')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/image', array('col' =>6, 'name' => 'image_principal_3')); ?>
                                <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_image_alt_3')); ?>
                                <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_image_title_3')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/image', array('col' =>6, 'name' => 'image_principal_4')); ?>
                                <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_image_alt_4')); ?>
                                <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_image_title_4')); ?>
                            </div>
                            <div class="row">
                                <?php echo view('admin/input/select', array('col' => 12, 'name' => 'dynamic_gallery')); ?>
                            </div>
                        </div>
                        <?php if (in_array($slug, $with_blocks)) : ?>
                            <div class="tab-pane fade" id="tabBloques">
                                <div class="page-parts">
                                    <h4>Bloques asignados:</h4>
                                    <div class="assigned-blocks">
                                        <?php
                                        foreach ($blocks as $block) {
                                            echo $block;
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif;?>
                        <?php if (in_array($slug, $with_seo)) : ?>
                            <div class="tab-pane fade" id="tabSeo">
                                <div class="page-parts">
                                    <?php echo view('admin/input/text', array('col' => 6, 'name' => 'slug')); ?>
                                    <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_page_title')); ?>
                                    <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_meta_description')); ?>
                                    <?php /* echo view('admin/input/text', array('col' => 6, 'name' => 'text_meta_keywords')); */ ?>
                                    <?php echo view('admin/input/text', array('col' => 6, 'name' => 'text_meta_robots')); ?>
                                    <?php echo view('admin/input/image', array('col' => 6, 'name' => 'image_seo')); ?>

                                    <?php
                                    $page_url = base_url();
                                    $page_url .= (!is_object($data) || $data->language == $idioma_original) ? '' : $data->language .'/';
                                    echo form_hidden('hidden_page_url', $page_url); ?>
                                    <div class="col-md-12">
                                        <h4>Google Preview</h4>
                                        <div id="seopreview-google"></div>
                                    </div>

                                    <?php echo view('admin/input/htmlarea', array('col' => 12, 'name' => 'textarea_custom_html')); ?>
                                </div>
                            </div>
                        <?php endif;?>
                        <?php if (in_array($slug, $with_dynamic)) : ?>
                            <div class="tab-pane fade" id="tabDynamic">
                                <?php echo view('admin/input/enum', array('col' => 2, 'label' => null, 'name' => 'container_of', 'values' => $dynamic_pages_tables)); ?>
                                <?php echo view('admin/input/enum', array('col' => 2, 'label' => null, 'name' => 'representative_of', 'values' => $dynamic_pages_tables)); ?>
                            </div>
                        <?php endif;?>

                    </div>

                    <br><br>

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

        <?php if (in_array($slug, $with_blocks)) : ?>
        <div class="col-md-3 page-parts">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-book fa-th-large"></i> CMS Bloques
                </div>
                <div class="panel-body new-blocks">

                    <?php
                    foreach ($new_blocks as $block) {
                        echo $block;
                    } ?>

                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();

            $('#seo-header').click(function () {
                $("#seo-section").slideToggle();
                var editor = $("#seo-section").find(".CodeMirror")[0];
                if ($("#seo-section").find(".CodeMirror")[0]) {
                    editor = editor.CodeMirror
                    setTimeout(function () {
                        editor.refresh();
                    }, 500);
                }
            });

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var target = $(e.target).attr("href");
                var editor = $(target).find(".CodeMirror")[0];
                if ($(target).find(".CodeMirror")[0]) {
                    editor = editor.CodeMirror
                    setTimeout(function () {
                        editor.refresh();
                    }, 500);
                }
            })

            $('#dynamic-header').click(function () {
                $("#dynamic-section").slideToggle();
            });

            function initialize_statustoggle(selector) {
                if (selector === undefined) {
                    selector = '.assigned-blocks .status-toggle';
                }
                $(selector).bootstrapToggle();
            }
            initialize_statustoggle();
            function initialize_select2(selector) {
                if (selector === undefined) {
                    selector = '.assigned-blocks .autoselect2, .autoselect2.select2';
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

            $('.assigned-blocks').on('click', '.block-move-up', function (e) {
                var block_id = $(this).parents('.cms-block').attr('id');
                $('#'+ block_id +' .box').boxWidget('collapse');
                var tmp = block_id.split('_');
                var position_input_name = tmp[0] + "[" + tmp[1] + "][position]";
                var actual_position = $("#"+ block_id).find('input[name*="position"]').val();

                //var new_position = parseInt(actual_position) - 1;
                var new_position = $("#"+ block_id).prev().find('input[name*="position"]').val();
                $("#"+ block_id).find('input[name*="position"]').val(new_position);

                $('.assigned-blocks .cms-block').each(function () {
                    var position_input_element = $(this).find('input[name*="position"]');
                    if ($(position_input_element).val() == new_position && $(position_input_element).attr('name') !== position_input_name) {
                        var val = $(position_input_element).val();
                        var new_val = parseInt(val) + 1;
                        $(position_input_element).val(new_val);
                    }
                });
                $("#"+ block_id).prev().insertAfter($("#"+ block_id));
                check_position();
            });

            $('.assigned-blocks').on('click', '.block-move-down', function (e) {
                var block_id = $(this).parents('.cms-block').attr('id');
                $('#'+ block_id +' .box').boxWidget('collapse');
                var tmp = block_id.split('_');
                var position_input_name = tmp[0] + "[" + tmp[1] + "][position]";
                var actual_position = $("#"+ block_id).find('input[name*="position"]').val();
                //var new_position = parseInt(actual_position) + 1;
                var new_position = $("#"+ block_id).next().find('input[name*="position"]').val();
                $("#"+ block_id).find('input[name*="position"]').val(new_position);

                $('.assigned-blocks .cms-block').each(function () {
                    var position_input_element = $(this).find('input[name*="position"]');
                    if ($(position_input_element).val() == new_position && $(position_input_element).attr('name') !== position_input_name) {
                        var val = $(position_input_element).val();
                        var new_val = parseInt(val) - 1;
                        $(position_input_element).val(new_val);
                    }
                });

                $("#"+ block_id).next().insertBefore($("#"+ block_id));
                check_position();
            });

            /* Assign new block */
            $('.block-append').on('click', function (e) {
                var block_id = $(this).data('block-id');
                var cms_block_id = $(this).parents('.cms-block').data('cmsBlockId');

                //$('#'+ block_id +' .box').boxWidget('collapse');
                $('.new-blocks [data-cms-block-id="'+ cms_block_id +'"] .box').boxWidget('collapse');

                var last_block_position = $('.assigned-blocks .cms-block:last-child').find('input[name*="position"]').val();

                //Move dummy block to list of used blocks
                //var $new_block = $('#'+ block_id).clone();
                var $new_block = $('.new-blocks [data-cms-block-id="'+ cms_block_id +'"]').clone();
                var block_index = $('.assigned-blocks .cms-block').length + 1;
                $new_block.attr('id', block_id + '_'+ block_index);
                var new_block_id = $new_block.attr('id');
                $new_block.appendTo($('.assigned-blocks'));

                $('#'+ new_block_id).find('.admin-form').show();
                $('#'+ new_block_id +' .box').boxWidget('expand');

                $('html, body').animate({
                    scrollTop: $('#'+ new_block_id).offset().top - 50
                }, 1000);

                //We need to rename every input from "new" to "new_*"
                $('#'+ new_block_id +' label, #'+ new_block_id +' [name]').each(function() {
                    var label = $(this).attr('for');
                    if (label !== undefined) {
                        label = label.replace('[new]', '[new_' + block_index + ']');
                        $(this).attr('for', label);
                    }

                    var name = $(this).attr('name');
                    if (name !== undefined) {
                        name = name.replace('[new]', '[new_'+ block_index +']');
                        $(this).attr('name', name);
                    }
                });

                var tmp = new_block_id.split('_');
                var position_input_name = tmp[0] + "[new_" + block_index + "][position]";
                var add_input_name = tmp[0] + "[new_" + block_index + "][add]";

                //We add this hidden to block to be saved, because it can be without any fields
                $('<input type="hidden" name="'+ add_input_name +'">').appendTo($('#'+ new_block_id));

                //If block is not repeatable
                if ($('#'+ block_id).hasClass('no_repeatable')) {
                    //$('#'+ block_id).hide();
                    var cms_block_class = $('#'+ block_id).data('cmsBlockClass');
                    $('.new-blocks .'+ cms_block_class).hide();
                }

                //Assign position parameter.
                var new_position = parseInt(last_block_position) + 1;
                if (isNaN(new_position)) new_position = 0;
                //If position != 0 and this block is "only_zero_position", we need to move down every
                if (new_position != 0 && $('#'+ new_block_id).hasClass('only_zero_position')) {
                    while (new_position > 0) {
                        new_position = new_position - 1;
                        var prev_block_elem = $('#'+ new_block_id).prev().find('input[name*="position"]');
                        if ($(prev_block_elem).val() == new_position) {
                            var val = $(prev_block_elem).val();
                            var new_val = parseInt(val) + 1;
                            $(prev_block_elem).val(new_val);
                            $("#"+ new_block_id).prev().insertAfter($("#"+ new_block_id));
                        }
                    }
                }
                //Check the "only_last_position"
                if (new_position != 0 && $('#'+ new_block_id).prev().hasClass('only_last_position')) {
                    new_position = new_position - 1;
                    var prev_block_elem = $('#'+ new_block_id).prev().find('input[name*="position"]');
                    if ($(prev_block_elem).val() == new_position) {
                        var val = $(prev_block_elem).val();
                        var new_val = parseInt(val) + 1;
                        $(prev_block_elem).val(new_val);
                        $("#"+ new_block_id).prev().insertAfter($("#"+ new_block_id));
                    }
                }

                $('[name="'+ position_input_name +'"]').val(new_position);
                //document.getElementById(position_input_name).value = new_position;
                //End assignation position

                check_position();
                initialize_select2('#'+ new_block_id +' .autoselect2');

                //Set codemirror
                $("#"+ new_block_id).find('[name*="html"]').each(function() {
                    random_id = 'id' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                    $(this).attr('id', random_id);
                    random_id = CodeMirror.fromTextArea(document.getElementById(random_id), {
                        lineNumbers: true,
                        matchBrackets: true,
                        mode: "text/html",
                        indentUnit: 2,
                        indentWithTabs: true,
                        enterMode: "keep",
                        tabMode: "shift",
                    });
                });

                //Set CKEDITOR
                $("#"+ new_block_id).find('textarea').each(function() {
                    if ($(this).attr('id') == undefined) { return; }
                    if ($(this).attr('name') != undefined && $(this).attr('name').includes('html')) { return; }
                    var ckeditor_id = $(this).attr('id');
                    if (CKEDITOR.instances[ckeditor_id] !== 'undefined') {
                        CKEDITOR.replace(ckeditor_id, {
                            language: '<?php echo config('App')->defaultLangGestor; ?>',
                            stylesSet: 'my_styles:<?php echo base_url(); ?>/assets/ckeditor/my_styles.js',
                            customConfig: '<?php echo base_url(); ?>/assets/ckeditor/my_config.js'
                        });
                    }
                });

                //Set status toggle
                initialize_statustoggle($('#'+ new_block_id +' .status-toggle'));
                initialize_button_type_switch();
            });
            //End assign new block

            /* Delete block */
            $('.assigned-blocks').on('click', '.block-delete', function (e) {
                if (!confirm('¿Quieres eliminar este bloque?')) {
                    return;
                }
                var block_id = $(this).parents('.cms-block').attr('id');

                //If block is not repeatable we need to show of same class in new blocks
                if ($('#'+ block_id).hasClass('no_repeatable')) {
                    var cms_block_class = $('#'+ block_id).data('cmsBlockClass');
                    $('.new-blocks .'+ cms_block_class).show();
                }

                if (block_id.indexOf("new") >= 0) {
                    $('#'+ block_id).detach();
                    return false;
                }
                $('#'+ block_id +' .box').boxWidget('collapse');
                $(this).parents('.cms-block').hide();
                var tmp = block_id.split('_');
                var delete_input_name = tmp[0] + "[" + tmp[1] + "][delete]";

                $('<input type="hidden" name="'+ delete_input_name +'">').appendTo($('#'+ block_id));

                check_position();
            });

            /* Check and hide arrows if block in border position */
            function check_position() {
                var previous_block_id;
                var length = $('.assigned-blocks .cms-block:visible').length;
                $('.assigned-blocks .cms-block:visible').each(function (i) {
                    //If this element is first or after "only sero"
                    if ($('#'+ previous_block_id).hasClass('only_zero_position') || previous_block_id === undefined) {
                        $(this).find('.block-move-up').hide();
                    } else {
                        $(this).find('.block-move-up').show();
                    }

                    if ($(this).hasClass('only_last_position')) {
                        $('#'+ previous_block_id).find('.block-move-down').hide();
                    } else {
                        $('#'+ previous_block_id).find('.block-move-down').show();
                    }

                    //If this element is last
                    if (i + 1 === length) {
                        $(this).find('.block-move-down').hide();
                    }

                    previous_block_id = $(this).attr('id');

                    //Fix position value
                    $(this).find('input[name*="position"]').val(i);
                });
            }
            check_position();

            function check_repeateable() {
                $('.assigned-blocks .cms-block:visible').each(function() {
                    if ($(this).hasClass('no_repeatable')) {
                        var cms_block_class = $(this).data('cmsBlockClass');
                        $('.new-blocks .'+ cms_block_class).hide();
                    }
                });
            }
            check_repeateable();

            $(".box").on("expanded.boxwidget", function() {
                $(this).find(".CodeMirror").each(function() {
                    var editor = this.CodeMirror;
                    setTimeout(function () {
                        editor.refresh();
                    }, 300);
                });
            });

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

            /* Universal add button for dynamic content */
            let quick_add = function() {
                let slug = $(this).attr('name');
                if (slug.includes("multiple_") || slug.includes("dynamic_")) {
                    if ($(this).attr('multiple') === 'multiple') {
                        slug = slug.replace("multiple_", "");
                        slug = slug.replace("[]", "");
                    } else {
                        slug = slug.replace("dynamic_", "");
                    }
                    let href = '../../../dlist/' + slug.split('_')[0] + '/<?= $domain_url_params ?>';
                    $(this).next('.quick-add').detach();
                    $(this).parent().find('label').after('<a href="' + href + '" target="_blank" class="quick-add" title="Ver contenido"><i class="fa fa-external-link-square  fa-fw"></i></a>');
                }
            };
            $("select").each(quick_add);
            /* end */
        });

        function checkSize(){
            var peso = $('.file-to-check')[0].files[0].size;
            if(peso > <?php echo $selected_domain_preferences->max_upload_size; ?>000){
                alert("Imagen demasiado grande, tamaño máximo: <?php echo $selected_domain_preferences->max_upload_size; ?>KB");
                return false;
            }
            return true;
        }


        <?php foreach($ids_codemirror as $id) { ?>
        get_editor('<?php echo $id;?>');
        <?php } ?>
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

        <?php if (in_array($slug, $with_dynamic)) : ?>
        $.seoPreview({
            google_div: "#seopreview-google",
            //facebook_div: "#seopreview-facebook",
            metadata: {
                title: $('[name="text_page_title"]'),
                desc: $('[name="text_meta_description"]'),
                url: {
                    full_url: "<?= $preview_link ?>"/*$('[name="slug"]#slug')*/,
                    use_slug: true,
                    base_domain: $('[name="hidden_page_url"]').val(),
                    auto_dash: true
                }
            },
            google: {
                show: true,
                date: false
            },
            facebook: {
                show: false,
                featured_image: ""
            }
        });
        <?php endif; ?>

        $('select[name="type"]').on('change', function() {
            init_page_type();
        });

        function init_page_type() {
            $type = $('select[name="type"] option:selected').val();
            <?php if (!in_array($slug, $with_dynamic)) : ?>
            $type = 'page';
            <?php endif; ?>
            if ($type == 'page') {
                $('input[name="text_full_url"]').val('');
                $('.page-parts').show();
                $('#full-url').hide();
            } else {
                $('.page-parts').hide();
                $('#full-url').show();
            }

        }
        init_page_type();


        /*!TODO FIX FOR NEW ADDED BLOCK! */

        /* Switch for button multitype */
        initialize_button_type_switch();
        function initialize_button_type_switch() {
            $types_selector = '.button-type-video, .button-type-slider, .button-type-image, .button-type-document, ' +
                '.button-type-link, .button-type-external, .button-type-anchor, .button-type-modal, .selectable, .page, .pdf, .external, .reserve, .tel, .email';
            $($types_selector).hide();
            $(".button-type-selector select").each(function() {
                $(this).closest('.row').find('.' + this.value).show();
                $(this).closest('.row').find('.button-type-selector-2 select').attr('data-selector', this.value);
            });
            $('.button-type-selector select').on('change', function() {
                $(this).closest('.row').find($types_selector).hide();
                $(this).closest('.row').find(".pdf").hide();
                $(this).closest('.row').find(".page").hide();
                $(this).closest('.row').find(".external").hide();
                $(this).closest('.row').find(".tel").hide();
                $(this).closest('.row').find(".email").hide();
                $(this).closest('.row').find(".reserve").hide();
                $(this).closest('.row').find('.' + this.value).show();
                $(this).closest('.row').find('.button-type-selector-2 select').attr('data-selector', this.value);
            });
        }

        /**
         * Inserted select
         */
        initialize_button_type_switch_2();
        function initialize_button_type_switch_2() {
            $types_selector = '.selectable';
            $(".button-type-selector-2 select").each(function() {
                $(this).closest('.row').find($types_selector).hide()
            });
            $(".button-type-selector-2 select").each(function() {
                $(this).closest('.row').find('.'+ $(this).data('selector') + '.' + this.value).show();
            });
            $('.button-type-selector-2 select').on('change', function() {
                $(this).closest('.row').find($types_selector).hide();
                $(this).closest('.row').find('.'+ $(this).data('selector') + '.' + this.value).show();
            });
        }
    </script>

<?= $this->endSection() ?>