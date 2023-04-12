<div class="col-lg-12">
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
        <?php echo $id; ?> = CodeMirror.fromTextArea(document.getElementById("<?php echo $id; ?>"), {
            lineNumbers: true,
            matchBrackets: true,
            mode: "text/html",
            indentUnit: 2,
            indentWithTabs: true,
            enterMode: "keep",
            tabMode: "shift",
        });
    }
</script>
