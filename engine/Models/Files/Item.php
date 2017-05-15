<?php

namespace M12_Engine\Models\Files;

class Item {
    
    public function update($file){
        $sample_command = "ssh user@example.com 'if [ -d ~/test.txt ] ; then echo 'directory' ; elif [ -f ~/test.txt ] ; then echo 'file' ; fi'";
        return $file_id;
    }
}
