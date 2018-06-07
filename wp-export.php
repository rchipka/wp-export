
<?php
if ($_GET['action'] == 'export' || $_GET['filter_action'] == 'Filter') {
  if (empty($_GET['s'])) {
    unset($_GET['s']);
  }
}

add_action('admin_head', function () {
?>
    <script>
      (function ($) {
          $(function () {
              $('button[name="action"][value="export"]').on('click', function () {
                  $(this).before('<input type="hidden" name="acp_filter_action" value="Filter" />');
                  $(this).before('<input type="hidden" name="ids" value="-1" />');
                  $(this).before('<input type="hidden" name="users" value="-1" />');
                  $(this).before('<input type="hidden" name="action" value="export" />');
                });
            });
        })(jQuery);
    </script>
    <?php
});
add_action('init', function () {
  add_filter('handle_bulk_actions-users', 'vbtk_export_posts');
  foreach(array_keys(get_post_types()) as $post_type) {
    add_filter('handle_bulk_actions-edit-' . $post_type, 'vbtk_export_posts');
  }
});

function vbtk_post_export_button() { ?>
  <button class="button" name="action" value="export" formmethod="GET" formtarget="_blank" style="margin-top:0px;margin-left:1rem;">Export</button>
  <?php
}

add_action('restrict_manage_posts', 'vbtk_post_export_button');
add_action('restrict_manage_users', 'vbtk_post_export_button');

function vbtk_pre_get_post_export($query) {
  if ($_GET['action'] !== 'export' || !is_admin()) {
    return $query;
  }

  $query->set('number', -1);
  $query->set('posts_per_page', -1);
  $query->set('posts_per_archive_page', -1);
  return $query;
}

add_action('pre_get_posts', 'vbtk_pre_get_post_export', 11, 1);
add_action('pre_get_users', 'vbtk_pre_get_post_export', 11, 1);
add_filter('edit_posts_per_page', function ($per_page, $post_type) {
  if ($_REQUEST['action'] !== 'export') {
    return $per_page;
  }

  return 1000;
}

, 10, 2);

// 'users_list_table_query_args'
// 'users_per_page'

function vbtk_export_posts() {
  global $wp_list_table;
  global $wp_query;
  
  set_time_limit(300);

  if ($_GET['action'] != 'export') {
    return;
  }

  $wp_query->set('posts_per_page', -1);
  $_GET['paged'] = 1;
  $wp_list_table->prepare_items();
  $columns = array_values(array_filter(array_values($wp_list_table->get_column_info() [0]) , 'strip_tags'));
  ob_start();
  $wp_list_table->display();

  // $wp_list_table->display_rows_or_placeholder();

  $result = ob_get_clean();
  $filename = 'export-' . ($_GET['post_type'] ? : time());
  header('Content-Encoding: UTF-8');
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
  echo "\xEF\xBB\xBF";
  echo implode(',', array_map(
  function ($value) {
    return json_encode(strip_tags($value));
  }

  , $columns)) . "\n";
  preg_match_all('/<tr.+?<\/tr>/si', $result, $rows);
  foreach($rows[0] as $i => $row) {
    if ($i == 0) {
      continue;
    }

    $cols = array();
    preg_match_all('/<td .+? data-colname="([^\"]+)">(.*?)<\/td>/si', $row, $tds, PREG_SET_ORDER);
    foreach($tds as $td) {
      preg_match_all('/<a.+?>.+?<\/a>/si', $td[2], $links);
      if (sizeof($links[0]) > 0) {
        if ($td[1] === 'Title' || array_search($td[1], $columns) === 0) {
          $cols[$td[1]] = trim(decodeHtmlEnt(replace_tags($links[0][0])));
        }
        else {
          $cols[$td[1]] = trim(decodeHtmlEnt(replace_tags(implode(' | ', $links[0]))));
        }
      }
      else {
        $cols[$td[1]] = trim(decodeHtmlEnt(replace_tags($td[2])));
      }
    }

    $values = array();
    foreach($columns as $column) {
      array_push($values, '"' . addcslashes($cols[$column], '"') . '"');
    }

    $line = implode(',', $values) . "\n";
    echo $line;
  }

  exit;
}

function replace_tags($string) {
  $spaceString = str_replace('<', ' <', $string);
  $doubleSpace = strip_tags($spaceString);
  return str_replace(' ', ' ', $doubleSpace);
}

function decodeHtmlEnt($str) {
  $ret = html_entity_decode($str, ENT_COMPAT, 'UTF-8');
  $p2 = - 1;
  for (;;) {
    $p = strpos($ret, '&#', $p2 + 1);
    if ($p === FALSE) break;

    $p2 = strpos($ret, ';', $p);
    if ($p2 === FALSE) break;

    if (substr($ret, $p + 2, 1) == 'x') $char = hexdec(substr($ret, $p + 3, $p2 - $p - 3));
    else $char = intval(substr($ret, $p + 2, $p2 - $p - 2));

    // echo "$char\n";

    $newchar = iconv('UCS-4', 'UTF-8', chr(($char >> 24) & 0xFF) . chr(($char >> 16) & 0xFF) . chr(($char >> 8) & 0xFF) . chr($char & 0xFF));

    // echo "$newchar<$p<$p2<<\n";

    $ret = substr_replace($ret, $newchar, $p, 1 + $p2 - $p);
    $p2 = $p + strlen($newchar);
  }

  return $ret;
}
