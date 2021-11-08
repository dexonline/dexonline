$(function() {

  var defaultColor, defaultBg;

  function init() {
    initSelect2('#parentId', 'ajax/getTagsById.php', {
      ajax: { url: wwwRoot + 'ajax/getTags.php', },
      allowClear: true,
      minimumInputLength: 1,
      placeholder: 'caută o etichetă',
      width: '100%',
    });

    /**
     * Frequent colors are colored squares underneath an <input type=color>.
     * Some complexity arises from storing the default fg/bg colors as empty
     * strings (in order to accommodate both the light and dark modes).
     */
    $('.frequent-color').click(frequentColorClick);
    $('#frm-tag-edit').submit(colorSubmit);

    defaultBg = getCssVariable('--c-tag-bg');
    defaultColor = getCssVariable('--c-tag');
    $('.frequent-color-background[data-value=""]').data('value', defaultBg);
    $('.frequent-color-foreground[data-value=""]').data('value', defaultColor);

    if ($('#background').attr('value') == undefined) {
      $('#background').val(defaultBg);
    }
    if ($('#color').attr('value') == undefined) {
      $('#color').val(defaultColor);
    }
  }

  function frequentColorClick() {
    var input = $($(this).data('target'));
    input.val($(this).data('value'));
  }

  // Removes color fields if they have default values.
  function colorSubmit() {
    if ($('#background').val() == defaultBg) {
      $('#background').remove();
    }
    if ($('#color').val() == defaultColor) {
      $('#color').remove();
    }
  }

  /**
   * Returns the value of a CSS variable holding a color. Converts
   * (potentially) named colors like 'white' to hex colors.
   */
  function getCssVariable(name) {
    var style = getComputedStyle(document.documentElement, null);
    var color = style.getPropertyValue(name).trim();

    // convert color
    var ctx = document.createElement('canvas').getContext('2d');
    ctx.fillStyle = color;
    return ctx.fillStyle;
  }

  init();
});
