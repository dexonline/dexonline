{if $footnotes}
  <div class="card card-footnotes">
    <div class="card-body">
      <ol>
        {foreach $footnotes as $f}
          <li>
            {HtmlConverter::convert($f)}
            &mdash;
            {include "bits/user.tpl" u=$f->getUser()}
          </li>
        {/foreach}
      </ol>
    </div>
  </div>
{/if}
