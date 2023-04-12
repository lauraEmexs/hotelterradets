<?php
$value = isset($data->{$name}) ? $data->{$name} : '';
if (isset($objects[$name]['value'])) {
    $value = $objects[$name]['value'];
}
?>

<div class="col-md-<?php echo $col?>">
    <div class="form-group">
        <?php echo form_label($objects[$name]['label'],$name,array('style'=>'display:block'));?>
        <?php if(isset($value) && trim($value) !='') {
            $file_name = explode('/',$value);
            $file_name = $file_name[count($file_name)-1];
        ?>
            <p>Actual documento adjuntado: <b>
                    <a href="<?php echo base_url($value); ?>" target="_blank"><?php echo $file_name; ?></a></b>
            </p>
            <?php echo form_label('¿Borrar?', $name .'_delete');?>
            <?php echo form_checkbox(array('name' => $name .'_delete','id'=>$name .'_delete', 'value'=>'xDELETEx', 'checked'=> false ,'style'=>'margin:10px;') );?>
            <?php echo form_error($name .'_delete');?>
        <?php } ?>
		<button type="button" style="margin-bottom: 20px;" class="btn bg-black btn-flat" data-id="<?php echo $name; ?>" data-name="<?php echo $name?>" data-toggle="modal" data-target="#galeria" data-folder="docs">Biblioteca de medios</button>
        <?php echo form_input(array('name'=>$name.'_hidden','id'=>$name.'_hidden','value' => $value,'type'=>'hidden') );?>
        <?php echo form_input(array('name'=>$name,'id'=>$name,'value'=> $value,'type'=>'file','class'=>'form-control file-to-check') );?>
        <?php echo form_error($name);?>
    </div>
</div>