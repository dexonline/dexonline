<select name="structuristId" class="form-control">
  <option value="{Entry::STRUCTURIST_ID_ANY}">oricare</option>
  <option value="{Entry::STRUCTURIST_ID_NONE}">niciunul</option>
  {foreach $structurists as $s}
    <option value="{$s->id}">
      {$s->nick} ({$s->name})
    </option>
  {/foreach}
</select>
