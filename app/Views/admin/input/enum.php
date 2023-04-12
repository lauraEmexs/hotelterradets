<div class="col-md-<?php echo $col?>">
    <div class="form-group">
        <?php
        $value = isset($data->{$name}) ? $data->{$name} : '';
        if (isset($objects[$name]['value'])) {
            $value = $objects[$name]['value'];
        }
        ?>
        <?php
        $label = $label ?? $objects[$name]['label'];
        echo form_label($label, $name);?>
        <?php echo form_dropdown($name, $values,isset($value) ? $value : $objects[$name]['default'],'class="form-control autoselect2"');?>
        <?php echo form_error($name);?>

        <script>
            $("select[name='<?php echo $name; ?>']:visible").select2();
        </script>
    </div>
</div>
