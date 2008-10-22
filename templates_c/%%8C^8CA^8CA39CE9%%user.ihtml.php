<?php /* Smarty version 2.6.18, created on 2007-12-08 11:44:40
         compiled from common/user.ihtml */ ?>
<?php if (! empty ( $this->_tpl_vars['user'] )): ?>
  <table>
  <tr>
    <td width=150>Utilizator:</td>
    <td><b><font size=+1><?php echo $this->_tpl_vars['user']->nick; ?>
</font></b></td>
  </tr>

  <?php if ($this->_tpl_vars['user']->name != ""): ?>
    <tr><td>Nume:</td><td><b><?php echo $this->_tpl_vars['user']->name; ?>
</b></td></tr>
  <?php endif; ?>

  <?php if ($this->_tpl_vars['user']->emailVisible): ?>
    <tr><td>Email:</td><td><b><?php echo $this->_tpl_vars['user']->email; ?>
</b></td></tr>
  <?php endif; ?>

  <tr>
    <td>Drept de moderator:</td>
    <td><b><?php if ($this->_tpl_vars['user']->moderator): ?>Da<?php else: ?>Nu<?php endif; ?></b></td>
  </tr>

  <tr>
    <td>Definiţii trimise:</td>
    <td><b><?php echo $this->_tpl_vars['userData']['num_words']; ?>
 (locul <?php echo $this->_tpl_vars['userData']['rank_words']; ?>
)</b></td>
  </tr>

  <tr>
    <td>Lungime totală:</td>
    <td><b><?php echo $this->_tpl_vars['userData']['num_chars']; ?>
 caractere (locul <?php echo $this->_tpl_vars['userData']['rank_chars']; ?>
)</b></td>
  </tr>
  </table>
<?php else: ?>
  Utilizatorul <b><?php echo $this->_tpl_vars['missingNick']; ?>
</b> nu există în <i>DEX online</i>.
<?php endif; ?>