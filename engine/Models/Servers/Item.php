<?php

namespace M12_Engine\Models\Servers;

use M12_Engine\Core\Factory;

class Item {

    public $error = null;

    public function completeReplication($server_id){
        $db = Factory::getDatabase();
        $query = "UPDATE `servers` SET";
        $query .= " `replication` = 0,";
        $query .= " `blocked` = 0,";
        $query .= " `updated_at` = NOW()";
        $query .= " WHERE `id` = " . $server_id;
        $db->update($query);
    }

    public function setReplicationError($server_id){
        $db = Factory::getDatabase();
        $query = "UPDATE `servers` SET";
        $query .= " `replication` = 0,";
        $query .= " `blocked` = 0,";
        $query .= " `error` = 1";
        $query .= " WHERE `id` = " . $server_id;
        $db->update($query);
    }

    public function block($server_id){
        $db = Factory::getDatabase();
        $query = "UPDATE `servers` SET `blocked` = 1 WHERE `id` = " . $server_id;
        $db->update($query);
    }

    public function makeBackup($server){
        $address = "{$server["ssh_login"]}@{$server["ip_address"]}";
        $remote_command = "tar -cjf {$server["backup_file"]} {$server["project_directory"]}";
        $ssh_options = "-o IdentityFile=/root/.ssh/updater_rsa";
        $command = "ssh {$ssh_options} {$address} '{$remote_command}'";
        \system($command, $return_var);
        if($return_var != 0){
            $this->error = "Backup error: {$command}";
            return false;
        }

        return true;
    }

    public function replication($options){
        $source = !empty($options["source"]) ? $options["source"] : null;
        $target = !empty($options["target"]) ? $options["target"] : null;
        $files = !empty($options["files"]) ? $options["files"] : array();

        foreach($files as $file){
            $source_address = "{$source["ssh_login"]}@{$source["ip_address"]}";
            $source_path = "{$source["project_directory"]}/{$file["path"]}";
            $scp_source = "{$source_address}:'{$source_path}'";

            $target_address = "{$target["ssh_login"]}@{$target["ip_address"]}";
            $target_path = "{$target["project_directory"]}/{$file["path"]}";
            $scp_target = "{$target_address}:'{$target_path}'";

            $scp_options = $file["type"] == "directory" ? " -r" : "";
            $ssh_options = "-o IdentityFile=/root/.ssh/updater_rsa";
            $command = "scp {$ssh_options} {$scp_options} {$scp_source} {$scp_target}";
            print $command . "\n";
            exit;
            \system($command, $return_var);
            if($return_var != 0){
                $this->error = "Replication error: {$command}";
                return false;
            }
        }

        return true;
    }
}
