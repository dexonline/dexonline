{assign var="ifs" value=$lexem->loadInflectedForms()}
    <Lexem>
      <Id>{$lexem->id}</Id>
      <Timestamp>{$lexem->modDate}</Timestamp>
      <Form>{$lexem->form|escape}</Form>
      {if $lexem->description}
        <Description>{$lexem->description|escape}</Description>
      {/if}
      {foreach from=$ifs item=if}
        <Inflection>
          <IId>{$if->inflectionId}</IId>
          <IForm>{$if->form|escape}</IForm>
        </Inflection>
      {/foreach}
    </Lexem>

