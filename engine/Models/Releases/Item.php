<?php

namespace M12_Engine\Models\Releases;

use M21_Engine\Core\Factory;

class Item {

    public function beginProductionReplication($release_id){
        $db = Factory::getDatabase();
        $query = "UPDATE `releases` SET";
        $query .= " `production_replication` = 1,";
        $query .= " `production_started_at` = NOW()";
        $query .= " WHERE `id` = {$release_id}";
        $db->update($query);
    }

    public function beginBetaReplication($release_id){
        $db = Factory::getDatabase();
        $query = "UPDATE `releases` SET";
        $query .= " `beta_replication` = 1,";
        $query .= " `beta_started_at` = NOW()";
        $query .= " WHERE `id` = {$release_id}";
        $db->update($query);
    }

    public function create($options){
        $label = !empty($options["label"]) ? $options["label"] : null;
        $files = !empty($options["files"]) ? $options["files"] : array();

        $db = Factory::getDatabase();

        $query = "INSERT INTO `releases`";
        $query .= $label ? " SET `label` = '" . $db->escape($label) . "'" : "";
        $release_id = $db->insert($query);

        $values = "";
        foreach($files as $file_id){
            $values .= !empty($values) ? ", " : "";
            $values .= "({$release_id}, {$file_id})"
        }
        $query = "INSERT INTO `file_release_map` (`release_id`, `file_id`) VALUES {$values}";
        $db->insert($query);

        return $release_id;
    }
}