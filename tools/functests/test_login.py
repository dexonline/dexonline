# encoding: utf-8

import unittest2 as unittest
import subprocess
import tempfile
from StringIO import StringIO
import py
import mechanize


_folder = py.path.local(__file__).dirpath().dirpath().dirpath()
config_path = _folder.join('dex.conf')
backup_config_path = _folder.join('dex.conf.TESTBAK')
URL_BASE = 'http://127.0.0.1'


import lxml.cssselect
import lxml.html.soupparser

def parse_html(html):
    return lxml.html.soupparser.fromstring(html)

def csstext(target, selector):
    sel = lxml.cssselect.CSSSelector(selector)
    return ' '.join(e.text_content() for e in sel(target)).strip()


def setUpModule():
    global orig_config
    if backup_config_path.check():
        # previous test run did not finish
        tearDownModule()
    with config_path.open('rb') as f:
        orig_config = f.read()
    config_path.rename(backup_config_path)
    with config_path.open('wb') as f:
        f.write(orig_config +
                "\ndatabase = mysql://root@localhost/DEXtest\n")

    # TODO use http://docs.python.org/library/configparser

    subprocess.check_call(['mysql', '-u', 'root',
                           '-e', 'create database DEXtest character set utf8'])

    p = subprocess.Popen(['mysqldump', '-u', 'root',
                          'DEX', '--no-data'], stdout=subprocess.PIPE)
    sql_dump, p_err = p.communicate()

    tmp_file = tempfile.TemporaryFile()
    with tmp_file:
        tmp_file.write(sql_dump)
        tmp_file.seek(0)
        subprocess.check_call(['mysql', '-u', 'root',
                               'DEXtest'], stdin=tmp_file)

    # create one user for all the tests
    br = mechanize.Browser()
    create_account(br, 'vasile', 'vasile@example.com', 'p@ssw0rd')


def tearDownModule():
    subprocess.check_call(['mysql', '-u', 'root',
                           '-e', 'drop database DEXtest'])
    backup_config_path.rename(config_path)


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
