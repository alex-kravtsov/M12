<?php

namespace M12_Engine\Core;

use M12_Engine\Core\Database;
use M12_Engine\Settings\Settings;

abstract class Factory {

    public static function getDatabase(){
        return Database::getInstance();
    }

    public static function getSettings(){
        return Settings::getInstance();
    }
}
