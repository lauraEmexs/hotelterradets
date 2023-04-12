<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<?php

function prepare_object_for_input ($field_name, $block_params)
{
    $objects = [
        'params['. $field_name .']' => [
            'label' => $field_name,
            'value' => isset($block_params->{$field_name}) ? $block_params->{$field_name} : false,
            'default' => '',
            'content' => [],
        ]
    ];

    return $objects;
}

?>

<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-book fa-fw"></i> <?php echo $title_section;?>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-lg-12">
                <?php echo form_open_multipart('admin/cmsBlock/save_cms_block/'. $id, array('role'=>'form')); ?>

                <h3>Block params</h3>

                <div class="row">
                    <?php
                    if ($id == 'new') {
                        $field_name = 'classname';
                        $objects = [$field_name => [
                            'label' => $field_name,
                            'value' => false,
                            'content' => [],
                        ]];
                        echo view('admin/input/text', ['col' => 4, 'name' => $field_name, 'objects' => $objects]);
                    }
                    ?>
                    <?php
                    $field_name = 'title';
                    $objects = prepare_object_for_input($field_name, $block_params);
                    echo view('admin/input/text', ['col' => 4, 'name' => 'params['. $field_name .']', 'objects' => $objects]);
                    ?>
                    <?php
                    $field_name = 'description';
                    $objects = prepare_object_for_input($field_name, $block_params);
                    echo view('admin/input/text', ['col' => 4, 'name' => 'params['. $field_name .']', 'objects' => $objects]);
                    ?>
                    <?php
                    $field_name = 'repeatable';
                    $objects = prepare_object_for_input($field_name, $block_params);
                    echo view('admin/input/enum', ['col' => 3, 'name' => 'params['. $field_name .']', 'objects' => $objects, 'values' => [false => 'No', true => 'Yes']]);
                    ?>
                    <?php
                    $field_name = 'static';
                    $objects = prepare_object_for_input($field_name, $block_params);
                    echo view('admin/input/enum', ['col' => 3, 'name' => 'params['. $field_name .']', 'objects' => $objects, 'values' => [false => 'No', true => 'Yes']]);
                    ?>
                    <?php
                    $field_name = 'only_last_position';
                    $objects = prepare_object_for_input($field_name, $block_params);
                    echo view('admin/input/enum', ['col' => 3, 'name' => 'params['. $field_name .']', 'objects' => $objects, 'values' => [false => 'No', true => 'Yes']]);
                    ?>
                    <?php
                    $field_name = 'only_zero_position';
                    $objects = prepare_object_for_input($field_name, $block_params);
                    echo view('admin/input/enum', ['col' => 3, 'name' => 'params['. $field_name .']', 'objects' => $objects, 'values' => [false => 'No', true => 'Yes']]);
                    ?>
                    <?php
                    $field_name = 'required';
                    $objects = prepare_object_for_input($field_name, $block_params);
                    echo view('admin/input/enum', ['col' => 3, 'name' => 'params['. $field_name .']', 'objects' => $objects, 'values' => [false => 'No', true => 'Yes']]);
                    ?>

                    <?php
                    $field_name = 'preview';
                    $objects = prepare_object_for_input($field_name, $block_params);
                    echo view('admin/input/image', ['col' => 12, 'name' => $field_name, 'objects' => [$field_name => [
                        'label' => 'preview',
                        'value' => isset($block_params->{$field_name}) ? $block_params->{$field_name} : false,
                    ]]]); ?>

                    <?php
                   // if ($id == 'new') {

                        if ($id == 'new') {
                            $value =
'[
    {"name":"position", "label":"position", "type":"hidden", "default":"0"}
]';
                        } else {
                            $value = $fields;
                        }

                        $field_name = 'fields';
                        $objects = [$field_name => [
                            'label' => $field_name .' json',
                            'value' => $value,
                            'content' => [],
                        ]];
                        echo view('admin/input/htmlarea', ['col' => 12, 'name' => $field_name, 'objects' => $objects]);
                    ?>

                    <div class="col-md-12">
                        <div class="well">
                            <h4>Available fields
                                <button type="button" class="btn btn-success btn-xs redo">Redo</button>
                                <button type="button" class="btn btn-warning btn-xs undo">Undo</button>
                            </h4>
                            <button type="button" class="btn btn-primary field-btn"
                                    data-json='{"name":"description","label":"Descripción","type":"textarea","default":"","cols":"12"}'
                            >Textarea</button>
                            <button type="button" class="btn btn-primary field-btn"
                                    data-json='{"name":"title","label":"Título","type":"text","default":"","cols":"6"}'
                            >Text</button>
                            <button type="button" class="btn btn-primary field-btn"
                                    data-json='{"name":"html","label":"HTML","type":"htmlarea","default":"","cols":"12"}'
                            >Htmlarea</button>
                            <button type="button" class="btn btn-primary field-btn"
                                    data-json='{"name":"","label":"","type":"multiselect","related_table":"dynamic_","default":"","cols":"6"}'
                            >Multiselect</button>
                            <button type="button" class="btn btn-primary field-btn"
                                    data-json='{"name":"","label":"","type":"enum","values":[{"key1":"val1"},{"key2":"val2"}],"default":"key1"}'
                            >Select enum</button>
                            <button type="button" class="btn btn-primary field-btn"
                                    data-json='{"name":"gallery","label":"Galería","type":"select","related_table":"dynamic_gallery","default":"","cols":"6"}'
                            >Select dynamic</button>
                            <button type="button" class="btn btn-primary field-btn"
                                    data-json='{"name":"image","label":"Imagen","type":"image","default":"","cols":"6"}'
                            >Image</button>
                            <button type="button" class="btn btn-primary field-btn"
                                    data-json='{"name":"image","label":"Imagen","type":"cmsimage","default":"","related_table":"dynamic_images"}'
                            >CmsImage</button>
                            <button type="button" class="btn btn-primary field-btn"
                                    data-json='{"name":"file","label":"Subir documento", "type":"document", "default":""}'
                            >Document</button>
                            <button type="button" class="btn btn-primary field-btn"
                                    data-json='{"name":"position", "label":"position", "type":"hidden", "default":"0"}'
                            >Position</button>
                        </div>
                    </div>


                    <div class="col-md-12">
                        <h4 role="button" data-toggle="collapse" href="#collapse-example" aria-expanded="false" aria-controls="collapse-example">Ejemplo (json):</h4>
                        <div class="collapse" id="collapse-example">
                            <pre>
[
   {
      "name":"title",
      "label":"Titulo",
      "type":"text",
      "default":""
   },
   {
      "name":"description",
      "label":"Descripcion",
      "type":"textarea",
      "default":""
   },
    {
      "name":"title_color",
      "label":"Color titulo",
      "type":"enum",
      "values":[
         {
            "white":"Blanco"
         },
         {
            "black":"Negro"
         }
      ],
      "default":"white"
   },
   {
      "name":"position",
      "label":"position",
      "type":"hidden",
      "default":"0"
   }
]
                            </pre>
                        </div>
                    </div>
                    <?php
                    //}
                    ?>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <?php echo form_button(array('name'=>'save', 'id' =>'enviar', 'type'=>'submit', 'class'=>'btn bg-black btn-flat', 'content' =>'<i class="fa fa-save fa-fw"></i> Guardar')); ?>
                        <?php echo ($id != 0) ? form_button(array('name'=>'duplicate', 'type'=>'submit', 'class'=>'btn bg-black btn-flat pull-right', 'onclick' => "return confirm('¿Quieres duplicar esta entrada?')", 'content' => '<i class="fa fa-save fa-fw"></i> Duplicar')) : ''; ?>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let cm = $('.CodeMirror')[0].CodeMirror;
        $(".field-btn").on("click", function() {
            updateCodeMirror(JSON.stringify($(this).data('json')));
        });

        function updateCodeMirror(data){
            console.log(data);
            let doc = cm.getDoc();
            let cursor = doc.getCursor(); // gets the line number in the cursor position
            let line = doc.getLine(cursor.line); // get the line contents
            let pos = { // create a new object to avoid mutation of the original selection
                line: cursor.line,
                ch: line.length // set the character position to the end of the line
            };
            doc.replaceRange(',\n    '+data+'', pos); // adds a new line
        }

        $(".undo").on("click", function() {
            cm.undo();
        });
        $(".redo").on("click", function() {
            cm.redo();
        });
    });
</script>

<?= $this->endSection() ?>