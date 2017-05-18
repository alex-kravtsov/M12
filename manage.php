<?php

use M12_Engine\Controllers\Manager;

require_once dirname(__FILE__) . "/engine/autoload.php";

/**
 * @var string $task What to do. Possible values: release_create|release_complete
 * @var array $files Required if $task is release_create. Release files.
 * @var string $release_title Optional. If $task is release_create, will be used as human readable release title.
 * @var int $release_id Required if $task is release_complete. Release ID.
 */

$task = null;
$files = null;
$release_title = null;
$release_id = null;

try {
    $manager = new Manager();
    switch($task){
    case 'release_create':
        $manager->createRelease($files, $release_title);
        break;
    case 'release_complete':
        $manager->completeRelease($release_id);
        break;
    default:
        throw new Exception("Invalid task.");
    }
}
catch(Exception $e){
    $output = "Error:\n";
    $output .= "Message: " . $e->getMessage() . "\n";
    $output .= "File: " . $e->getFile() . "\n";
    $output .= "Line: " . $e->getLine() . "\n";
    $output .= "Trace:\n" . $e->getTraceAsString() . "\n";
    echo $output;
}
