<div class="col-lg-<?php echo $col?>">
    <div class="form-group">
        <?php
        $value = isset($data->{$name}) ? $data->{$name} : '';
        if (isset($objects[$name]['value'])) {
            $value = $objects[$name]['value'];
        }
        $id = random_string('alpha', 16);
        ?>
        <?php echo form_label($objects[$name]['label'],$name);?>
        <?php echo form_textarea(array('name'=>$name,'id'=>$id,'value'=>$value,'class'=>'form-control') );?>
        <!--<span>Urls especiales: Páginas estáticas: ##URL_PAG_ID##, Galerías: ##URL_IMG_ID##, ##MOTOR##</span>-->
    </div>
</div>

<script>
    if ($("#<?php echo $id; ?>").parents(".new-blocks").length === 0) {
        CKEDITOR.replace( '<?php echo $id; ?>', {
            language: '<?php echo config('App')->defaultLangGestor; ?>',
            stylesSet: 'my_styles:<?php echo base_url(); ?>/assets/ckeditor/my_styles.js',
            customConfig: '<?php echo base_url(); ?>/assets/ckeditor/my_config.js'
        });
    }
</script>
