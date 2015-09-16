{extends file="admin/layout.tpl"}

{block name=title}Alegere sufix{/block}

{block name=headerTitle}
  Alegere sufix pentru etichetare în masă
{/block}

{block name=content}
  Mai întâi, alegeți sufixul de examinat. În paranteză este trecut
  numărul de lexeme care au sufixul ales.
  <br/><br/>

  <form action="bulkLabel.php" method="get">
    Sufix: 
    <select name="suffix">
      {foreach from=$stats item=stat}
        <option value="{$stat.0}">{$stat.0} ({$stat.1})</option>
      {/foreach}
    </select>

    <input type="submit" name="ignoredSubmit" value="Continuă" onclick="return hideSubmitButton(this)"/>
  </form>
{/block}
