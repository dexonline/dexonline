{extends "layout.tpl"}

{block "title"}{'tools'|_|cap}{/block}

{block "content"}
  <h3>{'off-line clients'|_|cap}</h3>

  <p>
    {'These applications download the definitions from <i>dexonline</i> to
    your computer or phone. Then you can see them without an Internet
    connection.'|_}
  </p>

  <table class="table table-bordered">
    <tbody>
      <tr class="active">
        <th>{'Client'|_}</th>
        {foreach $clients as $c}
          <td class="text-center">
            <a href="{$c.url}">{$c.name}</a>
            {if $c.download}
              <br>
              (<a href="{$c.download}">descarcă</a>)
            {/if}
          </td>
        {/foreach}
      </tr>

      <tr>
        <th>{'Platforms'|_}</th>
        {foreach $clients as $c}
          <td class="text-center">
            {foreach $c.os as $os}
              <img src="{$imgRoot}/os/{$os}.png" alt="{$os}" title="{$osNames[$os]}">
            {/foreach}
          </td>
        {/foreach}
      </tr>

      {foreach $clientOptions as $id => $option}
        <tr>
          <th title="{$option.1}">
            {$option.0}
            <i class="text-info glyphicon glyphicon-info-sign"></i>
          </th>
          {foreach $clients as $c}
            <td class="text-center">
              {if $c.options[$id]}
                <i class="glyphicon glyphicon-ok"></i>
              {/if}
            </td>
          {/foreach}
        </tr>
      {/foreach}

      <tr>
        <th>{'Requirements'|_}</th>
        {foreach $clients as $c}
          <td>{$c.requires}</td>
        {/foreach}
      </tr>

      <tr>
        <th>{'Disk space required'|_}</th>
        {foreach $clients as $c}
          <td>{$c.space}</td>
        {/foreach}
      </tr>

      <tr>
        <th>{'Author'|_}</th>
        {foreach $clients as $c}
          <td>
            {if $c.author.1}
              <a href="{$c.author.1}">{$c.author.0}</a>
            {else}
              {$c.author.0}
            {/if}
          </td>
        {/foreach}
      </tr>

      <tr>
        <th>{'license'|_|cap}</th>
        {foreach $clients as $c}
          <td>{$c.license}</td>
        {/foreach}
      </tr>

    </tbody>
  </table>

  <div class="clearfix">
    <a class="btn btn-link pull-right"
      data-toggle="collapse"
      href="#tableLicense"
      aria-expanded="false"
      aria-controls="tableLicense">
      {'license on the data in this table'|_}
    </a>
  </div>

  <div id="tableLicense" class="alert alert-info collapse">
    <p>
      <i class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></i>

      <strong>{'license'|_|cap}:</strong>

      {'The information in this table is dual-licensed under the GNU General
      Public License (as applicable to the entire site) and the
      <a href="%s">Creative Commons Attribution-ShareAlike 3.0</a> license.
      The information can be used (copied, modified, adapted etc.) by the
      website http://wikipedia.org. The data are gathered from the mailing
      list discutii@dexonline.ro (formerly dictionar@yahoogroups.com) in
      collaboration with the application developers.'|_|sprintf
      :"http://creativecommons.org/licenses/by-sa/3.0/"}
    </p>
  </div>

  <h3>{'Browser integration'|_}</h3>

  <ul>
    <li>
      {'A <a href="%s" onclick="%s">spell checker</a> for Firefox.'|_|sprintf
      :"{$cfg.static.url}download/dex-ff.xpi"
      :"return installFirefoxSpellChecker(event);"}

      <ul>
        <li>
          {'To use the spell checker, right-click in any text box, check the
          <em>Check spelling</em> box, right-click once more and select
          <em>Languages > Romanian</em>.'|_}
        </li>
      </ul>
    </li>

    <li>
      {'<a href="#" onclick="%s">Add <i>dexonline</i> to the search engine list</a>
      of your browser. Thanks to Alexandru Lixandru.'|_|sprintf
      :"return addProvider('https://dexonline.ro/download/dex.xml')"}
    </li>

    <li>
      {'A <a href="%s">Chrome extension</a> for right-click searches.'|_|sprintf
      :"https://chrome.google.com/webstore/detail/mfbhbmjeaomdhmkcfhlldgedmohdpeml?hl=ro"}
    </li>

    <li>
      {'A <a href="%s">Firefox plugin</a> for right-click searches. Save the file
      in the <tt>searchplugins/</tt> directory of Firefox and restart Firefox.
      Thanks to %s.'|_|sprintf
      :"download/dex-context-search.xml"
      :"Radu George Mureșan"}
    </li>

    <li>
      {'A <a href="%s">WordPress plugin</a> for your blog.'|_|sprintf
      :"https://wordpress.org/plugins/dexonline-searchbox/"}
    </li>

  </ul>
{/block}
