{if isset($data)}
    {if !count($data)}
      <p class="alert alert-danger">
        Niciun cuvânt din LOC {$selectedLocVersion|escape} nu generează forma
        <strong>{$form|escape}.</strong>
      </p>
    {else}
      <div class="alert alert-success">
        <dl class="dl-horizontal">
          {foreach $data as $r}
            <dt>{$r.inflectedForm|escape}</dt>
            <dd>provine din
              <a href="{$wwwRoot}definitie/{$r.lexemeFormNoAccent|escape}">{$r.lexemeForm|escape}</a>
              {$r.modelType}{$r.modelNumber}{$r.restriction}
              ({$r.inflection|escape})
            </dd>
          {/foreach}
        </dl>
      </div>
    {/if}
{/if}
