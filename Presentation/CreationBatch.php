<?php
include_once("../Model/Database.php");
$db = (Database::getInstance()) ;

function  PasswordGenerator(String$longueur ) : String {
    $Chaine  = "vbhjdfkbjcjffd65hGFHGFKFSGOGFNBPWMCPOCOPHUEZYRNVV5gfg5n9h6fxf9cb6v5ytg5h4vbhy789g6fcv7cgxfd6fgh9c6vhn48j5hg4j8965c4f5gf48,5jk54ml5o4ùôpiu435jtdhwgs4f5g6nv4jy6s45dgfh49786tre46h";
    $Chaine->str_shuffle($Chaine);
    $Chaine = substr($Chaine,0,10);
    return $Chaine;
};

function AddUserCSV():void{
    fgetcsv();
};