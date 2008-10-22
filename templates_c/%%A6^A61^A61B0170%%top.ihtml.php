<?php /* Smarty version 2.6.18, created on 2007-12-08 11:42:58
         compiled from common/top.ihtml */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'common/top.ihtml', 37, false),array('function', 'math', 'common/top.ihtml', 45, false),array('modifier', 'string_format', 'common/top.ihtml', 48, false),array('modifier', 'date_format', 'common/top.ihtml', 49, false),)), $this); ?>
Inimoşii "ctitori" ai <i>DEX online</i> sunt listaţi mai jos.  Dacă
<i>DEX online</i> v-a fost vreodată de folos, lor trebuie să le
mulţumiţi! Cu cât nuanţa de roşu este mai aprinsă, cu atât
persoana respectivă a fost mai activă în ultima vreme. Îi
mulţumim aici şi lui <b>Marius</b>, care a pus numeroase definiţii
scanate la îndemâna celor care doreau să ne ajute, dar nu aveau un
DEX tipărit. Fără el, <i>DEX online</i> nu ar fi ajuns atât de
departe, atât de repede.

<p><center>
<table border=0>
  <tr bgcolor=#d0d0d0>
    <th>Loc</th>
    <th align=left>
       <?php $this->assign('x', (@CRIT_NICK)); ?>
       <a href="top.php?crit=<?php echo $this->_tpl_vars['x']; ?>
&ord=<?php echo $this->_tpl_vars['title_links'][$this->_tpl_vars['x']]; ?>
">
       Nume</a>
    </th>
    <th>
      <?php $this->assign('x', (@CRIT_CHARS)); ?>
      <a href="top.php?crit=<?php echo $this->_tpl_vars['x']; ?>
&ord=<?php echo $this->_tpl_vars['title_links'][$this->_tpl_vars['x']]; ?>
">
      Nr. caractere</a>
    </th>
    <th>
      <?php $this->assign('x', (@CRIT_WORDS)); ?>
      <a href="top.php?crit=<?php echo $this->_tpl_vars['x']; ?>
&ord=<?php echo $this->_tpl_vars['title_links'][$this->_tpl_vars['x']]; ?>
">
      Nr. cuvinte</a>
    </th>
    <th>
      <?php $this->assign('x', (@CRIT_DATE)); ?>
      <a href="top.php?crit=<?php echo $this->_tpl_vars['x']; ?>
&ord=<?php echo $this->_tpl_vars['title_links'][$this->_tpl_vars['x']]; ?>
">
      Data ultimei trimiteri</a>
    </th>
  </tr>

  <?php $_from = $this->_tpl_vars['data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['place'] => $this->_tpl_vars['row']):
?>
    <tr bgcolor=<?php echo smarty_function_cycle(array('values' => "#eeeeee,#d0d0d0"), $this);?>
>
      <td align="center"><?php echo $this->_tpl_vars['place']+$this->_tpl_vars['start']+1; ?>
</td>
      <td width=200 nowrap>
	<a href="user.php?n=<?php echo $this->_tpl_vars['row']->userNick; ?>
"><?php echo $this->_tpl_vars['row']->userNick; ?>
</a>
      </td>
      <td align="center"><?php echo $this->_tpl_vars['row']->numChars; ?>
</td>
      <td align="center"><?php echo $this->_tpl_vars['row']->numDefinitions; ?>
</td>

      <?php echo smarty_function_math(array('equation' => "max(255 - days, 0)",'days' => $this->_tpl_vars['row']->days,'assign' => 'color'), $this);?>

      <td align="center">
        <?php if ($this->_tpl_vars['row']->timestamp <= 10): ?><b><?php endif; ?>
        <font color=<?php echo ((is_array($_tmp=$this->_tpl_vars['color'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "#%2x0000") : smarty_modifier_string_format($_tmp, "#%2x0000")); ?>
>
          <?php echo ((is_array($_tmp=$this->_tpl_vars['row']->timestamp)) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %b %Y") : smarty_modifier_date_format($_tmp, "%d %b %Y")); ?>

        </font>
        <?php if ($this->_tpl_vars['row']->timestamp <= 10): ?></b><?php endif; ?>
      </td>
    </tr>
  <?php endforeach; endif; unset($_from); ?>

    <tr>
    <td>
      <?php if ($this->_tpl_vars['prev_start'] != -1): ?>
        <font size=-1>
        <a href="top.php?crit=<?php echo $this->_tpl_vars['crit']; ?>
&ord=<?php echo $this->_tpl_vars['ord']; ?>
&start=<?php echo $this->_tpl_vars['prev_start']; ?>
">
	înapoi</a></font>
      <?php endif; ?>
    </td>
    <td colspan=4 align=right>
      <?php if ($this->_tpl_vars['next_start'] != -1): ?>
        <font size=-1>
        <a href="top.php?crit=<?php echo $this->_tpl_vars['crit']; ?>
&ord=<?php echo $this->_tpl_vars['ord']; ?>
&start=<?php echo $this->_tpl_vars['next_start']; ?>
">
	înainte</a></font>
      <?php endif; ?>
    </td>
  </tr>
</table>
</center>