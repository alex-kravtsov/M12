<?php

namespace M12_Engine\Settings;

class Settings {

    private static $_instance = null;

    public $db_host = "localhost";
    public $db_user = "test";
    public $db_password = "";
    public $db_name = "test";

    private function __construct(){
    }

    public static function getInstance(){
        if(empty(self::$_instance) ){
            self::$_instance = new Settings();
        }
        return self::$_instance;
    }
}
