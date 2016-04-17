$(function() {
  var year = 0;
  var month = 0;
  var stemRow = null;   // stem row from which we clone other rows
  var artists = []; // id => name map
  var selectedArtist = null;

  function init() {
    // build the artist id => name map
    $('#artists tbody tr').each(function() {
      var key = $(this).data('id');
      var value = $(this).find('td').text().trim();
      artists[key] = value;
    });

    $('.monthNav').click(changeMonth);
    $('.artistRow').click(selectArtist);
    $('.calendarRow').click(assignArtist);
    stemRow = $('#stem').detach().removeAttr('id');
    refreshCalendar();
  }

  function refreshCalendar() {
    $('#calendar tbody').empty();
    $.ajax({
      url: wwwRoot + 'ajax/getWotdAssignments.php',
      data: { year: year, month: month },
    }).done(function(response) {
      year = response.year;
      month = response.month;
      $('#monthName').text(response.date);
      for (var day in response.artists) {
        var row = stemRow.clone(true).appendTo($('#calendar tbody'));
        var artistId = response.artists[day];
        var name = artists[artistId];
        if (day == 37) {
          alert(row.html());
        }
        row.find('.day').text(day);
        row.find('.artist').text(name);
      }
    });
  }

  function changeMonth() {
    var delta = $(this).data('delta');
    month += delta;
    if (month < 1) {
      month = 12;
      year--;
    } else if (month > 12) {
      month = 1;
      year++;
    }
    refreshCalendar();
  }

  function selectArtist() {
    selectedArtist = $(this).data('id');
    $('.selectedArtist').removeClass('selectedArtist');
    $(this).addClass('selectedArtist');
  }

  function assignArtist() {
    if (selectedArtist == null) {
      alert('Alegeți mai întâi un artist din stânga.');
    } else {
      var row = $(this);
      var artistName = artists[selectedArtist];
      var day = row.find('.day').text();
      $.ajax({
        url: wwwRoot + 'ajax/assignWotdArtist.php',
        data: {
          year: year,
          month: month,
          day: day,
          artistId: selectedArtist,
        },
      }).done(function() {
        row.find('.artist').text(artistName);
      });
    }
  }

  init();
});
