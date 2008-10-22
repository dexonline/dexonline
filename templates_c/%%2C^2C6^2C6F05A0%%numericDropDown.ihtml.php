<?php /* Smarty version 2.6.18, created on 2007-10-05 12:47:53
         compiled from common/bits/numericDropDown.ihtml */ ?>
<select name="<?php echo $this->_tpl_vars['name']; ?>
">
  <?php unset($this->_sections['sectionName']);
$this->_sections['sectionName']['name'] = 'sectionName';
$this->_sections['sectionName']['start'] = (int)$this->_tpl_vars['start'];
$this->_sections['sectionName']['loop'] = is_array($_loop=$this->_tpl_vars['end']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['sectionName']['show'] = true;
$this->_sections['sectionName']['max'] = $this->_sections['sectionName']['loop'];
$this->_sections['sectionName']['step'] = 1;
if ($this->_sections['sectionName']['start'] < 0)
    $this->_sections['sectionName']['start'] = max($this->_sections['sectionName']['step'] > 0 ? 0 : -1, $this->_sections['sectionName']['loop'] + $this->_sections['sectionName']['start']);
else
    $this->_sections['sectionName']['start'] = min($this->_sections['sectionName']['start'], $this->_sections['sectionName']['step'] > 0 ? $this->_sections['sectionName']['loop'] : $this->_sections['sectionName']['loop']-1);
if ($this->_sections['sectionName']['show']) {
    $this->_sections['sectionName']['total'] = min(ceil(($this->_sections['sectionName']['step'] > 0 ? $this->_sections['sectionName']['loop'] - $this->_sections['sectionName']['start'] : $this->_sections['sectionName']['start']+1)/abs($this->_sections['sectionName']['step'])), $this->_sections['sectionName']['max']);
    if ($this->_sections['sectionName']['total'] == 0)
        $this->_sections['sectionName']['show'] = false;
} else
    $this->_sections['sectionName']['total'] = 0;
if ($this->_sections['sectionName']['show']):

            for ($this->_sections['sectionName']['index'] = $this->_sections['sectionName']['start'], $this->_sections['sectionName']['iteration'] = 1;
                 $this->_sections['sectionName']['iteration'] <= $this->_sections['sectionName']['total'];
                 $this->_sections['sectionName']['index'] += $this->_sections['sectionName']['step'], $this->_sections['sectionName']['iteration']++):
$this->_sections['sectionName']['rownum'] = $this->_sections['sectionName']['iteration'];
$this->_sections['sectionName']['index_prev'] = $this->_sections['sectionName']['index'] - $this->_sections['sectionName']['step'];
$this->_sections['sectionName']['index_next'] = $this->_sections['sectionName']['index'] + $this->_sections['sectionName']['step'];
$this->_sections['sectionName']['first']      = ($this->_sections['sectionName']['iteration'] == 1);
$this->_sections['sectionName']['last']       = ($this->_sections['sectionName']['iteration'] == $this->_sections['sectionName']['total']);
?>
    <option value="<?php echo $this->_sections['sectionName']['index']; ?>
"
            <?php if ($this->_sections['sectionName']['index'] == $this->_tpl_vars['selected']): ?>
              selected="selected"
            <?php endif; ?>>
      <?php echo $this->_sections['sectionName']['index']; ?>

    </option>
  <?php endfor; endif; ?>
</select>