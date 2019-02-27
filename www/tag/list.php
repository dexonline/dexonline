<?php

Smart::assign('tags', Tag::loadTree());
Smart::addResources('admin');
Smart::display('tag/list.tpl');
