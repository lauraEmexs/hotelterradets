<?php
$value = isset($data->{$name}) ? $data->{$name} : '';
if (isset($objects[$name]['value'])) {
    $value = $objects[$name]['value'];
}

if ($value && isset($objects[$name]['content'][$value])) {
    $image_url = $objects[$name]['content'][$value]->url;
    echo form_hidden($name .'[id]', $value);
} else {
	$value = null;
}

?>

<div class="col-md-<?php echo $col?>">

    <?php if ($value) { ?>

        <?php echo form_label($objects[$name]['label'], $name);?>
        <div class="row">

            <div class="col-md-4">
                <a href="<?php echo base_url($image_url); ?>" class="image_url">
                    <img loading="lazy" class="thumbnail img-responsive" style="display:inline"
                         src="<?php echo base_url($image_url); ?>?<?php echo time(); ?>"
                         id="<?php echo $name; ?>_image" />
                    <?php echo form_hidden($name .'[url]', $image_url); ?>
                </a>
            </div>
            <div class="col-md-8">

                <div class="row">
                    <div class="col-md-12">
                        <button type="button" style="margin-bottom: 20px;" class="btn bg-black btn-flat delete-image-button" data-id="<?php echo $name; ?>"
                                onclick="document.getElementById('<?php echo $name; ?>_hidden').value='xDELETEx';
                                    document.getElementById('<?php echo $name; ?>_image').style='display:none;';
                                    this.style='display:none;';
                                    alert('HECHO. Recuerda guardar los cambios para que tenga efecto');
                                    ">Borrar imagen</button>
						<button type="button" style="margin-bottom: 20px;" class="btn bg-black btn-flat" data-id="<?php echo $name; ?>" data-name="<?php echo $name?>" data-toggle="modal" data-target="#galeria" data-folder="img">Biblioteca de medios</button>

                        <?php echo form_input(array(
                            'name' => $name .'[delete]',
                            'id' => $name .'_hidden',
                            'value' => '',
                            'type'=>'hidden'
                        )); ?>
                        <?php echo form_input(array(
                            'name'=> $name .'[file]',
                            'id' => $name,
                            'value' => '',
                            'type' => 'file',
                            'class' => 'form-control file-to-check'
                        )); ?>

                        <?php echo form_error($name);?>
                        <span class="help-block hidden">Peso máximo excedido</span>
                        <p class="help-block">Peso máximo <?php echo $selected_domain_preferences->max_upload_size; ?>KB</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php

                            $value = isset($data->{$name}) ? $data->{$name} : '';
                            if (isset($objects[$name]['value'])) {
                                $value = $objects[$name]['value'];
                            }

                            echo form_label($objects[$name]['label'] .' Alt', $name);

                            $form_params = [
                                'name' => $name .'[alt]',
                                'id' => $name,
                                'value' => $objects[$name]['content'][$value]->text_alt,
                                'type' => 'text',
                                'class' => 'form-control',
                            ];

                            echo form_input($form_params);

                            echo form_error($name);

                            ?>
                        </div>
                    </div>
                </div>

                <div class="row"><div class="col-md-12">
                        <div class="form-group">
                            <?php

                            $value = isset($data->{$name}) ? $data->{$name} : '';
                            if (isset($objects[$name]['value'])) {
                                $value = $objects[$name]['value'];
                            }

                            echo form_label($objects[$name]['label'] .' Title', $name);

                            $form_params = [
                                'name' => $name .'[title]',
                                'id' => $name,
                                'value' => $objects[$name]['content'][$value]->text_title,
                                'type' => 'text',
                                'class' => 'form-control',
                            ];

                            echo form_input($form_params);

                            echo form_error($name);

                            ?>

                        </div>
                    </div>
                </div>

            </div>
        </div>

    <?php } else { ?>


        <div class="row">

            <div class="col-md-4">

                <div class="form-group">
                    <?php echo form_label($objects[$name]['label'], $name);?>

                    <?php echo form_input(array(
                        'name' => $name  .'[delete]',
                        'id' => $name .'_hidden',
                        'value' => $value,
                        'type'=>'hidden'
                    )); ?>
                    <?php echo form_input(array(
                        'name'=> $name .'[file]',
                        'id' => $name,
                        'value' => $value,
                        'type' => 'file',
                        'class' => 'form-control file-to-check'
                    )); ?>
					<button type="button" style="margin-bottom: 20px;" class="btn bg-black btn-flat" data-id="<?php echo $name; ?>" data-name="<?php echo $name?>" data-toggle="modal" data-target="#galeria" data-folder="img">Biblioteca de medios</button>

                    <?php echo form_error($name);?>
                    <span class="help-block hidden">Peso máximo excedido</span>
                    <p class="help-block">Peso máximo <?php echo $selected_domain_preferences->max_upload_size; ?>KB</p>

                </div>
            </div>

            <div class="col-md-4">

                <div class="form-group">
                    <?php

                    $value = isset($data->{$name}) ? $data->{$name} : '';
                    if (isset($objects[$name]['value'])) {
                        $value = $objects[$name]['value'];
                    }

                    echo form_label($objects[$name]['label'] .' Alt', $name);

                    $form_params = [
                        'name' => $name .'[alt]',
                        'id' => $name,
                        'value' => $value,
                        'type' => 'text',
                        'class' => 'form-control',
                    ];

                    echo form_input($form_params);

                    echo form_error($name);

                    ?>

                </div>
            </div>

            <div class="col-md-4">

                <div class="form-group">
                    <?php

                    $value = isset($data->{$name}) ? $data->{$name} : '';
                    if (isset($objects[$name]['value'])) {
                        $value = $objects[$name]['value'];
                    }

                    echo form_label($objects[$name]['label'] .' Title', $name);

                    $form_params = [
                        'name' => $name .'[title]',
                        'id' => $name,
                        'value' => $value,
                        'type' => 'text',
                        'class' => 'form-control',
                    ];

                    echo form_input($form_params);

                    echo form_error($name);

                    ?>

                </div>
            </div>

        </div>

    <?php } ?>

</div>
