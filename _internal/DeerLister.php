<?php

require_once "extensions/ExtensionManager.php";
require_once "ParsedownExtension.php";

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFilter;

class DeerLister
{
    private Environment $twig;

    private array $config;
    private ExtensionManager $extManager;

    function __construct()
    {
        $this->$extManager = new ExtensionManager();
        $this->config = [];

        // setup twig
        $loader = new FilesystemLoader("_internal/templates");

        $this->twig = new Environment($loader);

        // load config
        if (in_array("yaml", get_loaded_extensions()) && file_exists("_internal/config.yaml"))
        {
            $this->config = yaml_parse(file_get_contents("_internal/config.yaml"));
        }

        // Convert a size in byte to something more diggest
        $this->twig->addFilter(new TwigFilter("humanFileSize", function($size) {
            $units = ["B", "KB", "MB", "GB"];
            for ($i = 0; $size > 1024; $i++) $size /= 1024;

            return round($size, 2) . $units[$i];
        }));

        // Get the file where the user with be led from the current element
        $this->twig->addFilter(new TwigFilter("getFilePath", function($file, $directory) {
            if ($file["name"] === "..")
            {
                $path = $this->getRelativePath($directory . "/..");

                // don't create an url with ?dir if we are returning to root
                return $path == "" ? "/" : "?dir=" . $path;
            }

            $path = $file["isFolder"] ? "?dir=" : "/";
            
            return $path . $directory . ($directory == '' || str_ends_with($directory, '/') ? '' : '/') . $file["name"];
        }));

        // Build the path until the current index
        $this->twig->addFilter(new TwigFilter("buildPath", function($pathArray, $index) : string
        {
            $finalPath = "?dir=";

            foreach ($pathArray as $i => $value)
            {
                $finalPath .= $value . '/';
                if ($i === $index)
                {
                    break;
                }
            }

            return $finalPath;
        }));
    }

    private function filesCmp(array $a, array $b): int
    {
        if ($a["isFolder"] && $b["isFolder"])
        {
            return strcmp(strtoupper($a["name"]), strtoupper($b["name"]));
        }

        if ($b["isFolder"])
        {
            return 1;
        }

        return strcmp(strtoupper($a["name"]), strtoupper($b["name"]));
    }

    private function readDirectory(string $directory, mixed $config): array|false
    {
        $base = getcwd();
        $path = realpath($base . "/" . $directory);

        // make sure we are not accessing a folder outside the script root
        if ($path === false || strpos($path, $base) !== 0)
        {
            return false;
        }

        if (!is_dir($path))
        {
            return false;
        }

        if ($this->isHidden($directory, $config, true))
        {
            return false;
        }

        $relPath = $this->getRelativePath($directory);
        $files = [];

        foreach(scandir($path) as $name)
        {
            // exclude current directory and index.php or parent for the root directory
            if ($name === '.' || (($name === "index.php" || $name === "..") && $path === $base))
            {
                continue;
            }

            // check if file is hidden
            if ($this->isHidden($name, $config, false))
            {
                continue;
            }

            $file = realpath($path . "/" . $name);
            $modified = date("Y-m-d H:i", filemtime($file));

            $isFolder = is_dir($file);

            $extension = $isFolder // TODO
                ? new Extension(ExtensionIconType::Solid, 'fa-folder', null)
                : $this->$extManager->getExtension(pathinfo($file, PATHINFO_EXTENSION));
            array_push($files,
                [
                    "name" => $name,
                    "isFolder" => $isFolder,
                    "icon" => $extension->getIcon(),
                    "lastModified" => $modified,
                    "size" => filesize($file),

                    "filePreview" => !$isFolder && $extension->isFilePreviewable() ? $this->pathCombine($relPath, $name) : null
                ]
            );
        }

        usort($files, array($this, "filesCmp"));
        return $files;
    }

    /**
     * Returns if a file/folder should be displayed or not
     *
     * @param string $path Path to the file/folder
     * @param mixed $config config.yaml file
     * @param bool $ignoreHide Do we only consider forbidden files (true) or also hidden ones (false)
    */
    private function isHidden(string $path, mixed $config, bool $ignoreHide): bool
    {
        if (array_key_exists("forbidden", $config) && $config["forbidden"] !== NULL)
        {
            $hidden = $config["forbidden"];
        }
        else
        {
            $hidden = ["_internal", "vendor"];
        }
        if (!$ignoreHide && array_key_exists("hidden", $config) && $config["hidden"] !== NULL)
        {
            $hidden = [...$hidden, ...$config["hidden"]];
        }

        foreach ($hidden as $search)
        {
            if (strpos($path, $search) !== false)
            {
                return true;
            }
        }

        return false;
    }

    private function getRelativePath(string $directory): string
    {
        $base = getcwd();
        $path = realpath($base . "/" . $directory);
        
        return strtr(substr($path, strlen($base) + 1), DIRECTORY_SEPARATOR, "/");
    }

    /**
     * Combines multiple values to a path. Currently very simple, does not fix paths or check for trailing path seperator
     * 
     * @param string $paths Parameters of paths
     * 
     * @return string The combined path
     */
    private function pathCombine(string ...$paths): string
    {
        return implode("/", array_diff($paths, [""]));
    }

    public function render(string $directory): string
    {
        // read the directory
        if (($files = $this->readDirectory($directory, $this->config)) === false)
        {
            http_response_code(404);

            return $this->twig->render("404.html.twig", ["title" => "Not found"]);
        }

        // relative real path
        $path = $this->getRelativePath($directory);

        $title = $path == "" ? "Home" : basename($directory);
        $readme = null;
        foreach ($files as $f)
        {
            if (strtoupper($f["name"]) === 'README.MD')
            {
                $content = file_get_contents(($directory == "" ? "" : $directory . "/") . $f["name"]);

                $parsedown = new ParsedownExtension();
                $parsedown->setSafeMode(true);
                $readme = $parsedown->text($content);
                if ($parsedown->getTitle() !== null)
                {
                    $title = $parsedown->getTitle();
                }
                break;
            }
        }
        
        return $this->twig->render("index.html.twig",
            [
                "files" => $files,
                "title" => $title,
                "path" => [ "full" => $path, "exploded" => array_filter(explode("/", $path)) ],
                "readme" => $readme
            ]
        );
    }

    public function getFilePreview(string $file): string
    {
        // make sure we are not accessing files outside web root
        // path passed to any file preview should already be safe
        $base = getcwd();
        $path = realpath($base . "/" . $file);

        if ($path === false || strpos($path, $base) !== 0)
        {
            http_response_code(404);

            return "File could not be previewed";
        }

        // TODO check config forbidden

        $filename = pathinfo($file, PATHINFO_BASENAME);
        $ext = $this->$extManager.getExtension(pathinfo($file, PATHINFO_EXTENSION));

        if ($ext->isFilePreviewable())
        {
            return $ext->renderPreview($file, $this->twig);
        }

        http_response_code(404);
        return "File could not be previewed";
    }
}
