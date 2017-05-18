<?php

namespace M12_Engine\Models\Servers;

class Item {

    public $error = null;

    public function makeBackup($server){
        $address = "{$server["ssh_login"]}@{$server["ip_address"]}";
        $remote_command = "tar -cjf {$server["backup_file"]} {$server["project_directory"]}";
        $command = "ssh {$address} '{$remote_command}'";
            \system($command, $return_var);
            if($return_var != 0){
                $this->error = "Backup error: {$command}";
                return false;
            }
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
            $command = "scp{$scp_options} {$scp_source} {$scp_target}"
            \system($command, $return_var);
            if($return_var != 0){
                $this->error = "Replication error: {$command}";
                return false;
            }
        }

        return true;
    }
}
