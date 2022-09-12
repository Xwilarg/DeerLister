<?php

require_once "FilePreviewInterface.php";

/**
 * Provides previews for videos
 */
class VideoPreview implements FilePreviewInterface
{
    public function renderPreview(string $path, Twig\Environment $twig): string
    {
        return $twig->render("previews/video.html.twig", [ "path" => $path ]);
    }

    public static function self(): FilePreviewInterface
    {
        if (VideoPreview::$preview === null)
        {
            $preview = new VideoPreview();
        }
        return $preview;
    }

    private static ?FilePreviewInterface $preview = null;
}