<?php

$weaver = file_get_contents('weaver-paracord.xslt');
$paracord = file_get_contents('engraving-paracord.xslt');
$laserEngraving = file_get_contents('engraving-export-laser.xslt');
$recessedEngraving = file_get_contents('engraving-export-recessed.xslt');
$tape = file_get_contents('name-tape.xslt');

$output = <<<COMBINE
<?xml version="1.0" encoding="utf-8"?>
<?mso-application progid="Excel.Sheet"?>
<files>
    <file filename="%Y%-%m%-%d% Weaver Paracord Engraving Order Engravings.xml">
        {$weaver}
    </file>
    <file filename="%Y%-%m%-%d% Paracord Engraving Order Engravings.xml">
        {$paracord}
    </file>
    <file filename="%Y%-%m%-%d% Engraving Order - Recessed.xml">
        {$recessedEngraving}
    </file>
    <file filename="%Y%-%m%-%d% Engraving Order - Laser.xml">
        {$laserEngraving}
    </file>
    <file filename="%Y%-%m%-%d% Name Tape Order.xml">
        {$tape}
    </file>
</files>
COMBINE;

file_put_contents('xtento.xslt', $output);
