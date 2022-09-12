<?php

require_once "Extension.php";
require_once "_internal/previews/CodePreview.php";

class ExtensionManager
{
    public function __construct() {
        $this->extensions = 
        [
            'txt' => new Extension(ExtensionIconType::Solid, 'fa-file-lines', null)//CodePreview::self())
        ];

        $this->$default = new Extension(ExtensionIconType::Solid, 'fa-file', null);
    }

    private array $extensions;
    private Extension $default;

    public function getExtension(string $extension): Extension
    {
        var_dump($this->$extensions);
        if (array_key_exists($extension, []))
        {
            return $this->$extensions[$extension];
        }
        return $this->$default;
    }
}