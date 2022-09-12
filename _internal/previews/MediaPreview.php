<?php

require_once "FilePreviewInterface.php";

/**
 * Provides previews for media such as images and videos
 */
class MediaPreview implements FilePreviewInterface
{
    public function renderPreview(string $path, Twig\Environment $twig): string
    {
        $video = pathinfo($path, PATHINFO_EXTENSION) == "mp4";

        return $twig->render("previews/media.html.twig", [ "path" => $path, "isVideo" => $video ]);
    }

    public static function self(): FilePreviewInterface
    {
        if (MediaPreview::$preview === null)
        {
            $preview = new MediaPreview();
        }
        return $preview;
    }

    private static ?FilePreviewInterface $preview = null;
}