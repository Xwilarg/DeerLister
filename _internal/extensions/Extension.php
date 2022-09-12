<?php

require_once "ExtensionIconType.php";
require_once "_internal/previews/IFilePreview.php";

class Extension
{
    public function __construct(ExtensionIconType $extensionType, string $extensionName, ?IFilePreview $previewMode) {
        $this->extensionType = $extensionType;
        $this->extensionName = $extensionName;
        $this->$previewMode = $previewMode;
    }

    public function getIcon()
    {
        $category = "";
        switch ($this->$extensionType)
        {
            case ExtensionIconType::Solid: $category = "solids";
            case ExtensionIconType::Brand: $category = "brands";
            default: $category = "meh";
//            default: throw new Exception('Invalid extension type ' . $this->$extensionType);
        }

        return $category . " " . $this->$extensionName;
    }

    public function isFilePreviewable(): bool
    {
        return $this->$previewMode !== null;
    }

    public function renderPreview(string $path, Twig\Environment $twig): string
    {
        return $this->previewMode->renderPreview($path, $twig);
    }

    private ExtensionIconType $extensionType;
    private string $extensionName;
    private ?FilePreview $previewMode;
}