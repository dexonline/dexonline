<div class="voffset3">
  <button type="submit"
          name="refreshLexem"
          value="1"
          class="lexemEditSaveButton btn btn-default">
    <i class="glyphicon glyphicon-refresh"></i>
    reafișează
  </button>

  <button type="submit"
          name="saveLexem"
          value="1"
          class="lexemEditSaveButton btn btn-default">
    <i class="glyphicon glyphicon-floppy-disk"></i>
    salvează
  </button>

  {if $canEdit.general}
    <button type="submit"
            name="cloneLexem"
            value="1"
            class="btn btn-default">
      <i class="glyphicon glyphicon-duplicate"></i>
      clonează
    </button>
  {/if}

  <a href="?lexemId={$lexem->id}">renunță</a>

  {if $canEdit.loc || !$lexem->isLoc}
    <button type="submit"
            name="deleteLexem"
            value="1"
            onclick="return confirm('Confirmați ștergerea acestui lexem?');"
            class="btn btn-danger pull-right"
            {if $lexem->isLoc}disabled="disabled"{/if}>
      <i class="glyphicon glyphicon-trash"></i>
      șterge
    </button>
  {/if}
  
</div>
