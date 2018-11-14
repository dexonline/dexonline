{if isset($answer)}
  {if $answer}
    <div class="alert alert-success">
      {'The form <strong>%s</strong> exists in LOC %s.'|_|sprintf
      :$form
      :$version}
    </div>
  {else}
    <div class="alert alert-danger">
      {'The form <strong>%s</strong> does not exist in LOC %s.'|_|sprintf
      :$form
      :$version}
    </div>
  {/if}
{/if}
