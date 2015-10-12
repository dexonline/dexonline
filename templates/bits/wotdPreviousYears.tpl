<ul class="widgetList">
    <li>
        <b>Cuvântul zilei de {$timestamp|date_format:'%e %B'} în alți ani:</b>
    </li>
    {foreach from=$wotds item=w}
    <li>
        <img src="{$w.img}" alt="iconiță cuvântul zilei" class="commonShadow"/>
    <span>
        <label>În anul {$w.year}</label>
        <br/>
        <a href="{$w.href}">{$w.word}</a>
        {if $w.tip}
        <span class="tooltip2" title="{$w.tip}">&nbsp;</span>
        {/if}
    </span>
    </li>
    {/foreach}
</ul>