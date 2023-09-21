<?php

namespace xorc;

class util_http {

    public static function redirect($to) {
        header("Location: $to");
        // für die testbarkeit
        if (\PHP_SAPI != 'cli') exit;
    }

    // TODO: auto_off()
    public static function send_file($file, $type = null, $options = null) {

        if (!$type) $type = "application/octet-stream";
        # TODO: type=auto

        if (!$options) $options = array();

        # default headers, wenn download dialog erzwungen werden soll
        if ($type == 'download') {
            $name = $options['name'];
            if (!$name) $name = basename($file);

            # IE spezialfeature
            # http://support.microsoft.com/kb/231296
            # http://support.microsoft.com/kb/323308
            # http://stackoverflow.com/questions/1038707/cant-display-pdf-from-https-in-ie-8-on-64-bit-vista
            if (client_is_ie()) {
                header("Pragma: ");
                header("Cache-Control: ");
                # /IE
            }
            header("Content-Type: application/octet-stream");
            header('Content-Length: ' . sprintf('%u', filesize($file)));
            header('Content-Disposition: attachment; filename="' . $name . '"');

            unset($options['name']);
        } else {
            header("Content-Type: $type");
        }

        foreach ($options as $k => $v) {
            header($k . ": " . $v);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $chunksize = 1 * (1024 * 1024); // how many bytes per chunk
        if (filesize($file) > $chunksize) {
            $handle = fopen($file, 'rb');
            $buffer = '';
            while (!feof($handle)) {
                $buffer = fread($handle, $chunksize);
                echo $buffer;
                ob_flush();
                flush();
            }
            fclose($handle);
        } else {
            readfile($file);
        }
    }
}
