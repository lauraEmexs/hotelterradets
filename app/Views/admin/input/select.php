<div class="col-md-<?php echo $col?>">
    <div class="form-group">
        <?php echo form_label($objects[$name]['label'], $name); ?><br/>
        <?php

        $value = isset($data->{$name}) ? $data->{$name} : '';
        if (isset($objects[$name]['value'])) {
            $value = $objects[$name]['value'];
        }

        if ($value != '' && $value != 0) {
            $selected = $selected_tmp = explode(",", $value);
            $values = [];

            while ($id = array_shift($selected_tmp)) {
                if (isset($objects[$name]['content'][$id])) {
                    $values[$id] = $objects[$name]['content'][$id];
                }
                unset($objects[$name]['content'][$id]);
            }

            $values = $values + $objects[$name]['content'];
        } else {
            $values = ['' => ''] + $objects[$name]['content'];
            $selected = [];
        }

        $related_table = (isset($objects[$name]['related_table'])) ? 'data-related-table="'. $objects[$name]['related_table'] .'"' : '';
        echo form_dropdown($name, $values, $selected,'class="form-control autoselect2" '. $related_table);
        echo form_error($name);

        ?>

        <script>
            $("select[name='<?php echo $name; ?>']:visible").select2({
                placeholder: "Selecciona",
                allowClear: true
            });
        </script>
    </div>
</div>
