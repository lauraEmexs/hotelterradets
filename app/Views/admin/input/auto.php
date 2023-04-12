<div class="col-md-<?php echo $col?>">
    <div class="form-group">
        <?php echo form_label($objects[$name]['label'],$name);?>
        <?php $data_input=array('name'=>$name,'id'=>$name,'value'=>isset($data->{$name})?$data->{$name}:'','type'=>'text','class'=>'form-control'); ?>
        <?php if($name=='auto_slug') $data_input['readonly']=true; ?>
        <?php echo form_input($data_input);?>
        <?php echo form_error($name);?>
    </div>
</div>