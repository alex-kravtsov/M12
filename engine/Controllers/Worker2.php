<?php

namespace M12_Engine\Controllers;

use M12_Engine\Core\Factory;
use M12_Engine\Models\Servers\Peer as ServerPeer;
use M12_Engine\Models\Servers\Items as ServerModel;

class Worker2 {

    public $server_id = null;

    public function runJob(){
        $release_peer = new ReleasePeer();
        $releases = $release_peer->get(array(
            "filterby" => array(
                array(
                    "column" => "production_update",
                    "condition" => "equals",
                    "rvalue" => 1,
                ),
                array(
                    "column" => "production_error",
                    "condition" => "equals",
                    "rvalue" => 0,
                ),
                array(
                    "column" => "production_completed_at",
                    "condition" => "is null",
                ),
            ),
        ));

        if(empty($releases[0]["id"]) ){
            throw new \Exception("Release is not found.")
        }
        $release_id = $releases[0]["id"];

        $file_peer = new FilePeer();
        $files = $file_peer->get(array(
            "filterby" => array(
                array(
                    "table" => "t_map",
                    "column" => "release_id",
                    "condition" => "equals",
                    "rvalue" => $release_id,
                ),
            ),
        ));
        if(empty($files) ){
            throw new \Exception("Cannot get release files.");
        }
    }

    public function hasJob(){
        return $this->server_id ? true : false;
    }

    public function holdReplicatedServer(){
        $server_model = new ServerModel();
        $server_model->block($this->server_id);
    }

    public function pickReplicatedServer(){
        $server_peer = new ServerPeer();
        $servers = $server_peer->get(array(
            "filterby" => array(
                array(
                    "column" => "replication",
                    "condition" => "equals",
                    "rvalue" => 1,
                ),
                array(
                    "column" => "blocked",
                    "condition" => "equals",
                    "rvalue" => 0,
                ),
                array(
                    "column" => "error",
                    "condition" => "equals",
                    "rvalue" => 0,
                ),
            ),
        ));
        if(!empty($servers[0]["id"]) ){
            $this->server_id = $servers[0]["id"];
            return true;
        }

        return false;
    }

    public function getLock(){
        $db = Factory::getDatabase();
        $query = "LOCK TABLES `servers`";
        $db->execute($query);
    }

    public function releaseLock(){
        $db = Factory::getDatabase();
        $query = "UNLOCK TABLES";
        $db->execute($query);
    }
}