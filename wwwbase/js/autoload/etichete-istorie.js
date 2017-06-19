$(function() {

  $('ins, del').each(function() {
    $(this).html($(this).text().split(' ').join('␣&#8203;'));
  });

  initSelect2('#tagIds', 'ajax/getTagsById.php', {
    ajax: { url: wwwRoot + 'ajax/getTags.php' },
    minimumInputLength: 1,
  });

});
