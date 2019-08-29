<?php
use Furry\Pawesome\Builder;
require (dirname(__DIR__)) . '/autoload.php';

(new Builder())
    ->loadPreambleFile()
    ->build();
