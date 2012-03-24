var lives=6;
var cale_poze="poze_spnz/image";
function gameOver(form) {
  if(lives == 0) {
    form.end.value="Ne pare rău ai pierdut";
    form.hint.disabled = 'true';
    var i=0;
    for(i=0;i<word.length;i++) {
      form["out"+i].value = word.charAt(i).toUpperCase();
    }
    document.getElementById("def").style.display = "inline";
  }
  else if(match == 0) {
    form.hint.disabled = 'true';
    form.end.value="Felicitări, ai câștigat!!";
    document.getElementById("def").style.display = "inline";
  }
}
function letterPressed(letter,form, field) { 
  var i=0, ok = 0;
  if(lives == 0 || match == 0) {
    return;
  }
  field.disabled = 'true';
  for(i=0;i<word.length;i++) {
    if(letter == word.charAt(i)) {
      form["out"+i].value = letter.toUpperCase();
      ok = 1;
      match--;
    }
  }
  form["in"+letter].style.fontWeight="bold";
  if(ok == 1) {
    form["in"+letter].style.color="blue";
  }
  else {
     lives--;
     form.poza.src= cale_poze + lives + '.gif';
     form["in"+letter].style.color="red";
  }
  form.vieti.value=lives;
  gameOver(form);
}
 
function resetFields(form) {
  var i=0;
  form.vieti.value=lives;
  for(i=0;i<word.length;i++) {
    form["out"+i].value = "";
  }
  form.end.value="";
}
function Hint(form) {
 // alert(word);
  for(i=0;i<word.length;i++) {
    //alert(form["in" + word.charAt(i)].disabled);
    if(form["in" + word.charAt(i)].disabled == false) {
     // alert("da");
      letterPressed(word.charAt(i), form, form["in"+word.charAt(i)]);
      form["in"+word.charAt(i)].style.color="green";
      break;
    }
  }  
  if(lives >=2 )
    lives-=2;
  else
    lives=0;
  form.poza.src= cale_poze + lives + '.gif';
  form.vieti.value=lives; 
  gameOver(form);
}
function newGame(form, a) {
  form.hint.disabled = 'false';
  difficulty = a;
  window.location = "spnz.php?d=" + difficulty;
}

