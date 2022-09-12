<?php

interface FilePreviewInterface
{
    /**
     * Render the file preview content
     * 
     * @param string $path The relative path to the file
     * @param Twig\Environment $twig Instance of twig which can be used to render the file preview
     * 
     * @return string The file preview content
     */
    public function renderPreview(string $path, Twig\Environment $twig): string;

    public static function self(): FilePreviewInterface;
}