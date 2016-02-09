var wa_year = 0;
var wa_month = 0;
var wa_row = null;
var wa_artists = [];
var wa_selectedArtist = null;

function wotdAssignmentInit() {
  // build the artist id => name map
  $('#artists tbody tr').each(function() {
    var key = $(this).data('id');
    var value = $(this).find('td').text().trim();
    wa_artists[key] = value;
  });

  $('.monthNav').click(changeMonth);
  $('.artistRow').click(selectArtist);
  $('.calendarRow').click(assignArtist);
  wa_row = $('#stem').detach().removeAttr('id');
  refreshCalendar();
}

function refreshCalendar() {
  $('#calendar tbody').empty();
  $.ajax({
    url: wwwRoot + 'ajax/getWotdAssignments.php',
    data: { year: wa_year, month: wa_month },
  }).done(function(response) {
    wa_year = response.year;
    wa_month = response.month;
    $('#monthName').text(response.date);
    for (var day in response.artists) {
      var row = wa_row.clone(true).appendTo($('#calendar tbody'));
      var artistId = response.artists[day];
      var artist = wa_artists[artistId];
      row.find('.day').text(day);
      row.find('.artist').text(artist);
    }
  });
}

function changeMonth() {
  var delta = $(this).data('delta');
  wa_month += delta;
  if (wa_month < 1) {
    wa_month = 12;
    wa_year--;
  } else if (wa_month > 12) {
    wa_month = 1;
    wa_year++;
  }
  refreshCalendar();
}

function selectArtist() {
  wa_selectedArtist = $(this).data('id');
  $('.selectedArtist').removeClass('selectedArtist');
  $(this).addClass('selectedArtist');
}

function assignArtist() {
  if (wa_selectedArtist == null) {
    alert('Alegeți mai întâi un artist din stânga.');
  } else {
    var row = $(this);
    var artistName = wa_artists[wa_selectedArtist];
    var day = row.find('.day').text();
    $.ajax({
      url: wwwRoot + 'ajax/assignWotdArtist.php',
      data: {
        year: wa_year,
        month: wa_month,
        day: day,
        artistId: wa_selectedArtist,
      },
    }).done(function() {
      row.find('.artist').text(artistName);
    });
  }
}
