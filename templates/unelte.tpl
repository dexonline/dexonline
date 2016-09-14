{extends "layout.tpl"}

{block name=title}Unelte{/block}

{block name=content}
  <h3>Clienți off-line</h3>

  <p>
    Aceste aplicații transferă definițiile din <i>dexonline</i> pe
    calculatorul dumneavoastră personal. Ulterior, le puteți consulta și
    în absența unei conexiuni la internet.
  </p>

  <table class="table table-bordered">
    <tbody>
      <tr class="active">
        <th>Client</th>
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
        <th>Platforme</th>
        {foreach $clients as $c}
          <td class="text-center">
            {foreach $c.os as $os}
              <img src="{$imgRoot}/os/{$os}.png" alt="{$os}" title="{$osNames[$os]}" />
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
        <th>Cerințe</th>
        {foreach $clients as $c}
          <td>{$c.requires}</td>
        {/foreach}
      </tr>

      <tr>
        <th>Spațiu necesar</th>
        {foreach $clients as $c}
          <td>{$c.space}</td>
        {/foreach}
      </tr>

      <tr>
        <th>Autor</th>
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
        <th>Licență</th>
        {foreach $clients as $c}
          <td>{$c.license}</td>
        {/foreach}
      </tr>

    </tbody>
  </table>

  <div class="alert alert-info">
    <p>
      <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
      <strong>Licență:</strong> Informațiile din acest tabel poartă o dublă licență: Licența Publică Generală GNU (care se aplică întregului site) și licența <a
                                                                                                                                                                  href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution-ShareAlike 3.0</a>. Informațiile pot fi folosite (copiate,
      modificate, adaptate, etc) de către siteul http://wikipedia.org. Datele sunt adunate de pe lista discutii@dexonline.ro (fostă
      dictionar@yahoogroups.com) prin colaborare cu dezvoltatorii aplicațiilor.
    </p>
    <p>
      <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
      <strong>License:</strong> The information in this table is dual-licensed under the GNU General Public License (as applicable to the entire site) and the <a
                                                                                                                                                                   href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution-ShareAlike 3.0</a> license. The information can be used (copied,
      modified, adapted etc.) by the website http://wikipedia.org. The data are gathered from the mailing list discutii@dexonline.ro (formerly
      dictionar@yahoogroups.com) in collaboration with the application developers.
    </p>
  </div>

  <h3>Integrare în browser</h3>
  
  <ul>
    <li>
      Un 

      <a href="{$cfg.static.url}download/dex-ff.xpi"
           onclick="return installFirefoxSpellChecker(event);">
        corector ortografic
      </a>

      pentru Firefox.

      <ul>
        <li>Pentru a folosi corectorul, deschideți meniul <i>Edit >
          Preferences,</i> selectați panoul <i>Advanced</i> și bifați
          opțiunea <i>Check my spelling as I type.</i> Pentru a activa
          limba română (în locul limbii engleze), dați clic-dreapta în
          orice cutie de editare și selectați <i>Languages >
          Romanian.</i></li>
      </ul>
    </li>

    <li><a href="#" onclick="return addProvider('https://dexonline.ro/download/dex.xml')">Adăugați <i>dexonline</i> la motoarele de căutare</a> ale browserului
      dumneavoastră. Mulțumiri lui Alexandru Lixandru.</li>

    <li>Ați întâlnit cuvinte pe care nu le cunoașteți în pagini
      românești? Acest modul Firefox vă permite să le căutați cu ușurință
      în <i>dexonline</i>. Salvați <a href="download/dex-context-search.xml">acest
      fișier</a> în directorul <tt>searchplugins/</tt> al aplicației
      Firefox și reporniți Firefox. Acum, dacă selectați orice cuvânt și
      dați clic-dreapta, veți avea opțiunea să-l căutați în <i>dexonline</i>.
      Mulțumiri lui Radu George Mureșan.</li>

    <li>O <a href="https://chrome.google.com/extensions/detail/adpeehopdngemnfahceoeppdadkiagka">extensie Google Chrome</a>, autor OviTeodor.</li>

    <li>Un <a href="https://wordpress.org/plugins/dexonline-searchbox/">modul WordPress</a> pentru blogul dumneavoastră.</li>

  </ul>
{/block}
