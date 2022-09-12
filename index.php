<?php

require_once "vendor/autoload.php";
require_once "_internal/DeerLister.php";

spl_autoload_register(function($class) {
    include "_internal/previews/" . $class . ".php";
});

$lister = new DeerLister();

if (isset($_GET["preview"]))
{
    echo $lister->getFilePreview($_GET["preview"]);

    exit;
}

echo $lister->render($_GET["dir"] ?? "");
