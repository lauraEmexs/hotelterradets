<?php
$value = isset($data->{$name}) ? $data->{$name} : '';
if (isset($objects[$name]['value'])) {
    $value = $objects[$name]['value'];
}

echo form_hidden($name, $value);
