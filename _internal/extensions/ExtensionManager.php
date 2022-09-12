<?php

require_once "Extension.php";
require_once "_internal/previews/CodePreview.php";

class ExtensionManager
{
    public function __construct() {
        $this->extensions =
        [
            'txt' => new Extension(ExtensionIconType::Solid, 'fa-file-lines', CodePreview::self()),
            'json' => new Extension(ExtensionIconType::Solid, 'fa-file', CodePreview::self())
        ];

        $this->default = new Extension(ExtensionIconType::Solid, 'fa-file', null);
    }

    private array $extensions;
    private Extension $default;

    public function getExtension(string $extension): Extension
    {
        if (array_key_exists($extension, $this->extensions))
        {
            return $this->extensions[$extension];
        }
        return $this->default;
    }
}