{extends file="admin/layout.tpl"}

{block name=title}Editare definiție{/block}

{block name=headerTitle}
  {if $isOCR}
    {$title='Adăugare definiție OCR'}
  {else}
    {$title="Editare definiție {$def->id}"}
  {/if}
  {$title}
{/block}

{block name=content}
  <form action="definitionEdit.php" method="post">
    <input type="hidden" name="definitionId" value="{$def->id}"/>
    <input type="hidden" name="isOCR" value="{$isOCR}"/>
    {if $homonyms}
      <img src="{$imgRoot}/icons/exclamation.png" alt="warning"/>
      Omonim(e):
      {foreach $homonyms as $h}
        &nbsp;
        {include file="bits/lexemLink.tpl" lexem=$h}
        <span class="associateHomonym">
          [<a class="associateHomonymLink" data-hid="{$h->id}" href="#">asociază</a>]
        </span>
      {/foreach}
    {/if}

    <table class="editableFields">
      <tr>
        <td>Lexeme:</td>
        <td>
          <select id="lexemIds" name="lexemIds[]" style="width: 100%" multiple>
            {foreach $lexemIds as $l}
              <option value="{$l}" selected></option>
            {/foreach}
            {foreach $homonyms as $h}
              <option value="{$h->id}"></option>
            {/foreach}
          </select>

          <span class="tooltip2" title="Lexemele colorate cu roșu au accent sau paradigmă lipsă.">&nbsp;</span>

        </td>
      </tr>
      <tr>
        <td>Sursa:</td>
        <td>
          {if $source->canModerate}
            {include file="bits/sourceDropDown.tpl" sources=$allModeratorSources src_selected=$def->sourceId skipAnySource=true}
          {else}
            <input type="hidden" name="source" value="{$def->sourceId}"/>
            {$source->shortName}

            <span class="tooltip2" title="Sursa nu este deschisă pentru moderare și nu poate fi modificată.">&nbsp;</span>

          {/if}
        </td>
      </tr>
      <tr>
        <td>Starea:</td>
        <td>
          {include file="bits/statusDropDown.tpl" name="status" selectedStatus=$def->status}

          <span class="tooltip2" title="Dacă treceți o definiție în starea ștearsă, ea va fi automat disociată de orice lexem. Notă: Definiția va
                                        fi imposibil de găsit la o căutare ulterioară, tocmai din cauza disocierii (căutarea se face după lexem). Definiția este încă disponibilă în
                                        panoul de pagini recent vizitate.">&nbsp;</span>

        </td>
      </tr>
      {if count($typos)}
        <tr>
          <td>Greșeli de tipar:</td>
          <td>
            {foreach from=$typos item=typo}
              <span class="typo">* {$typo->problem|escape}</span><br/>
            {/foreach}
          </td>
        </tr>
      {/if}
      <tr>
        <td>Conținut:</td>
        <td>
          <textarea id="internalRep" name="internalRep" rows="15" cols="80"
                    >{$def->internalRep|escape}</textarea>
        </td>
      </tr>
      <tr>
        <td>
          Comentariu<br/>(opțional):

          <span class="tooltip2" title="Comentariul va fi vizibil public într-un subalineat al definiției. Folosiți acest câmp pentru a face adnotări pe
                                        marginea unei definiții fără a altera forma originală a definiției.">&nbsp;</span>

        </td>
        <td>
          <textarea id="comment" name="commentContents" rows="5" cols="80">{if $comment}{$comment->contents|escape}{/if}</textarea><br/>
          {if $commentUser}
            <input id="preserveCommentUser" type="checkbox" name="preserveCommentUser" value="1" checked="checked">
            <label for="preserveCommentUser">Păstrează autorul comentariului original ({$commentUser->nick|escape})</label>

            <span class="tooltip2" title="Dacă modificați un comentariu existent, puteți alege să vă treceți drept autor al comentariului sau să păstrați
                                          autorul versiunii anterioare. Sistemul nu ia automat această decizie. Nu fiți modești; dacă considerați că ați îmbunătățit semnificativ
                                          comentariul, însușiți-vi-l!">&nbsp;</span>

          {/if}
        </td>
      </tr>
      <tr id='similarSourceRow' {if !$sim->source}style="display:none"{/if}>
        <td>Similarități</td>
        <td>
          <input type="checkbox" id="similarSource" name="similarSource" value="1" {if $def->similarSource}checked="checked"{/if}/>
          <label for="similarSource">Definiție identică cu cea din <span class="similarSourceName"></span></label>
        </td>
      </tr>
      <tr>
        <td></td>
        <td>
          <div class="buttonRow">
            <input type="button" id="refreshButton" value="Reafișează"/>
            <span class="tooltip2" title="Tipărește definiția și comentariul cu modificările făcute. Modificările nu sunt încă salvate.">&nbsp;</span>
            <input type="submit" name="but_accept" value="Salvează"/>
            {if $isOCR}
              <input type="submit" name="but_next_ocr" value="Salvează și preia următoarea definiție OCR"/>
            {/if}

            <div id="tinymceButtonWrapper">
              <button id="tinymceToggleButton" type="button" data-other-text="ascunde TinyMCE" href="#"
                      title="TinyMCE este un editor vizual (cu butoane de bold, italic etc.).">
                arată TinyMCE
              </button>
            </div>
          </div>
        </td>
      </tr>
    </table>
  </form>

  <div id="defPreview">{$def->htmlRep}</div>
  <span class="defDetails">
    Id: {$def->id} |
    Sursa: {$source->shortName|escape} |
    Trimisă de {$user->nick|escape}, {$def->createDate|date_format:"%e %b %Y"} |
    Starea: {$def->getStatusName()}
  </span>

  <div id="commentPreview">{$comment->htmlContents|default:''}</div>

  <pre id="similarRecord"><!--{$sim->getJson()}--></pre>

  <div id="similarSourceMessageYes">
    Definiția corespunzătoare din <span class="similarSourceName"></span>:
    <a id="similarDefinitionEdit" href="?definitionId={$sim->definition->id|default:''}" target="_blank">
      <img src="{$imgRoot}/icons/pencil.png" alt="editează" title="editează"/>
    </a>
  </div>
  <div id="similarSourceMessageNoSource">
    Nu există o sursă anterioară.
  </div>
  <div id="similarSourceMessageNoDefinition">
    Nu există o definiție similară în <span class="similarSourceName"></span>.
  </div>

  <div id="similarRep"></div>
  <div id="similarNotIdentical"><img src="{$imgRoot}/icons/cross.png"> Diferențe față de definiția din <span class="similarSourceName"></span>:</div>
  <div id="similarIdentical"><img src="{$imgRoot}/icons/check.png"> Definiția este identică cu cea din <span class="similarSourceName"></span>.</div>
  <div id="similarDiff"></div>
{/block}
