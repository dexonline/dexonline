{if $footnotes}
  <div class="card card-footnotes">
    <div class="card-body">
      <ol>
        {foreach $footnotes as $f}
          <li>
            {HtmlConverter::convert($f)}
            {if $f->isAnonymous()}
              {include "bits/user.tpl"}
            {else}
              &mdash;
              {include "bits/user.tpl" u=$f->getUser()}
            {/if}
          </li>
        {/foreach}
      </ol>
    </div>
  </div>
{/if}
