{{--
 | tag.blade.php — flat taxonomy term archive.
 |
 | Rendered for terms belonging to non-hierarchical taxonomies (is_hierarchical = false).
 | Falls back to taxonomy.blade.php if this file does not exist in your theme.
 |
 | Available variables: $taxonomy, $taxTrans, $term, $termTrans, $posts, $site, $lang
--}}
@include('theme::taxonomy')
