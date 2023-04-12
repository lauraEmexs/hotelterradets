// This file contains style definitions that can be used by CKEditor plugins.
//
// The most common use for it is the "stylescombo" plugin which shows the Styles drop-down
// list containing all styles in the editor toolbar. Other plugins, like
// the "div" plugin, use a subset of the styles for their features.
//
// If you do not have plugins that depend on this file in your editor build, you can simply
// ignore it. Otherwise it is strongly recommended to customize this file to match your
// website requirements and design properly.
//
// For more information refer to: https://docs.ckeditor.com/ckeditor4/docs/#!/guide/dev_styles-section-style-rules

CKEDITOR.stylesSet.add( 'my_styles', [
    // Block-level styles
    { name: 'Title (h2)', element: 'h2', attributes: { 'class': 'title' } },
    { name: 'Sub Title (h3)' , element: 'h3', attributes: { 'class': 'title' } },
    { name: 'Text' , element: 'p', attributes: { 'class': 'text' } },

    // Object styles

    // Inline styles

] );