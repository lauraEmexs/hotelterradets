<!doctype html>
<html lang="<?php echo \Config\Services::session()->get('locale_lang'); ?>">

<?php echo view('layout/frontend_head'); ?>

<body>
<!--<div id="preloader"></div>

        <div id="barraaceptacion" style="display: block;">
            <div class="inner">
                <a href="javascript:void(0);" class="close-cookies" onclick="PonerCookie();">X</a>
                <p><?php echo lang('theme_cookie_info'); ?></p>
            </div>
        </div>
        -->

<?php if ($site_preferences->google_tag_manager) : ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $site_preferences->google_tag_manager; ?>"
                      height="0" width="0" style="display:none; visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
<?php endif; ?>

<?= $this->renderSection('content') ?>


<?php if (isset($page->custom_html)) {
    echo $page->custom_html;
} ?>

<!-- CSS -->
<noscript id="deferred-styles">
    <link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(THEME_PATH .'css/flickity.min.css'); ?>">

    <?php if (isset($site_preferences->custom_css) && $site_preferences->custom_css != '') :
        foreach (explode(';', $site_preferences->custom_css) as $item) : ?>
            <link rel="stylesheet" type="text/css" href="<?php echo base_url(THEME_PATH.'css/'.$item); ?>"/>
        <?php endforeach;
    endif; ?>
</noscript>
<script>
    var loadDeferredStyles = function() {
        var addStylesNode = document.getElementById("deferred-styles");
        var replacement = document.createElement("div");
        replacement.innerHTML = addStylesNode.textContent;
        document.body.appendChild(replacement)
        addStylesNode.parentElement.removeChild(addStylesNode);
    };
    var raf = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
        window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
    if (raf) raf(function() { window.setTimeout(loadDeferredStyles, 10); });
    else window.addEventListener('load', loadDeferredStyles);
</script>

<!-- JS -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="<?php echo base_url(THEME_PATH .'js/bootstrap.min.js'); ?>" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.3/moment.min.js" defer></script>
<!--<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>-->
<script src="<?php echo base_url(THEME_PATH .'js/lazyload.min.js'); ?>" defer></script>
<script src="<?php echo base_url(THEME_PATH .'js/flickity.pkgd.min.js'); ?>" defer></script>
<?php if ($site_lang != 'en') : ?>
    <script src="<?php echo base_url(THEME_PATH); ?>js/datepicker-<?php echo $site_lang; ?>.js" async defer></script>
<?php endif; ?>
<script src="<?php echo base_url(THEME_PATH .'js/custom.js?0'); ?>" defer></script>

<?php if (isset($site_preferences->custom_js) && $site_preferences->custom_js != '') :
    foreach (explode(';', $site_preferences->custom_js) as $item) : ?>
        <script src="<?php echo base_url(THEME_PATH. 'js/'.$item); ?>" async defer></script>
    <?php endforeach;
endif; ?>

<?php if ($site_preferences->scripts_footer) {
    echo $site_preferences->scripts_footer;
} ?>
<?php if ($site_preferences->gmap_api_key) : ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $site_preferences->gmap_api_key; ?>&callback=initMap" async defer></script>
<?php endif; ?>

<?php
if ($site_preferences->recaptcha_public_key) {
    if ($site_preferences->recaptcha_version == 3) {
        echo '<script src="https://www.google.com/recaptcha/api.js?render='. $site_preferences->recaptcha_public_key .'" async defer></script>';
    } else {
        echo '<script src="https://www.google.com/recaptcha/api.js?onload=CaptchaCallback&render=explicit" async defer></script>';
    }
}
?>

<script>
    let captchaReady = false;
    function CaptchaCallback() {
        captchaReady = true;
    }
</script>
</body>

<?php
/*todo delete this*/
if((\Config\Services::session()->get('userdata')) && \Config\Services::session()->get('userdata')->group == 'admin') {
    echo '<script>';
    //echo 'console.log('. json_encode( $_ci_cached_vars ) .');';
    echo '</script>';
}
?>
</html>
