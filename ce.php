<?php

$aDate = new \DateTime('1998-12-31 12:31:10');
$bDate = new \DateTime('2001-12-19 13:23:10');

var_dump($aDate > $bDate);
var_dump($aDate < $bDate);
var_dump($aDate == $bDate);
