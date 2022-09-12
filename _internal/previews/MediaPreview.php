<?php

require_once "IFilePreview.php";

/**
 * Provides previews for media such as images and videos
 */
class MediaPreview implements IFilePreview
{
    public function renderPreview(string $path, Twig\Environment $twig): string
    {
        $video = pathinfo($path, PATHINFO_EXTENSION) == "mp4";

        return $twig->render("previews/media.html.twig", [ "path" => $path, "isVideo" => $video ]);
    }

    public static function self(): IFilePreview
    {
        if (MediaPreview::$preview === null)
        {
            $preview = new MediaPreview();
        }
        return $preview;
    }

    private static ?IFilePreview $preview = null;
}