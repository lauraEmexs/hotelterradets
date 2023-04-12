<div class="col-md-<?php echo $col?>">
    <div class="form-group">
        <?php

        $value = isset($data->{$name}) ? $data->{$name} : '';
        if (isset($objects[$name]['value'])) {
            $value = $objects[$name]['value'];
        }

        echo form_label($objects[$name]['label'], $name);

        $objects[$name]['default'] = $objects[$name]['default'] ?? '';

        $form_params = [
            'name' => $name,
            'id' => $name,
            'value' => ($value) ? $value : $objects[$name]['default'],
            'type' => 'text',
            'class' => 'form-control',
        ];

        if (strpos($name, 'text_title') !== FALSE) {
            $form_params['required'] = true;
        }

        echo form_input($form_params);

        if (str_contains($name, 'button_url')) {
            echo '<p class="text-muted">Posibles valores: ##MOTOR##, +34933482263, emexs@emexs.es, https://</p>';
        }

        echo form_error($name);

        ?>
    </div>
</div>
