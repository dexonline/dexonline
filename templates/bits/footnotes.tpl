{if $footnotes}
  <div class="panel panel-default panel-footnotes">
    <div class="panel-body">
      <ol>
        {foreach $footnotes as $f}
          <li>
            {$f->htmlRep}
            &mdash;
            {include "bits/user.tpl" u=$f->getUser()}
          </li>
        {/foreach}
      </ol>
    </div>
  </div>
{/if}
