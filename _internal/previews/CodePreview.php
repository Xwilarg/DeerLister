<?php

require_once "IFilePreview.php";

/**
 * Provides previews for code or text
 */
class CodePreview implements IFilePreview
{
    public function renderPreview(string $path, Twig\Environment $twig): string
    {
        return $twig->render("previews/code.html.twig", [ "code" => file_get_contents($path) ]);
    }

    public static function self(): IFilePreview
    {
        if (CodePreview::$preview === null)
        {
            $preview = new CodePreview();
        }
        return $preview;
    }

    private static ?IFilePreview $preview = null;
}