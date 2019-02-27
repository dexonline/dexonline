<?php

Smart::assign('wikiTitles', WikiArticle::loadAllTitles());
Smart::display('article/list.tpl');
