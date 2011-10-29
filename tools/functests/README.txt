To run functional tests:

* set up a Python virtualenv:

    $ sudo apt-get install python-virtualenv
    $ virtualenv sandbox

* install libraries:

    $ sudo apt-get install python-dev libxml2-dev libxslt1-dev
    $ sandbox/bin/pip install -r requirements.txt

* run the test suite:

    $ sandbox/bin/nosetests -s
