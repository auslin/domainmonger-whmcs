<?php
function lowercase_domain_hook($vars) {
  if (is_array($vars['products'])) {
    foreach($vars['products'] AS $k => $v) {
      if (isset($_SESSION['cart']['products'][$k]['domain'])) {
        $_SESSION['cart']['products'][$k]['domain'] = strtolower($v['domain']);
      }
    }
  }
  if (is_array($vars['domains'])) {
    foreach($vars['domains'] AS $k => $v) {
      if (isset($_SESSION['cart']['domains'][$k]['domain'])) {
        $_SESSION['cart']['domains'][$k]['domain'] = strtolower($v['domain']);
      }
    }
  }
}
add_hook("PreCalculateCartTotals",1,"lowercase_domain_hook");
?>