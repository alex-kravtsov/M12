<?php

use M12_Engine\Models\Releases\Peer as ReleasePeer;
use M12_Engine\Models\Releases\Item as ReleaseModel;
use M12_Engine\Models\Files\Item as FileModel;

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
    switch($task){
    case 'release_create':
        if(!is_array($files) || empty($files) ){
            throw new Exception("Cannot create release without files.");
        }

        $release_peer = new ReleasePeer();
        $releases = $release_peer->get(array(
            "filterby" => array(
                array(
                    "column" => "completed_at",
                    "condition" => "is null",
                ),
            ),
        ));
        if(!empty($releases) ){
            throw new Exception("Incomplete release.")
        }

        $file_model = new FileModel();
        $file_ids = array();
        foreach($files as $file){
            $file_ids[] = $file_model->update($file);
        }

        $release_model = new ReleaseModel();
        $release_id = $release_model->create(array(
            "label" => $release_title,
            "files" => $file_ids,
        ));

        $release_model->beginBetaReplication($release_id);

        break;
    case 'release_complete':
        if(empty($release_id) || !is_numeric($release_id) ){
            throw new Exception("Invalid release ID.");
        }

        $release_peer = new ReleasePeer();
        $releases = $release_peer->get(array(
            "filterby" => array(
                array(
                    "column" => "id",
                    "condition" => "equals",
                    "rvalue" => $release_id,
                ),
            ),
        ));
        if(empty($releases) ){
            throw new Exception("Invalid release ID.");
        }

        $release = $releases[0];
        if($release["beta_error"] || !$release["beta_completed_at"]){
            throw new Exception("Beta replication is incompleted.");
        }

        $release_model = new ReleaseModel();
        $release_model->beginProductionReplication($release["id"]);

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
