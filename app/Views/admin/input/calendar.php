<div class="col-md-<?php echo $col?>">
    <div class="form-group">
        <?php

        $value = isset($data->{$name}) ? $data->{$name} : '';
        if (isset($objects[$name]['value'])) {
            $value = $objects[$name]['value'];
        }

        if ($value) {
            $value = date("d/m/Y", strtotime($value));
        }

        echo form_label($objects[$name]['label'], $name);

        $form_params = [
            'name' => $name,
            'id' => $name,
            'value' => $value,
            'type' => 'text',
            'class' => 'form-control',
            'autocomplete' => 'off',
        ];

        echo form_input($form_params);

        echo form_error($name);

        ?>

        <script>
            $("input[name='<?php echo $name; ?>']:visible").datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true
            });
        </script>
    </div>
</div>
