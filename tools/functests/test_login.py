# encoding: utf-8
from StringIO import StringIO
import nose
import ConfigParser
import lxml.cssselect
import lxml.html.soupparser
import mechanize
import py
import re
import subprocess
import sys
import tempfile
import unittest2 as unittest

CONFIG_FILE = py.path.local(__file__).dirpath().dirpath().dirpath().join('dex.conf').strpath
CONFIG = ConfigParser.ConfigParser()
CONFIG.read(CONFIG_FILE)
LOCK_FILE = py.path.local(CONFIG.get('functest', 'functestLockFile'))
URL_BASE = CONFIG.get('functest', 'baseUrl').strip(' \'"')

def parseDsn(dsn):
    dsnPattern = re.compile("(^\w+)://(\w+)(:(\w+))?@(\w+)(:(\d+))?/(\w+)$");
    match = dsnPattern.match(dsn)
    if match is None:
        return None
    groups = match.groups()
    assert groups[0] == 'mysql' # we can only handle the mysql protocol
    assert groups[6] is None # we cannot handle port numbers
    return { 'user': groups[1], 'password': groups[3], 'host': groups[4], 'database': groups[7] }

# Takes a dictionary obtained with parseDsn
def getMysqlArgs(dict):
    result = ['-u', dict['user'], '-h', dict['host']]
    if dict['password'] is not None:
        result.append('--password="' + dict['password'] + '"')
    result.append(dict['database'])
    return result

def parse_html(html):
    return lxml.html.soupparser.fromstring(html)

def csstext(target, selector):
    sel = lxml.cssselect.CSSSelector(selector)
    return ' '.join(e.text_content() for e in sel(target)).strip()

def assertElementExists(page, selector):
    sel = lxml.cssselect.CSSSelector(selector)
    assert len(sel(page)) == 1

def setUpModule():
    if LOCK_FILE.check():
        # previous test run did not finish
        tearDownModule()
    LOCK_FILE.ensure() # touch the lock file

    # copy the schema from the database
    mainDbDict = parseDsn(CONFIG.get('global', 'database'))
    mainDbArgs = getMysqlArgs(mainDbDict)
    functestDbDict = parseDsn(CONFIG.get('functest', 'functestDatabase'))
    functestDbArgs = getMysqlArgs(functestDbDict)

    subprocess.check_call(['mysql'] + mainDbArgs + ['-e', 'create database %s character set utf8' % functestDbDict['database']])
    p = subprocess.Popen(['mysqldump'] + mainDbArgs + ['--no-data'], stdout=subprocess.PIPE)
    sql_dump, p_err = p.communicate()

    tmp_file = tempfile.TemporaryFile()
    with tmp_file:
        tmp_file.write(sql_dump)
        tmp_file.seek(0)
        subprocess.check_call(['mysql'] + functestDbArgs, stdin=tmp_file)

    # create one user for all the tests
    br = mechanize.Browser()
    create_account(br, 'vasile', 'vasile@example.com', 'p@ssw0rd')


def tearDownModule():
    functestDbDict = parseDsn(CONFIG.get('functest', 'functestDatabase'))
    functestDbArgs = getMysqlArgs(functestDbDict)
    subprocess.check_call(['mysql'] + functestDbArgs + ['-e', 'drop database if exists %s' % functestDbDict['database']])
    LOCK_FILE.remove()


def click_first(br, **kwargs):
    links = list(br.links(**kwargs))
    assert links
    resp = br.follow_link(links[0])
    return resp


def create_account(br, nick, email, password, password2 = None):
    br.open(URL_BASE + '/inregistrare').read()
    br.select_form(name='inregistrare')
    br['nick'] = nick
    br['password'] = password
    br['password2'] = password2 if password2 else password
    br['email'] = email
    resp = br.submit()
    return resp


def log_in(br, email, password):
    br.open(URL_BASE + '/')
    click_first(br, text_regex='^Conectare$')
    br.select_form(name='loginForm')
    br['email'] = email
    br['password'] = password
    resp = br.submit()
    return resp

# Note that we can only set/get the first checkbox in an array of 5 checkboxes all having the name "userPrefs[]"
# This seems to be a bug in mechanize:
#   forms = [f for f in br.forms()]
#   print forms[1].controls
# This prints a bunch of text/password controls, but only ONE CheckboxControl
def edit_account(br, nick, password, new_password, new_password2, name, email, design, pref_st):
    br.open(URL_BASE + '/preferinte').read()
    br.select_form(name='accountForm')
    if nick: br['nick'] = nick
    if password: br['curPass'] = password
    if new_password: br['newPass'] = new_password
    if new_password2: br['newPass2'] = new_password2
    if name: br['name'] = name
    if email: br['email'] = email
    if design: br['skin'] = [design]
    if pref_st is not None: br.find_control('userPrefs[]').items[0].selected = pref_st
    resp = br.submit()
    return resp

class LoginTest(unittest.TestCase):

    def test_register_bad_info(self):
        br = mechanize.Browser()
        resp = create_account(br, '', '', '')
        html = resp.read()
        self.assertIn('Trebuie să vă alegeți un nume de cont', html)

        resp = create_account(br, 'john', '', '')
        html = resp.read()
        self.assertIn('Trebuie să vă alegeți o parolă', html)

        resp = create_account(br, 'john', 'john@john.com', 'password', 'mismatched password')
        html = resp.read()
        self.assertIn('Parolele nu coincid', html)

        resp = create_account(br, 'vasile', 'john@john.com', 'password')
        html = resp.read()
        self.assertIn('Acest nume de cont este deja folosit', html)

        resp = create_account(br, 'john', 'vasile@example.com', 'password')
        html = resp.read()
        self.assertIn('Această adresă de e-mail este deja folosită', html)

    def test_create_account(self):
        br = mechanize.Browser()
        resp = create_account(br, 'newuser', 'newuser@example.com', 'p@ssw0rd')
        html = resp.read()
        self.assertIn('Înscrierea s-a făcut cu succes! Acum vă puteți', html)

        login_links = list(br.links(text_regex='^conecta$'))
        self.assertEqual(len(login_links), 1)

    def test_log_in(self):
        br = mechanize.Browser()
        resp = log_in(br, 'vasile@example.com', 'p@ssw0rd')
        page = parse_html(resp.read())
        self.assertEqual(csstext(page, 'ul#userMenu li#userNick > a'), 'vasile')

    def test_log_out(self):
        br = mechanize.Browser()
        resp = log_in(br, 'vasile@example.com', 'p@ssw0rd')
        resp = click_first(br, text_regex='^Deconectare$')
        html = resp.read()
        self.assertNotIn('vasile', html)
        self.assertIn('Anonim', html)

    def test_my_account(self):
        br = mechanize.Browser()
        log_in(br, 'vasile@example.com', 'p@ssw0rd')
        resp = edit_account(br, nick = None, password = None, new_password = None, new_password2 = None,
                            name = None, email = None, design = None, pref_st = None)
        html = resp.read()
        self.assertIn('Parola actuală este incorectă', html)

        resp = edit_account(br, nick = None, password = 'p@ssw0rd', new_password = None, new_password2 = None,
                            name = 'Vasile Popescu', email = None, design = 'polar', pref_st = True)
        html = resp.read()
        page = parse_html(html)
        # Note the ţ instead of ț below, since we checked cb_CEDILLA_BELOW
        self.assertEqual(csstext(page, 'div.flashMessage'), u'Informaţiile au fost salvate.')
        assertElementExists(page, 'input[name=name][value="Vasile Popescu"]')
        assertElementExists(page, 'input#cb_CEDILLA_BELOW[checked=checked]')
        self.assertEqual(csstext(page, 'select[name=skin] option[selected=selected]'), 'Polar')
