<?php

require_once "FilePreviewInterface.php";

/**
 * Provides previews for images
 */
class ImagePreview implements FilePreviewInterface
{
    public function renderPreview(string $path, Twig\Environment $twig): string
    {
        return $twig->render("previews/media.html.twig", [ "path" => $path ]);
    }

    public static function self(): FilePreviewInterface
    {
        if (ImagePreview::$preview === null)
        {
            $preview = new ImagePreview();
        }
        return $preview;
    }

    private static ?FilePreviewInterface $preview = null;
}