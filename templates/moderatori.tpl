{extends "layout-admin.tpl"}

{block "title"}Moderatori{/block}

{block "content"}
  <h3>Moderatori</h3>

  <form method="post" action="moderatori">
    <table class="table table-condensed table-striped table-bordered">
      <tr>
        <th>Nume utilizator</th>
        <th>Admin</th>
        <th>Moderator LOC</th>
        <th>Moderator</th>
        <th>Ghid de exprimare<br/>(nefolosit)</th>
        <th>Cuvântul zilei</th>
        <th>Acces căutare</th>
        <th>«Structurist» al definițiilor</th>
        <th>Dicționarul vizual</th>
      </tr>
      {foreach $users as $user}
        <tr>
          <td>
            <a href="{$wwwRoot}utilizator/{$user->nick}">{$user->nick}</a>
            {* Ensure this user is processed even if all the boxes are unchecked *}
            <input type="hidden" name="userIds[]" value="{$user->id}"/>
          </td>

          {section name="bit" loop=$smarty.const.NUM_PRIVILEGES}
            {math equation="1 << x" x=$smarty.section.bit.index assign="mask"}
            <td>
              <input type="checkbox" name="priv_{$user->id}[]" value="{$mask}" {if $user->moderator & $mask}checked="checked"{/if}/>
            </td>
          {/section}
        </tr>
      {/foreach}
      <tr>
        <td>
	        <input type="text" name="newNick" class="form-control" placeholder="Moderator nou">
        </td>
        {section name="bit" loop=$smarty.const.NUM_PRIVILEGES}
          {math equation="1 << x" x=$smarty.section.bit.index assign="mask"}
          <td><input type="checkbox" name="newPriv[]" value="{$mask}"/></td>
        {/section}
      </tr>
    </table>

    <button type="submit" class="btn btn-success" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează
    </button>

  </form>
{/block}
