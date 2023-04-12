<?php
    $value = isset($data->{$name}) ? $data->{$name} : '';
    if (isset($objects[$name]['value'])) {
        $value = $objects[$name]['value'];
    }
?>

<div class="col-md-<?php echo $col?>">
    <div class="form-group">
    <?php echo form_label($objects[$name]['label'], $name);?>
    <div class="row">

        <?php if (trim($value) != '' ) { ?>
        <div class="col-md-4">
            <a href="<?php echo base_url();?><?php echo $value;?>" class="image_url">
                <img loading="lazy" class="thumbnail img-responsive" style="display:inline" src="<?php echo base_url();?>/<?php echo $value;?>?<?php echo time(); ?>"
                     id="<?php echo $name; ?>_image"/>
            </a>
        </div>
        <div class="col-md-8">
            <button type="button" style="margin-bottom: 20px;" class="btn bg-black btn-flat delete-image-button" data-id="<?php echo $name; ?>"
                onclick="document.getElementById('<?php echo $name; ?>_hidden').value='xDELETEx';
                                                document.getElementById('<?php echo $name; ?>_image').style='display:none;';
                                                this.style='display:none;';
                                                alert('HECHO. Recuerda guardar los cambios para que tenga efecto');
                                            ">Borrar imagen</button>
			<button type="button" style="margin-bottom: 20px;" class="btn bg-black btn-flat" data-id="<?php echo $name; ?>" data-name="<?php echo $name?>" data-toggle="modal" data-target="#galeria" data-folder="img">Biblioteca de medios</button>
            <?php } else { ?>
                <div class="col-md-12">
			<button type="button" style="margin-bottom: 20px;" class="btn bg-black btn-flat" data-id="<?php echo $name; ?>" data-name="<?php echo $name?>" data-toggle="modal" data-target="#galeria" data-folder="img">Biblioteca de medios</button>
            <?php } ?>
                <?php echo form_input(array(
                    'name' => $name .'_hidden',
                    'id' => $name .'_hidden',
                    'value' => $value,
                    'type'=>'hidden'
                )); ?>
                <?php echo form_input(array(
                    'name'=> $name,
                    'id' => $name,
                    'value' => $value,
                    'type' => 'file',
                    'class' => 'form-control file-to-check'
                )); ?>

                <?php echo form_error($name);?>
                <span class="help-block hidden">Peso máximo excedido</span>
                <p class="help-block">Peso máximo <?php echo $this->selected_domain_preferences->max_upload_size ?? ''; ?>KB</p>

            </div>
        </div>
    </div>
</div>