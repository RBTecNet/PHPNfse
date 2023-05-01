<?php

use Rbtecnet\Phpnfse\Provedores\NotaCarioca\Operations\Download\DownloadDanfeNfse;

require "vendor/autoload.php";
$df = new DownloadDanfeNfse();
$retorno = $df->DownloadDanfeNfse('homologacao','2615789','23494','R2K1-MEGY');
echo "<a href=$retorno targer='_blank'>link nfse</a>";