<?php
/*
 Copyright (C) 2014-2015, Siemens AG
 Author: Daniele Fognini

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 version 2 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */


namespace Fossology\Reportgen;

require_once("$MODDIR/lib/php/common-cli.php");
cli_Init();

$clearedGetter = new \Fossology\Lib\Report\LicenseIrrelevantGetter();
$clearedGetter->getCliArgs();
$uploadId = $clearedGetter->getUploadId();
$groupId = $clearedGetter->getGroupId();
print json_encode($clearedGetter->getCleared($uploadId, $groupId));
/*
print json_encode(array('paths'=>array(

array("content"=>'path/one/',"files"=>array("one.txt")),
    array("content"=>'path/two/',"files"=>array("two.txt","two.php","two.cpp"))
        
        ) )  );
 * 
 */