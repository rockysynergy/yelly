<?php
namespace orq\php\yelly;
require_once __DIR__ . '/../vendor/autoload.php';

class Converter {


    function convert($filePath) {
        $fh = fopen($filePath, 'r');
        $headers = [];
        $body = '';

        $sawDivider = FALSE;
        $finishHeader = FALSE;
        while ($line = fgets($fh)) {
            if (preg_match('/^-+(\r|\n)*$/', $line)) {
                if ($sawDivider) {
                    $finishHeader = TRUE;
                }
                $sawDivider = TRUE;
                continue;
            } 

            // Process the header
            if ($sawDivider && !$finishHeader) {
                $entry = explode(':', $line);
                array_push($headers, $entry);
            }

            // Process body
            if ($finishHeader) {
                $body .= $line;
            }
        }

        var_dump($headers);

        $parseDown = new Parsedown();
        echo $parseDown->text($body);
    }

    function traverse($dir) {
        $dirIterator = new RecursiveDirectoryIterator($dir);
        $reIterator = new RecursiveIteratorIterator($dirIterator);

        foreach ($reIterator as $v) {
            echo '===============' . "\n";
            echo $reIterator->getDepth();
            
            if ($v->isDir()) {
                var_dump($v->getPath());
            } else {
                convert($v->getPathname());
            }
            $mTime = new DateTime();
            $mTime->setTimestamp($v->getMTime());
            var_dump($mTime->format('Y-m-d H:i:s'));
        }
    }

}
// convert('./sampleBlog.md');
traverse(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'input');