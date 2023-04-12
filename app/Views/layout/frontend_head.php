<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <?php /* <link rel="shortcut icon" href="<?php echo base_url(THEME_PATH .'img/favicon.png'); ?>" type="image/png"/> */ ?>
    <link rel="shortcut icon" href="<?= base_url($site_preferences->favicon) ?>"/>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>

    <title><?php echo $page->text_page_title; ?></title>

    <meta name="robots" content="<?php echo $page->text_meta_robots; ?>">
    <?php if ($page->text_meta_keywords != '') { ?>
        <meta name="keywords" content="<?php echo $page->text_meta_keywords; ?>">
    <?php } ?>
    <meta name="description" content="<?php echo $page->text_meta_description; ?>">

    <!-- OpenGraph -->
    <meta property="og:type" content="website" />
    <?php if ($site_preferences->og_site_name != '') : ?>
        <meta property="og:site_name" content="<?php echo $site_preferences->og_site_name; ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?php echo $page->text_page_title; ?>"/>
    <?php if (isset($alternate['canonical'])) : ?>
        <meta property="og:url" content="<?php echo $alternate['canonical']; ?>"/>
    <?php endif; ?>

    <?php if (isset($page->image_seo) || isset($site_preferences->og_image)) : ?>
        <meta property="og:image" content="<?= (isset($page->image_seo) && $page->image_seo) ? base_url($page->image_seo) : $site_preferences->og_image; ?>" />
    <?php endif; ?>
    <meta property="og:description" content="<?php echo $page->text_meta_description; ?>">

    <!-- TwitterCard -->
    <meta name="twitter:card" content="summary_large_image">
    <?php if (isset($alternate['canonical'])) : ?><meta name="twitter:url" content="<?php echo $alternate['canonical']; ?>"><?php endif; ?>
    <meta name="twitter:title" content="<?php echo $page->text_page_title; ?>">
    <meta name="twitter:text:description" content="<?php echo $page->text_meta_description; ?>" />
    <?php if (isset($page->image_seo) || isset($site_preferences->og_image)) : ?>
        <meta name="twitter:image:src" content="<?= (isset($page->image_seo) && $page->image_seo) ? base_url($page->image_seo) : $site_preferences->og_image; ?>" />
    <?php endif; ?>

    <!-- Alternates y Hreflang -->
    <?php if (!empty($alternate)) { ?>
        <link href="<?php echo $alternate['canonical']; ?>" rel="canonical" />

        <?php foreach ($alternate['alternate'] as $language_alternate => $url_alternate) { ?>
            <link href="<?php echo $url_alternate; ?>" hreflang="<?php echo $language_alternate; ?>" rel="alternate" />
        <?php } ?>

        <link href="<?php echo $alternate['canonical']; ?>" hreflang="<?php echo \Config\Services::session()->get('site_lang'); ?>" rel="alternate" />
    <?php } ?>

    <?php if ($site_preferences->google_tag_manager) : ?>
        <!-- Google Tag Manager -->
        <script>
            (function (w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({'gtm.start':
                        new Date().getTime(), event: 'gtm.js'});
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', '<?php echo $site_preferences->google_tag_manager; ?>');
        </script>
        <!-- END Google Tag Manager -->
    <?php endif; ?>

    <script type="text/javascript">
        var BASE_URL = "<?php echo base_url(); ?>";
        var THEME_PATH = "<?php echo THEME_PATH; ?>";
        var HOTEL_LAT = "<?php echo $site_preferences->hotel_latitude; ?>";
        var HOTEL_LON = "<?php echo $site_preferences->hotel_longitude; ?>";

        document.addEventListener("DOMContentLoaded", function() {
            var lazyloadMapElems = document.querySelectorAll(".map-lazy");
            var lazyloadThrottleTimeout;

            function lazyloadMap () {
                if(lazyloadThrottleTimeout) {
                    clearTimeout(lazyloadThrottleTimeout);
                }

                lazyloadThrottleTimeout = setTimeout(function() {
                    var scrollTop = window.pageYOffset;
                    lazyloadMapElems.forEach(function(map) {
                        if(map.offsetTop < (window.innerHeight + scrollTop)) {
                            initMap();
                            map.classList.remove('map-lazy');
                        }
                    });

                    if(document.querySelectorAll(".map-lazy").length == 0) {
                        document.removeEventListener("scroll", lazyloadMap);
                        window.removeEventListener("resize", lazyloadMap);
                        window.removeEventListener("orientationChange", lazyloadMap);
                    }
                }, 100);
            }

            document.addEventListener("scroll", lazyloadMap);
            window.addEventListener("resize", lazyloadMap);
            window.addEventListener("orientationChange", lazyloadMap);
        });
    </script>

    <?php if ($site_preferences->scripts_head) {
        echo $site_preferences->scripts_head;
    } ?>

</head>

