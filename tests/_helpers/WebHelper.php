<?php

namespace Tests\System\_helpers;

// here you can define custom functions for WebGuy

use Codeception\Module;

class WebHelper extends \Codeception\Module
{
    /**
     * Custom method to handle common test setup
     */
    public function amOnPageAndWait($page, $waitSeconds = 1)
    {
        $webDriver = $this->getModule('WebDriver');
        $webDriver->amOnPage($page);
        $webDriver->wait($waitSeconds);
    }

    /**
     * Helper method to click and wait
     */
    public function clickAndWait($element, $waitSeconds = 0.5)
    {
        $webDriver = $this->getModule('WebDriver');
        $webDriver->click($element);
        $webDriver->wait($waitSeconds);
    }

    /**
     * Forward method calls to WebDriver module
     */
    public function __call($method, $args)
    {
        $webDriver = $this->getModule('WebDriver');
        return call_user_func_array([$webDriver, $method], $args);
    }
}
