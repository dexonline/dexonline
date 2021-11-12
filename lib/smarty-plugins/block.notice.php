<?php

/**
 * A replacement for Bootstrap alerts. We only use a single color and we
 * support vertically centered icons.
 **/
function smarty_block_notice($params, $content, $template, &$repeat) {
  // forward all the received $params plus the $content
  $params['contents'] = $content;
  if (!$repeat) {
    $template->smarty->assign($params);
    return $template->smarty->fetch('bits/notice.tpl');
  }
}
