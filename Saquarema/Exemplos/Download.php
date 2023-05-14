<?php

use Rbtecnet\Phpnfse\Provedores\Saquarema\Operations\Download\DownloadDanfeNfse;

require "vendor/autoload.php";
$df = new DownloadDanfeNfse();
$retorno = $df->DownloadDanfeNfse('202300000043819','8d43e5c9c','18890963000173', 'xml');
echo "<a href=$retorno targer='_blank'>link nfse de Saquarema</a>";