<?php
/**
 * @var $url
 * @var $alt
 * @var $title
 * @var $custom_attribute
 * @var $class
 * @var $desktop
 * @var $tablet
 * @var $mobile
 */

$desktop = !empty($desktop) ? $desktop : 'high';
$tablet = !empty($tablet) ? $tablet : 'medium';
$mobile = !empty($mobile) ? $mobile : 'low';
?>

<picture>
    <?php if (in_array(pathinfo($url)['extension'], ['png', 'jpg', 'jpeg'])
        && file_exists(FCPATH . replace_extension($url, 'webp'))): ?>
        <source data-srcset="<?= image_url(replace_extension($url, 'webp'),
            $desktop, $tablet, $mobile) ?>" srcset="<?= image_url(replace_extension($url, 'webp'),
            $desktop, $tablet, $mobile) ?>" type="image/webp">
    <?php endif; ?>
    <img data-src="<?= in_array(pathinfo($url)['extension'], ['png', 'jpg', 'jpeg']) && file_exists(FCPATH . replace_extension($url, 'webp')) ? image_url(replace_extension($url, 'webp'), $desktop, $tablet, $mobile) : image_url($url, $desktop, $tablet, $mobile) ?>"
         class="<?= $class; ?>" <?= $custom_attribute ?>
         alt="<?= $alt ?? '' ?>"
         title="<?= $title ?? '' ?>">
</picture>