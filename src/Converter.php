<?php
namespace orq\php\yelly;
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/erusev/parsedown/Parsedown.php';


class Converter {
    /**
     * @var array
     */
    protected $articles;

    /**
     * @return void
     */
    public function makeCollections() {
        foreach ($this->articles as $tag => $files) {
            $outPath = __DIR__ . '/../output/' . strtolower($tag) . '.html';
            $content = $this->toHtml(['files' => $files], false);

            $fh = fopen($outPath, 'w+');
            fwrite($fh, $content);
        }
    }

    /**
     * Traverse the directories recursively
     * 
     * @param string $dir The root of the articles
     * @return void 
     */
    public function traverse($dir) {
        $dirIterator = new \RecursiveDirectoryIterator($dir);
        $reIterator = new \RecursiveIteratorIterator($dirIterator);

        foreach ($reIterator as $v) {
                        
            if ($v->isDir()) {
                var_dump($v->getPath());
            } else {
                $this->makeSingle($v->getPathname(), $v->getMTime());
            }
            // $mTime = new DateTime();
            // $mTime->setTimestamp($v->getMTime());
            // var_dump($mTime->format('Y-m-d H:i:s'));
        }
    }

    /**
     * extract information from article to make it ready to be converted into HTML file
     * 
     * @param string $filePath The file to parse
     * @param integer $mTime The file modify time
     * @retur void
     */
    public function makeSingle($filePath, $mTime) {
        $fh = fopen($filePath, 'r');
        $headers = [];
        $body = '';

        $sawDivider = FALSE;
        $finishHeader = FALSE;

        // extract Information
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
                $label = preg_replace('/[:：].*$/', '', $entry[0]);
                $value = trim(preg_replace('/[\n\r]/', '', $entry[1]));

                if (strtolower($label) == 'tags') {
                    $tags = explode(',', $value);
                    $nValue = [];
                    foreach ($tags as $tag) {
                        $tag = preg_replace('/ /', '_', trim($tag));
                        $nTag['label'] = $tag;
                        $nTag['value'] = strtolower($tag);
                        array_push($nValue, $nTag);
                    }
                    $value = $nValue;
                }
                $headers[$label] = $value;
            }

            // Process body
            if ($finishHeader) {
                $body .= $line;
            }
        }

        // Parse body content to html string
        $parseDown = new \Parsedown();
        $body = $parseDown->text($body);

        // write to html file
        $name = str_replace(' ', '_', $headers['title']) . '.html';
        $outPath = __DIR__ . '/../output/' . $name;
        $fh = fopen($outPath, 'w+');
        if (!isset($headers['tags'])) {
            $headers['tags'] = [];
        }
        array_push($headers['tags'], ['label' => 'index', 'value' => 'index']);
        $content = $this->toHtml(['title' => $headers['title'], 'tags' => $headers['tags'], 'body' => $body]);
        fwrite($fh, $content);

        // Add article to collectios
        
        $processName = function($name) {
            return preg_replace('/.html/i', '', $name);
        };

        $makeTime = function($stamp) {
            $date = new \DateTime();
            $date->setTimestamp($stamp);

            return $date->format('Y-m-d H:i:s');
        };

        foreach ($headers['tags'] as $tag) {
            $tag = $tag['value'];
            if (!isset($this->articles[$tag])) {
                $this->articles[$tag] = [];
            }
            array_push($this->articles[$tag], ['html' => $processName($name), 'mTime' => $makeTime($mTime), 'tags' => $headers['tags']]);
        }
    }

    /**
     * Convert to HTML files
     * @param array $vars Variables to be assigned for rendering
     * @param boolean $isArticle
     * @return string
     */
    protected function toHtml($vars, $isArticle = true) {
        $FLUID_CACHE_DIRECTORY = !isset($FLUID_CACHE_DIRECTORY) ? __DIR__ . '/../cache/' : $FLUID_CACHE_DIRECTORY;
        $view = new \TYPO3Fluid\Fluid\View\TemplateView();
        $paths = $view->getTemplatePaths();
        $paths->setTemplateRootPaths([
            __DIR__ . '/../Resources/Private/Templates/'
        ]);
        $paths->setLayoutRootPaths([
            __DIR__ . '/../Resources/Private/Layouts/'
        ]);
        $paths->setPartialRootPaths([
            __DIR__ . '/../Resources/Private/Partials/'
        ]);
        
        if ($FLUID_CACHE_DIRECTORY) {
            // Configure View's caching to use ./examples/cache/ as caching directory.
            $view->setCache(new \TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache($FLUID_CACHE_DIRECTORY));
        }

        if ($isArticle) {
            $templatePath = __DIR__ . '/../Resources/Private/Templates/Blog.html';
        } else {
            $templatePath = __DIR__ . '/../Resources/Private/Templates/Collection.html';
        }
        $paths->setTemplatePathAndFilename($templatePath);
        $view->assignMultiple($vars);
        $output = $view->render();
        // echo $output;       
        return $output;
    }

}
// convert('./sampleBlog.md');


$converter = new Converter();
// $converter->toHtml(['title' => 'The blog Title', 'body' => 'Hey the blog body content is here!']);
$converter->traverse(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'input');
$converter->makeCollections();