#!/usr/bin/env python
# To run this script:
# - move tools/old/ormTest.php into wwwbase/
# - edit the baseUrl in the PageLoader() constructor in main()
# - create a virtual environment in the same directory as this file:
#
#    virtualenv sandbox
#
# - install the required packages:
#
#    sandbox/bin/pip install -r requirements.txt
#
# - install any additional packages required (e.g. python-mysqldb)
# - run the benchmark:
#
#    sandbox/bin/python simpletest.py

from time import time
from contextlib import contextmanager
import sqlalchemy
import wsgiproxy.app
import webob


def get_random_lexems():
    import random

    engine = sqlalchemy.create_engine('mysql://root@localhost/DEX')
    metadata = sqlalchemy.MetaData(engine)
    Lexem = sqlalchemy.Table('Lexem', metadata, autoload=True, autoload_with=engine)

    desc = sqlalchemy.sql.expression.desc
    top = Lexem.select().order_by(desc(Lexem.c.id)).limit(1).execute().fetchone()
    max_id = top.id

    while True:
        query = Lexem.select(Lexem.c.id == random.randint(0, max_id))
        lexem = query.execute().fetchone()
        if lexem is not None:
            yield lexem


class PageLoader(object):
    def __init__(self, base_url):
        self.app = wsgiproxy.app.WSGIProxyApp(base_url)

    def get(self, url):
        req = webob.Request.blank(url)
        req.environ['REMOTE_ADDR'] = ''
        return req.get_response(self.app)


class TimerData(object): pass

@contextmanager
def timer():
    ob = TimerData()
    t0 = time()
    yield ob
    ob.duration = time() - t0


def main():
    loader = PageLoader('http://localhost/~cata/DEX2/wwwbase')
    lexems = iter(get_random_lexems())

    adodb = idiorm = 0

    for c in xrange(100):
        lid = next(lexems).id
        with timer() as t:
            loader.get('/ormTest.php?orm=adodb&lexemId=%d' % lid)
        adodb += t.duration

        lid = next(lexems).id
        with timer() as t:
            loader.get('/ormTest.php?orm=idiorm&lexemId=%d' % lid)
        idiorm += t.duration

    print 'adodb:', adodb
    print 'idiorm:', idiorm


if __name__ == '__main__':
    main()
