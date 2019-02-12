<?php
namespace orq\php\yelly;

class FluidWriter implements WriterInterface {
    
    /**
     * Write blog content into template
     * @param string $vars
     * @param string $template to use for assembling
     * @return string The whole HTML content
     */
    public function assemble($vars, $template) {
        $config = parse_ini_file(__DIR__ . '/../configuration.ini');  
        $FLUID_CACHE_DIRECTORY = !isset($FLUID_CACHE_DIRECTORY) ? __DIR__ . '/../' . $config['cache_path'] : $FLUID_CACHE_DIRECTORY;
        $view = new \TYPO3Fluid\Fluid\View\TemplateView();
        $paths = $view->getTemplatePaths();
        $paths->setTemplateRootPaths([
            __DIR__ . '/../' . $config['template_path']
        ]);
        $paths->setLayoutRootPaths([
            __DIR__ . '/../' . $config['layout_path']
        ]);
        $paths->setPartialRootPaths([
            __DIR__ . '/../' . $config['partials_path']
        ]);
        
        if ($FLUID_CACHE_DIRECTORY) {
            // Configure View's caching to use ./examples/cache/ as caching directory.
            $view->setCache(new \TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache($FLUID_CACHE_DIRECTORY));
        }
        $templatePath = __DIR__ . '/../' . $config['template_path'] . \ucfirst($template) . '.html';
        $paths->setTemplatePathAndFilename($templatePath);
        $view->assignMultiple($vars);
        $output = $view->render();
        // echo $output;       
        return $output;
    }
}