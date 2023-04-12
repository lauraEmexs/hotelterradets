<div class="col-md-<?php echo $col?>">
    <div class="form-group">
        <?php

        $value = isset($data->{$name}) ? $data->{$name} : '';
        if (isset($objects[$name]['value'])) {
            $value = $objects[$name]['value'];
        }

        echo form_label($objects[$name]['label'], $name);

        $form_params = [
            'name' => $name,
            'id' => $name,
            'value' => ($value) ? $value : $objects[$name]['default'],
            'type' => 'text',
            'class' => 'form-control',
            'autocomplete' => 'off',
        ];

        echo form_input($form_params);

        echo form_error($name);

        ?>
        <script>
            $("input[name='<?php echo $name; ?>']").spectrum({
                type: "component",
                color: "<?= $value ?>"
            });
        </script>
    </div>
</div>
