<ul class="widgetList">
    <li>
        <b>Cuvântul zilei de {$timestamp|date_format:'%e %B'} în alți ani:</b>
    </li>
    {foreach from=$wotds item=w}
    <li>
        <img src="{$w.img}" alt="iconița cuvântului zilei" class="commonShadow"/>
        <span>
            <label>{$w.year}:&nbsp;</label>
            <a href="{$w.href}">{$w.word}</a>
            <br/>
            {if $w.tip}
            <div class="tooltip" title="">{$w.tip}</div>
            {/if}
        </span>
    </li>
    {/foreach}
</ul>