<?php

use M12_Engine\Models\Releases\Peer as ReleasePeer;
use M12_Engine\Models\Releases\Item as ReleaseModel;
use M12_Engine\Models\Files\Peer as FilePeer;
use M12_Engine\Models\Servers\Peer as ServerPeer;
use M12_Engine\Models\Servers\Item as ServerModel;
use M12_Engine\Models\Logs\Release\Item as ReleaseErrorLog;

require_once realpath(dirname(__FILE__) . "/../engine/autoload.php");

try {
    $release_peer = new ReleasePeer();
    $releases = $release_peer->get(array(
        "filterby" => array(
            array(
                "column" => "beta_replication",
                "condition" => "equals",
                "rvalue" => 1,
            ),
            array(
                "column" => "beta_completed_at",
                "condition" => "is null",
            ),
        ),
    ));

    if(!empty($releases[0]) ){
        $release_id = $releases[0]["id"];

        $file_peer = new FilePeer();
        $files = $file_peer->get(array(
            "filterby" => array(
                "table" => "t_map",
                "column" => "release_id",
                "condition" => "equals",
                "rvalue" => $release_id,
            ),
        ));
        if(empty($files) ){
            throw new Exception("Cannot get release files.");
        }

        $server_peer = new ServerPeer();
        $servers = $server_peer->get(array(
            "filterby" => array(
                "column" => "dev",
                "condition" => "equals",
                "rvalue" => 1,
            ),
        ));
        if(empty($servers[0]) ){
            throw new Exception("Cannot get developer server.");
        }
        $dev_server = $servers[0];

        $servers = $server_peer->get(array(
            "filterby" => array(
                "column" => "beta",
                "condition" => "equals",
                "rvalue" => 1,
            ),
        ));
        if(empty($servers[0]) ){
            throw new Exception("Cannot get beta server.");
        }
        $beta_server = $servers[0];

        $server_model = new ServerModel();
        $success = $server_model->replication(array(
            "source" => $dev_server,
            "target" => $beta_server,
            "files" => $files,
        ));

        $release_model = new ReleaseModel();
        if(!$success){
            $error_log = new ReleaseErrorLog();
            $error_log->write(array(
                "release_id" => $release_id,
                "message" => $server_model->error,
            ));

            $release_model->setBetaError($release_id);
        }
        else {
            $release_model->completeBetaReplication($release_id);
        }
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
