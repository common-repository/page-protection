<?php
/*
Plugin Name: Page Protection
Plugin URI: http://www.mfd-consult.dk/page-protection/
Description: Protect pages and their subpages with user name/password, and keep protected pages from showing up in menus, search results and page lists.
Version: 1.2
Author: Morten Høybye Frederiksen
Author URI: http://www.wasab.dk/morten/
License: GPL
*/

function page_protection_template_redirect() {
  global $post;
  if (!is_page() || !is_protected_page())
    return;
  if (!is_authorized_protected_page(true)) {
    $pp = page_protection_pages(false, true);
    @header("HTTP/1.1 401 Not Authorized");
    @header('WWW-Authenticate: Basic realm="'.utf8_decode(get_bloginfo_rss('name')).': '.utf8_decode($pp[$post->ID]['parent_title']).'"');
  }
  @header('Cache-Control: no-cache, must-revalidate, max-age=0');
  @header('Pragma: no-cache');
}
add_action('template_redirect', 'page_protection_template_redirect');

function page_protection_parse_query(&$q) {
  if (is_admin() || !is_search())
    return $q;
  $pp = page_protection_pages(false, true);
  foreach ($pp as $pageid => $ppinfo) {
    if (!isset($ppinfo['searchable']) || !$ppinfo['searchable'])
      $q->query_vars['post__not_in'][] = $pageid;
  }
  return $q;
}
add_action('parse_query', 'page_protection_parse_query');

function page_protection_pages($only_children = false, $extended = false) {
  $cache_key = 'page-protection' . ($only_children?'-oc':'') . ($extended?'-e':'');
  $list = wp_cache_get($cache_key);
  if ($list !== false)
    return $list;
  global $wpdb;

  // Find pages that are protected directly.
  $prot = $wpdb->get_results('SELECT DISTINCT post_id FROM '.$wpdb->postmeta.' WHERE meta_key = "_page-protection"');
  $list = array();
  if (sizeof($prot)) {
    // Build list to hold all protected pages.
    $epages = array();
    foreach ($prot as $m)
      $epages[] = $m->post_id;
    if (!$only_children)
      $list = $epages;
    if ($extended) {
      $list = array_flip($list);
      foreach ($list as $pageid => $dummy)
        $list[$pageid] = array_merge(array('parent'=>$pageid, 'parent_title'=>get_the_title($pageid)), get_post_meta($pageid,'_page-protection',true));
    }
    // Find children of protected pages.
    while ($prot = $wpdb->get_results('SELECT DISTINCT ID, post_parent FROM '.$wpdb->posts.' WHERE (post_status="static" OR post_type="page") AND (post_parent='.join(' OR post_parent=', $epages).')')) {
      $epages = array();
      foreach ($prot as $m) {
        $epages[] = $m->ID;
        if ($extended)
          $list[$m->ID] = $list[$m->post_parent];
        else
          $list[] = $m->ID;
      }
    }
  }
  wp_cache_add($cache_key, $list);
  return $list;
}

function page_protection_wp_list_pages_excludes($exclude) {
  global $post;
  if (is_authorized_protected_page()) {
    $pp = page_protection_pages(false,true);
    foreach ($pp as $pageid => $ppinfo) {
      if ($ppinfo['parent']!=$pp[$post->ID]['parent'] && $ppinfo['parent']!=$pageid)
        $exclude[] = $pageid;
    }
    return $exclude;
  }
  else
    return array_unique(array_merge($exclude,page_protection_pages(true)));
}
add_filter('wp_list_pages_excludes', 'page_protection_wp_list_pages_excludes');

function is_protected_page() {
  global $post;

  if (!$post || !$post->ID)
    return false;

  $pp = page_protection_pages(false, true);
  if (isset($pp[$post->ID]))
    return true;
  return false;
}

function is_authorized_protected_page($header=false) {
  global $post;
  
  if (!is_protected_page())
    return false;

  $pp = page_protection_pages(false, true);
  
  if (!$header && $pp[$post->ID]['user']=='debug' && $pp[$post->ID]['pass']=='debug') {
    print_r($_SERVER);
    print_r($_REQUEST);
    print_r($pp);
    exit;
  } elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
    list($user, $pw) = array($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
  elseif (isset($_SERVER['HTTP_AUTHORIZATION']))
    list($user, $pw) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
  elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))
    list($user, $pw) = explode(':' , base64_decode(substr($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 6)));
  elseif (isset($_SERVER['REDIRECT_REDIRECT_HTTP_AUTHORIZATION']))
    list($user, $pw) = explode(':' , base64_decode(substr($_SERVER['REDIRECT_REDIRECT_HTTP_AUTHORIZATION'], 6)));
  elseif (isset($_REQUEST['PHP_AUTH_USER']) && isset($_REQUEST['PHP_AUTH_PW']))
    list($user, $pw) = array($_REQUEST['PHP_AUTH_USER'], $_REQUEST['PHP_AUTH_PW']);
  elseif (isset($_REQUEST['HTTP_AUTHORIZATION']))
    list($user, $pw) = explode(':' , base64_decode(substr($_REQUEST['HTTP_AUTHORIZATION'], 6)));
  elseif (isset($_REQUEST['REDIRECT_HTTP_AUTHORIZATION']))
    list($user, $pw) = explode(':' , base64_decode(substr($_REQUEST['REDIRECT_HTTP_AUTHORIZATION'], 6)));
  elseif (isset($_REQUEST['REDIRECT_REDIRECT_HTTP_AUTHORIZATION']))
    list($user, $pw) = explode(':' , base64_decode(substr($_REQUEST['REDIRECT_REDIRECT_HTTP_AUTHORIZATION'], 6)));
  else
    return false;

  if ($pp[$post->ID]['user']==$user && $pp[$post->ID]['pass']==$pw)
    return true;
  return false;
}

function page_protection_the_content($content) {
  global $post;
  if (!is_protected_page() || is_authorized_protected_page())
    return $content;
  return '<div class="page-protection">' . __('This page is protected with user name and password...', 'page-protection') . '</div>';
}
add_filter('the_content', 'page_protection_the_content');

function page_protection_the_excerpt($excerpt) {
  if (!is_protected_page() || is_authorized_protected_page())
    return $excerpt;
  return '<span class="page-protection">' . __('This page is protected with user name and password...', 'page-protection') . '</span>';
}
add_filter('the_excerpt', 'page_protection_the_excerpt');

function page_protection_admin_head() {
  echo '<style type="text/css">
#page-protection-user-pass label { display: block; margin: 4px 0; }
#page-protection-user-pass input { display: block; width: 94%; }
#page-protection-options { margin: 4px 0; }
</style>';
}
add_action('admin_head', 'page_protection_admin_head');

function page_protection_meta_box($post) {
  $page_protection = get_post_meta($post->ID,'_page-protection',true);
  $page_protection_on = (is_array($page_protection) && isset($page_protection['user']) && isset($page_protection['pass']));
  $page_protection_user = $page_protection_on ? $page_protection['user'] : '';
  $page_protection_pass = $page_protection_on ? $page_protection['pass'] : '';
  $page_protection_searchable = $page_protection_on ? ($page_protection['searchable']?true:false) : false;
  ?>
  <div id="page-protection-switch">
    <input type="checkbox" tabindex="96" name="page-protection-on" id="page-protection-on" value="1" <?php checked( $page_protection_on, true ); ?> />
    <label for="page-protection-on" class="selectit"><?php _e('Protect page and subpages', 'page-protection'); ?></label>
  </div>
  <div id="page-protection-user-pass">
    <span>
      <label for="post_user"><?php _e('User name:', 'page-protection'); ?></label>
      <input type="text" tabindex="97" name="page-protection-user" id="page-protection-user" value="<?php echo esc_attr($page_protection_user); ?>" />
    </span>
    <span>
      <label for="post_pass"><?php _e('Password:', 'page-protection'); ?></label>
      <input type="text" tabindex="98" name="page-protection-pass" id="page-protection-pass" value="<?php echo esc_attr($page_protection_pass); ?>" />
    </span>
  </div>
  <div id="page-protection-options">
    <input type="checkbox" tabindex="99" name="page-protection-searchable" id="page-protection-searchable" value="1" <?php checked( $page_protection_searchable, true ); ?> />
    <label for="page-protection-searchable" class="selectit"><?php _e('Make page and subpages searchable', 'page-protection'); ?></label>
  </div>
  <?php
}

function page_protection_insert_post($pID) {
  delete_post_meta($pID, '_page-protection');
  if (isset($_POST['page-protection-on']) && $_POST['page-protection-on'])
    update_post_meta($pID, '_page-protection', array(
      'user' => $_POST['page-protection-user'],
      'pass' => $_POST['page-protection-pass'],
      'searchable' => isset($_POST['page-protection-searchable'])
    )
  );
}
add_action('wp_insert_post', 'page_protection_insert_post');

function page_protection_admin_init() {
  add_meta_box('pageprotectiondiv', __('Page Protection', 'page-protection'), 'page_protection_meta_box', 'page', 'side', 'default');
}
add_action('admin_init', 'page_protection_admin_init');

function page_protection_init() {
  // Load translation
  load_plugin_textdomain('page-protection', PLUGINDIR . 'page-protection', 'page-protection');
}
add_action('init', 'page_protection_init');

// EOF
