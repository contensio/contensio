{{--
 | category.blade.php — hierarchical taxonomy term archive.
 |
 | Rendered for terms belonging to hierarchical taxonomies (is_hierarchical = true).
 | Falls back to taxonomy.blade.php if this file does not exist in your theme.
 |
 | Available variables: $taxonomy, $taxTrans, $term, $termTrans, $posts, $site, $lang
--}}
@include('theme::taxonomy')
