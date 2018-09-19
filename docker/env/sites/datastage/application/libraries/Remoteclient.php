<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Remoteclient {

    public function getfile($host,$output_filename) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $result = curl_exec($ch);

        curl_close($ch);

        // the following lines write the contents to a file in the same directory (provided permissions etc)
        $fp = fopen($output_filename, 'w');
        fwrite($fp, $result);
        fclose($fp);

        return 1;
    }
}


?>