<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class System {

    const OS_UNKNOWN = 1;
    const OS_WIN = 2;
    const OS_LINUX = 3;
    const OS_OSX = 4;

    /**
     * @return int
     */
    static public function getOS() {
        switch (true) {
            case stristr(PHP_OS, 'DAR'): return self::OS_OSX;
            case stristr(PHP_OS, 'WIN'): return self::OS_WIN;
            case stristr(PHP_OS, 'LINUX'): return self::OS_LINUX;
            default : return self::OS_UNKNOWN;
        }
    }
    
    /**
     * @return string
     */
    static public function getCatCommand() {
        switch (self::getOS()) {
            case self::OS_WIN: return "type";
            default : return "cat";
        }
    }
    
    /**
     * @return string
     */
    static public function getCorrectPath($path) {
        switch (self::getOS()) {
            case self::OS_WIN: return str_replace("/", "\\", $path);
            default : return str_replace("\\", "/", $path);
        }
    }

}