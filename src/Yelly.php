<?php
namespace orq\php\yelly;
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/erusev/parsedown/Parsedown.php';

/**
 * Recursively read the files in the source directory and collect the file information
 */
class Yelly {
    /**
     * The source root to hold input files
     * @var string
     */
    private $inputDir;

    /**
     * The directory to put html files
     * @var string
     */
    private $outDir;

    /**
     * @var array
     */
    private $articles;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * read paths information from configuration
     * @return void
     */
    public function __construct() {
        $config = \parse_ini_file(__DIR__ . '/../configuration.ini');
        $this->inputDir = $this->prefixRoot($config['source_dir']);
        $this->outDir = $this->prefixRoot($config['out_dir']);
        $this->writer = new FluidWriter();
        $this->collectMeta();
    }

    /**
     * Prefix directory with project root
     * @param string $dir
     * @return string
     */
    private function prefixRoot($dir) {
        return __DIR__ . '/../' . $dir . '/';
    }

    /**
     * Travase the files
     * 
     * @return array 
     */
    protected function collectMeta() {
        $dirIterator = new \RecursiveDirectoryIterator($this->inputDir);
        $reIterator = new \RecursiveIteratorIterator($dirIterator);

        foreach ($reIterator as $v) {  
            if (!$v->isDir()) {
                $this->extractMeta($v->getPathname(), $v->getMTime());
            }
        }
    }

    /**
     * collect meta information
     * 
     * @param string $filePath The file to parse
     * @param integer $mTime The file modify time
     * @retur void
     */
    protected function extractMeta($filePath, $mTime) {        
        $meta['mTime'] = $this->processTime($mTime);
        $meta['inputFilePath'] = $filePath;

        $sawDivider = FALSE;
        $finishHeader = FALSE;
        // extract Information
        $lineCount = 0;
        $fh = fopen($filePath, 'r');
        while ($line = fgets($fh, 4096)) {
            if ($sawDivider && $finishHeader) break;
            $lineCount++;
            // determine whether the line is divider('---')
            if (preg_match('/^-+(\r|\n)*$/', $line)) {
                if ($sawDivider) {
                    $finishHeader = TRUE;
                }
                $sawDivider = TRUE;
                continue;
            } 

            // Process the meta
            if ($sawDivider && !$finishHeader) {
                $entry = explode(':', $line);
                $label = trim(preg_replace('/[:：].*$/', '', $entry[0]));
                $value = trim(preg_replace('/[\n\r]/', '', $entry[1]));
                if (strtolower($label) == 'tags') {
                    $value = explode(',', $value);
                }
                
                $meta[$label] = $value;
            }
        }

        $meta['bodyLineNumber'] = $lineCount;
        $meta['tags'] = $this->processTags($meta['tags']);
        $this->addToAritlcesMeta($meta);
    }

    /**
     * Process tags
     * @param array $tags
     * @return array
     */
    private function processTags($tags) {
        $nTags = [];
        foreach ($tags as $tag) {
            $item['label'] = $tag;
            $item['value'] = \preg_replace('/ /', '-', trim($tag));
            array_push($nTags, $item);
        }

        return $nTags;
    }

    /**
     * @param $stamp 
     * @return string
     */
    private function processTime($stamp) {
        $date = new \DateTime();
        $date->setTimestamp($stamp);

        return $date->format('Y-m-d H:i:s');
    }


    /**
     * @param array $meta
     * @return void
     */
    protected function addToAritlcesMeta(array $meta) {
        $meta['linkFile'] = str_replace(' ', '_', $meta['title']) . '.html';
        $meta['linkLabel'] = $meta['title'];
        foreach ($meta['tags'] as $tag) {
            $tagValue = $tag['value'];
            if (!isset($this->articlesMeta[$tagValue])) {
                $this->articlesMeta[$tagValue] = [];
            }
            array_push($this->articlesMeta[$tagValue], $meta);
        }
    }

    /**
     * Make the site
     */
    public function makeSite() {
        $this->makeCollectionPages();
        $this->makeArticlePages();
    }

    /**
     * Make collection pages
     */
    public function makeCollectionPages() {
        foreach ($this->articlesMeta as $tagValue => $articles) {   
            $this->writeCollectionPage($tagValue, $articles);
        }

        // make index page
        $articles = $this->getAllPagesInfo();
        $this->writeCollectionPage('index', $articles);
    }
   
    /**
     * Write collection file
     */
    protected function writeCollectionPage($filename, array $articles) { 
        usort($articles, function($a, $b) {
            $aMtime = new \DateTime($a['mTime']);
            $bMtime = new \DateTime($b['mTime']);
            if ($bMtime < $aMtime) return -1;
            else if ($bMtime == $aMtime) return 0;
            else return 1;
        });
        $content = $this->writer->assemble(['articles' => $articles], 'Collection');
            
        $outPath = $this->outDir . $filename . '.html';
        $fh = fopen($outPath, 'w+');
        fwrite($fh, $content);
    }
    
    /**
     * Make all article pages
     */
    public function makeArticlePages() {
        $articles = $this->getAllPagesInfo();
        foreach ($articles as $article) {
            $lineCount = 0;
            $fh = fopen($article['inputFilePath'], 'r');
            $body = '';
            while ($line = fgets($fh, 4096)) {
                $lineCount++;
                if ($lineCount > $article['bodyLineNumber']) {
                    $body .= $line;
                }
            }
            $this->writeArticlePage($article, $body);
        }
    }

    /**
     * Get all pages information
     * @return array
    */
    protected function getAllPagesInfo() {
        $articles = [];
        foreach ($this->articlesMeta as $tagArticles) {
            foreach ($tagArticles as $article) {
                $articles[$article['linkFile']] = $article;
            }
        }

        return $articles;
    }
    
    /**
     * @param array $meta
     * @param string $body
     * @return string
     */
    protected function writeArticlePage(array $meta, $body) {
        $vars = $meta;
        $parseDown = new \Parsedown();
        $body = $parseDown->text($body);
        $vars['body'] = $body;
        $content = $this->writer->assemble($vars, 'Blog');

        $name = str_replace(' ', '_', $meta['title']) . '.html';
        $outPath = $this->outDir . $name;
        $fh = fopen($outPath, 'w+');
        fwrite($fh, $content);
    }

}

$reader = new Yelly();
$reader->makeSite();