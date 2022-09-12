<?php

require_once "FilePreviewInterface.php";

/**
 * Provides previews for code or text
 */
class CodePreview implements FilePreviewInterface
{
    public function renderPreview(string $path, Twig\Environment $twig): string
    {
        return $twig->render("previews/code.html.twig", [ "code" => file_get_contents($path) ]);
    }

    public static function self(): FilePreviewInterface
    {
        if (CodePreview::$preview === null)
        {
            $preview = new CodePreview();
        }
        return $preview;
    }

    private static ?FilePreviewInterface $preview = null;
}