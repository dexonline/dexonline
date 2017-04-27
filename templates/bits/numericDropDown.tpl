{assign var=selected value=$selected|default:null}
<select class="form-control" name="{$name}">
  {section name="sectionName" start=$start loop=$end}
    <option value="{$smarty.section.sectionName.index}"
            {if $smarty.section.sectionName.index == $selected}selected{/if}>
      {$smarty.section.sectionName.index}
    </option>
  {/section}
</select>
