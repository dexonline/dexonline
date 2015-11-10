{extends file="layout.tpl"}

{block name=title}
  Cuvântul zilei ({$timestamp|date_format:'%e %B %Y'}): {$searchResult->definition->lexicon}
{/block}

{block name=pageDescription}
  <meta name="description"
        content="Cuvântul zilei de {$timestamp|date_format:'%e %B %Y'} la dexonline: {$searchResult->definition->lexicon}"/>
{/block}

{block name=openGraph}
  {* Nothing -- so crawlers index the image of the day instead. *}
{/block}

{block name=content}
  {assign var="nextday" value=$nextday|default:false}
  {assign var="prevday" value=$prevday|default:false}
  {assign var="reason" value=$reason|default:''}
  <div>
    {if $skinVariables.wotdSubscribe}
      <div id="wotdSocialMedia">
        <img src="{$imgRoot}/social-media/email-29.png" alt="iconiță email"/>
        <a type="application/rss+xml" href="https://dexonline.ro/rss/cuvantul-zilei"><img src="{$imgRoot}/social-media/rss-29.png" alt="iconiță RSS"/></a>
        <a href="https://www.facebook.com/dexonline"><img src="{$imgRoot}/social-media/facebook-29.png" alt="iconiță Facebook"/></a>
        <span id="toggleTitle" class="toggleClosed">detalii</span>
      </div>
    {/if}
    <p class="paragraphTitle">Cuvântul zilei, {$timestamp|date_format:'%e %B %Y'}</p>
  </div>

  {if $skinVariables.wotdSubscribe}
    <div id="toggleContents" class="commonShadow">
      <ul>
        <li><b>Email:</b> <i>dexonline</i> nu oferă cuvântul zilei direct prin email. Există însă <a
      href="http://www.google.com/search?q=rss+by+email">numeroase site-uri</a> care fac acest lucru pentru orice RSS. Vă recomandăm <a
      href="http://www.feedmyinbox.com/">feed my inbox</a>:

          <ul>
            <li>La <i>Website or Feed URL</i> introduceți <code>https://dexonline.ro/rss/cuvantul-zilei</code></li>
            <li>La <i>Your Email Address</i> introduceți adresa dumneavoastră de e-mail</li>
            <li>Apăsați <i>Submit</i>
              <li>Veți primi un e-mail pentru confirmarea abonării</li>
              <li>În continuare veți primi zilnic câte un email cu cuvântul zilei.</li>
          </ul>
            </li>

            <li><b>RSS:</b> Dacă folosiți un cititor de bloguri, puteți adăuga <a href="https://dexonline.ro/rss/cuvantul-zilei">feed-ul pentru cuvântul
              zilei</a> la lista urmărită.</li>

            <li><b>Facebook:</b> Cuvântul zilei este publicat automat și pe Facebook. Puteți aprecia <a href="https://www.facebook.com/dexonline">pagina
              dexonline</a> pentru a primi cuvântul zilei cu fluxul dumneavoastră de știri.</li>

      </ul>
    </div>

    <script>
     $(function() {
       $('#toggleTitle').click(function() {
         $('#toggleContents').slideToggle(200);
         $('#toggleTitle').toggleClass('toggleOpen');
         $('#toggleTitle').toggleClass('toggleClosed');
       });
     });
    </script>
  {/if}

  {include file="bits/definition.tpl" row=$searchResult}

  {if $reason}
    <div class="wotdReason">
      <b>Cheia alegerii:</b> {$reason|escape:'html'}
    </div>
  {/if}

    {if $imageUrl}
        <div id="wotdImage">
            <table>
                <tr>
                    <td><a href="{$wwwRoot}cuvantul-zilei/{$prevday}">«</a></td>
                    <td>
                        <img src="{$imageUrl}" alt="{$searchResult->definition->lexicon}" title="{$searchResult->definition->lexicon}"/>
                        <div class="copyright">
                            {$artist->credits|default:''}
                        </div>
                    </td>
                    <td><a href="{$wwwRoot}cuvantul-zilei/{$nextday}">»</a></td>
                </tr>
            </table>
        </div>
    {/if}

  {if $skinVariables.wotdArchive}
    <p class="paragraphTitle">Arhiva cuvintelor zilei</p>

    <div id="wotdArchive" class="wotdArchive"></div>
    <script>loadAjaxContent('{$wwwRoot}arhiva/cuvantul-zilei/{$timestamp|date_format:'%Y/%m'}','#wotdArchive')</script>

    <div id="oldWotD" class="widgetWotD"></div>
    <script>
      loadAjaxContent('{$wwwRoot}arhiva/cuvantul-zilei-anii-trecuti/{$timestamp|date_format:'%Y/%m/%d'}','#oldWotD');
      topWidgetStart = $('.widgetWotD').position().top;
      $(document).ready(function () {
        $(window).scroll(function (event) {
          $('.widgetWotD').css('top', $(document).scrollTop() + topWidgetStart);
        });
      });
    </script>

    {* Javascript for "Add to favorites" *}
    {if $sUser}
      <script>
       $(function () {
         $('.bookmarkAddButton').click(function () {
           addBookmark($(this));
           return false;
         });
       });
      </script>
    {/if}

    {* Javascript for "Report a typo" *}
    <div id="typoDiv"></div>
    <script>
     $(".typoLink").click(showTypoForm);
    </script>
  {/if}
{/block}
