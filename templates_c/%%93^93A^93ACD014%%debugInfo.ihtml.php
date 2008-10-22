<?php /* Smarty version 2.6.18, created on 2007-10-05 12:47:47
         compiled from common/bits/debugInfo.ihtml */ ?>
<?php if ($this->_tpl_vars['debug']): ?>
  <div class="debugInfo">
    Pagină generată în
    <?php echo debug_getRunningTimeInMillis(); ?>
    ms.<br/>
   <?php echo debug_getDebugInfoAsHtml(); ?>
  </div>
<?php endif; ?>