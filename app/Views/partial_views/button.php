<?php
/**
 * @var $type
 * @var $title
 * @var $page
 * @var $url
 * @var $pdf
 * @var $class
 * @var $custom_attribute
 * @var $site_preferences
 */
?>

<?php
$href = "";
$blank = "";
switch($type) {
    case "email":
        $href = "mailto:" . $url;
        break;
    case "tel":
        $href = "tel:" . $url;
        break;
    case "page":
        $href = get_page_href($page, $site_preferences->home_page_id);
        break;
    case "external":
    case "reserve":
        $href = $url;
        break;
    case "pdf":
        $href = base_url($pdf);
        break;
}
?>

<a <?= $custom_attribute ?> href="<?= $href ?>" class="<?= $class ?>"><?= $title ?></a>