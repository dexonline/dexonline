# encoding: utf-8
from StringIO import StringIO
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


def create_account(br, nick, email, password):
    br.open(URL_BASE + '/inregistrare').read()
    br.select_form(name="inregistrare")
    br['nick'] = nick
    br['password'] = password
    br['password2'] = password
    br['email'] = email
    resp = br.submit()
    return resp


def log_in(br, email, password):
    br.open(URL_BASE + '/')
    click_first(br, text_regex='^Conectare$')
    br.select_form(name="loginForm")
    br['email'] = email
    br['password'] = password
    resp = br.submit()
    return resp


class LoginTest(unittest.TestCase):

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
        self.assertEqual(csstext(page, 'ul#userMenu li#userNick > a'),
                         "vasile")

    def test_log_out(self):
        br = mechanize.Browser()
        resp = log_in(br, 'vasile@example.com', 'p@ssw0rd')
        resp = click_first(br, text_regex='^Deconectare$')
        html = resp.read()
        self.assertNotIn('vasile', html)
        self.assertIn('Anonim', html)
