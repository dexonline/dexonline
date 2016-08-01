{extends file="layout.tpl"}

{block name=title}Unelte{/block}

{block name=content}
  <h3>Clienți off-line</h3>

  <p>
    Aceste aplicații transferă definițiile din <i>dexonline</i> pe
    calculatorul dumneavoastră personal. Ulterior, le puteți consulta și
    în absența unei conexiuni la internet.
  </p>

  <br />

  <table class="table table-striped-column-odd" id="toolTable">
    <tbody>
    <tr>
      <th>Client</th>
      <th>Platforme</th>
      {foreach from=$clientOptions item=option key=id}
        <th>
          {$option.0}
          <span class="tooltip2" title="{$option.1}">&nbsp;</span>
        </th>
      {/foreach}
      <th>Cerințe</th>
      <th>Spațiu necesar</th>
      <th>Autori</th>
      <th>Licență</th>
    </tr>

    {foreach from=$clients item=c}
      {if $c.available}
        <tr>
          <td>
            {if $c.name[1]}
              <a href="{$c.name[1]}">{$c.name[0]}</a>
            {else}
              {$c.name[0]}
            {/if}
          </td>

          <td>
            {foreach from=$c.os item=os}
              <img src="{$imgRoot}/icons/{$os}.png" alt="{$os}" title="{$osNames[$os]}" />
            {/foreach}
          </td>

          {foreach from=$clientOptions item=ignored key=id}
            <td>
              {if $c.options[$id]}
                <img src="{$imgRoot}/icons/check.png" alt="da"/>
              {/if}
            </td>
          {/foreach}

          <td>{$c.requires}</td>

          <td>{$c.space}</td>

          <td>
            {foreach from=$c.authors item=address key=name}
              {if $address|strstr:"http://"}
                <a href="{$address}">{$name}</a>
              {elseif $address}
                <a href="mailto:{$address}">{$name}</a>
              {else}
                {$name}
              {/if}
              <br/>
            {/foreach}
          </td>

          <td>{$c.license}</td>

        </tr>
      {/if}
    {/foreach}
    </tbody>
  </table>

  <div class="alert alert-warning">
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
  
  <ul class="browserIntegration">
    <li>Instalați un corector ortografic pentru limba română pentru Firefox:
      <a href="{$cfg.static.url}download/dex-ff.xpi" onclick="return installFirefoxSpellChecker(event);">clic aici</a>.

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

    <li>Un <a href="http://www.ieaddons.com/ro/addons/detail.aspx?id=12819">accelerator pentru Internet Explorer</a>. El vă permite să selectați un
      cuvânt din pagină și să-l căutați rapid în <i>dexonline</i>.</li>

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
