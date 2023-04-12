<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-book fa-fw"></i> <?php echo $title_section;?>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-lg-12">
                <?php echo form_open_multipart('admin/cmsBlock/save_static_block/'. $cms_block_id .'?domain='. $domain, array('role'=>'form')); ?>

                <!-- Language Elements -->
                <div class="row">
                    <div class="col-lg-6">
                        <label>Idioma de este bloque</label>
                        <p><img src="<?php echo base_url('assets/themes/adminlte/flags/blank.png'); ?>"
                                class="flag flag-<?php echo ($language=='en') ? 'us' : $language;?>" alt="<?php echo $language;?>" /> <?php echo $language_name;?></p>
                    </div>
                    <?php if ($language != $language_original) { ?>
                        <div class="col-lg-6">
                            <label>Esta es una traducci&oacute;n de:</label>
                            <p><img src="<?php echo base_url('assets/themes/adminlte/flags/blank.png'); ?>"
                                    class="flag flag-<?php echo ($language_original=='en')?'us':$language_original;?>" alt="<?php echo $language_original;?>" /> <?php echo $language_original_name;?></p>
                        </div>
                    <?php } ?>
                </div>

                <?php echo form_hidden('language', $language); ?>
                <?php echo form_hidden('domain', $domain); ?>

                <?php echo $block_form; ?>

                <div class="row">
                    <div class="col-lg-12">
                        <?php echo form_button(array('id' =>'enviar', 'type'=>'submit', 'class'=>'btn bg-black btn-flat', 'content' =>'<i class="fa fa-save fa-fw"></i> Guardar'))?>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

    <script>
        $(document).ready(function() {
            let quick_add = function() {
                console.log(this);
                $(this).next('.quick-add').detach();
                let slug = $(this).data('related-table');
                if (slug == undefined) { return; }
                slug = slug.replace("dynamic_", "");
                let href = '../../../dynamic/dlist/'+ slug +'/';
                $(this).parent().find('label').after('<a href="'+ href +'" target="_blank" class="quick-add" title="Ver contenido"><i class="fa fa-external-link-square fa-fw"></i></a>');
            };
            $("select").each(quick_add);
        });

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
    </script>
<?= $this->endSection() ?>