<select name="{$users.vars.name}"
  id="{$users.vars.id}"
  class="form-control">
  <option value="">Orice moderator</option>
  {foreach $users.resultSet as $user}
    <option value="{$user->id}"
      {if $users.vars.selectedValue == $user->$users.vars.submitValue}selected{/if}
      >{$user->nick|escape}</option>
  {/foreach}
</select>
