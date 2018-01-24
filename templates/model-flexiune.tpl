{extends "layout.tpl"}

{block "title"}Model de flexiune{/block}

{block "content"}
    <h3>
        Modelul de flexiune {$model->modelType} {$model->number}
    </h3>

    <p>
        <a class="btn btn-default" href="#" onclick="window.history.back();">
            <i class="glyphicon glyphicon-chevron-left"></i>
            înapoi
        </a>
    </p>

    <h4>
        {$model->modelType} {$model->number}. {$model->getHtmlExponent()}
    </h4>

    <b>Descriere: {$model->description}</b>
    {include "paradigm/paradigm.tpl" lexeme=$lexeme}
{/block}
