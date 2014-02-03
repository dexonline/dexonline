[[TOC]]

= Acces la codul-sursă =

Codul ''DEX online'' este disponibil liber (și gratuit) sub licența Affero GPL. Iată cum puteți să creați o copie funcțională a ''DEX online'' și să contribuiți cu patch-uri.

== Vagrant ==

Ca alternativă la această pagină, Alex Morega menține o [https://github.com/mgax/dexonline-vagrant listă de instrucțiuni] pentru dezvoltarea într-un mediu virtual prin Vagrant. Poate fi mai rapid de instalat -- YMMV.

== Cerințe de sistem ==

El a fost testat pe sisteme Ubuntu și Fedora standard. Aveți nevoie cel puțin de:

 * PHP >= 5.0
   * Modulele '''curl''', '''mbstring''', '''mysql''' și '''zlib''' sunt strict necesare.
   * Modulul php5-imagick este necesar pentru dicționarul vizual.
   * Modulele '''apc''' și '''memcache''' sunt opționale.
 * MySQL >= 5.0
 * Apache HTTP Server >= 2.0
 * Subversion sau Git

== Convenții de editare ==

 * Asigurați-vă că sistemul dumneavoastră permite citirea și tastarea diacriticelor românești ĂÂÎȘȚ.  În particular, asigurați-vă că puteți folosi diacriticele Ș și Ț cu virgulă (nu Ş și Ţ cu sedilă). Editorul dumneavoastră trebuie să poată deschide și salva fișiere fără a corupe semnele diacritice din ele. Orice sistem GNU/Linux modern se descurcă perfect cu aceste simboluri. Sub Gnome, singura operație necesară este adăugarea unei mapări românești de tastatură ''(System / Preferences / Keyboard / Layouts / Add / Country:Romania).'' Cătălin folosește varianta simplă (Romania), care practic lasă tastatura originală nemodificată și adaugă diacriticele românești folosind talta !AltGr (Alt din dreapta).
 * Fișierele sunt scrise cu indentare de două spații, fără taburi. Aceasta este doar o convenție. Dacă preferați un alt stil, putem să îl adoptăm, dar trebuie să scrieți un script care să modifice toată baza de cod. Dacă ne oferiți și un fișier .emacs care să formateze codul în stilul dumneavoastră, este și mai bine. :-)
 * Majoritatea identificatorilor sunt „camelCased”, fără underscores: {{{$numeVariabilă}}}, {{{NumeClasă}}}, {{{$this->numeCâmp}}}.
 * Constantele sunt scrise cu litere mari și cu underscore: {{{NUME_CONSTANTĂ}}}.
 * Lățimea ecranului nu este bătută în cuie; credem că până la 160 de caractere nu se va supăra nimeni.
 * Pentru cuvintele-cheie ({{{for, foreach, if, while, case}}} etc) se lasă spațiu în afara parantezelor, nu și înăuntrul lor.
 * Pentru apeluri de funcții, nu se lasă loc între numele funcției și paranteza deschisă.
 * Se lasă spațiu în afara acoladelor.
 * Se lasă spațiu după virgule.
 * Orice acoladă deschisă stă pe aceeași linie cu instrucțiunea if / else etc. precedentă și este urmată de o linie nouă.
 * Orice acoladă închisă este singură pe linia ei.
 * Folosim acolade și dacă blocul if / else / while etc. constă dintr-o singură instrucțiune.

{{{
#!php
  if (($y > 3) && ($y < 10)) {
    while ($z < 10) {
      $z++;
      callMyFunction($y, $z);      
    }
  }
}}}

== Variante de instalare ==

Există două variante de instalare a codului și un pas opțional pentru oricare dintre ele. Instrucțiunile de instalare depind în mică măsură de alegerea făcută.

 1. '''userdir''': în directorul utilizatorului, de obicei `/home/user/public_html/DEX`. URL-ul de acces este `http://localhost/~user/DEX/wwwbase`
 2. '''document root''': în directorul-rădăcină al lui Apache, de obicei `/var/www/DEX`. URL-ul de acces este `http://localhost/DEX/wwwbase`

Opțional:

 3. '''virtual host''': pentru oricare din variantele 1 și 2, puteți crea un site virtual. URL-ul de acces este `http://dex.domeniulmeu.com` (desigur, presupunând că dețineți domeniul `domeniulmeu.com`, ați creat subdomeniul `dex.domeniulmeu.com` și sunteți pe o conexiune cu IP static).

Pe parcursul acestui document ne vom referi la aceste trei variante pentru instrucțiuni specifice.

== Instalarea și configurarea codului și a bazei de date ==

Alegeți-vă directorul unde doriți să lucrați ('''userdir''': `/home/user/public_html/DEX` sau '''document root''': `/var/www/DEX`). Descărcați o copie a codului sursă:

{{{
#!sh
cd /home/user/public_html
# sau cd /var/www
svn checkout http://voronet.francu.com/repos/DEX
cd DEX
}}}

Dacă preferați Git în loc de Subversion, Alex Morega întreține și o clonă Git a codului:

{{{
#!sh
git clone git://github.com/mgax/DEXonline
}}}


Configurați codul pentru prima dată. Acest pas poate produce diverse erori, deoarece scriptul setup.sh nu este bine pus la punct.

{{{
#!sh
tools/setup.sh
}}}

Modificați fișierul {{{dex.conf}}} conform cu setările sistemului. Acest fișier nu este sub controlul lui Subversion, deci este acceptabil să stocați parola pentru baza de date. În special:

 * Modificați valoarea variabilei {{{database}}} conform [http://pear.php.net/manual/en/package.database.db.intro-dsn.php specificației DSN].

Descărcați baza de date a ''DEX online'' și importați-o în MySQL. De asemenea, migrați schema bazei de date la ultima versiune pentru cazul (improbabil) în care codul din Subversion este mai nou decât codul care rulează pe dexonline.ro.

{{{
#!sh
mysql -u ... -p ... -e "create database DEX character set utf8"
wget -O /tmp/dex-database.sql.gz http://dexonline.ro/download/dex-database.sql.gz
zcat /tmp/dex-database.sql.gz | mysql -u ... -p ... DEX
php tools/migration.php
}}}

Dacă veți avea nevoie să testați pagini ale moderatorului, trebuie să vă creați un cont cu permisiuni de moderator. Dacă aveți deja cont pe DEX online, rulați comanda MySQL:

{{{
#!sql
update User set moderator = 31 where email = 'adresa_de_email';
}}}

Altfel, creați-vă un cont și apoi executați comanda de mai sus. Puteți să vă creați un cont chiar pe dexonline.ro, altfel cel creat local se va pierde când veți recopia baza de date. Există diferite niveluri de privilegii, pentru editarea definițiilor, editare LOC, gestionarea cuvântului zilei etc. Valoarea 31 le include pe toate (cu OR).

== Configurarea Apache ==

Aceste instrucțiuni sunt pentru Apache cu mod_php5 sub Ubuntu. Le puteți folosi cu titlu orientativ pe orice alt sistem, dar fișierele exacte pe care trebuie să le modificați pot diferi. Toate comenzile și editările se execută ca root.

 * Permiteți execuția de cod PHP.

{{{
#!sh
apt-get install libapache2-mod-php5
}}}

 * Activați modulul rewrite:

{{{
#!sh
a2enmod rewrite
}}}

 * Numai pentru '''userdir''': Activați modulul userdir

{{{
#!sh
a2enmod userdir
}}}

 * Numai pentru '''userdir''': În `/etc/apache2/mods-available/php5.conf` comentați secțiunea

{{{
# <IfModule mod_userdir.c>
#     <Directory /home/*/public_html>
#         php_admin_value engine Off
#     </Directory>
# </IfModule>
}}}

 * Numai pentru '''userdir''': Permiteți fișiere `.htaccess`. În `/etc/apache2/mods-available/userdir.conf` modificați secțiunea

{{{
<Directory /home/*/public_html>
    ...
    AllowOverride All
    ...
</Directory>
}}}

 * Numai pentru '''document root''': Permiteți fișiere `.htaccess`. În `/etc/apache2/sites-available/default` modificați secțiunea

{{{
<Directory /var/www/>
    ...
    AllowOverride All
    ....
</Directory>
}}}

 * Alegeți setul de caractere UTF-8. În `/etc/apache2/conf.d/charset`, decomentați sau adăugați linia

{{{
AddDefaultCharset UTF-8
}}}

 * Numai pentru '''userdir''', fără '''virtual host''': Editați `DEX/wwwbase/.htaccess` și decomentați/modificați linia:

{{{
RewriteBase /~user/DEX/wwwbase/
}}}

 * Numai pentru '''virtual host''': Creați fișierul `/etc/apache2/sites-available/dex.domeniulmeu.com`:

{{{
<VirtualHost *:80>
        DocumentRoot /path/to/DEX/wwwbase
        ServerName dex.domeniulmeu.com
</VirtualHost>
}}}

apoi

{{{
#!sh
a2ensite dex.domeniulmeu.com
}}}

 * Reporniți Apache:

{{{
#!sh
sudo /etc/init.d/apache2 restart
}}}

 * Accesați una din paginile
   * '''userdir''': `http://localhost/~user/DEX/wwwbase`
   * '''document root''': `http://localhost/DEX/wwwbase`
   * '''virtual host''': `http://dex.domeniulmeu.com`

Dacă întâmpinați probleme netratate aici, vă rugăm contactați-ne ca să actualizăm acest document.

== Ținerea la zi ==

Pentru a ține ulterior clientul la zi, rulați:

{{{
#!sh
svn update
php tools/migration.php
}}}

Modificările schemei bazei de date se fac exclusiv prin patchuri în directorul {{{patches/}}}. De aceea, ultimii pași nu sunt necesari decât atunci când apar fișiere noi în directorul {{{patches/}}}.

O situație mai delicată au fișierele {{{dex.conf}}} și {{{wwwbase/.htaccess}}}. Acestea nu sunt sub controlul lui Subversion, deoarece sunt prea dependente de sistem. Ele sunt copiate după fișierele-șablon {{{dex.conf.sample}}} și respectiv {{{docs/.htaccess}}}. Când fișierele-șablon se modifică, trebuie să încorporați noutățile și în fișierele propriu-zise.

== Contribuții la baza de cod ==

Accesul anonim la cod oferă doar drept de citire; nu veți avea permisiunea să rulați {{{svn commit}}}. Pentru a ne trimite modificările făcute, executați comenzile:

{{{
#!sh
svn update            # pentru a fi siguri că diff-ul se aplică pe cea mai nouă versiune a codului
svn diff > /tmp/diff.txt
}}}

Apoi trimiteți-ne prin [mailto:contact@dexonline.ro email] fișierul {{{/tmp/diff.txt}}}. Atenție, trebuie să anexați fișierul separat, nu doar să îl includeți în corpul mesajului. De asemenea, trebuie să includeți separat orice fișiere nou adăugate -- Subversion nu le include automat în diff.

Dacă doriți să contribuiți pe termen lung, vă vom oferi (destul de ușor) drept de commit.
