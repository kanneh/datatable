<?php
require_once __DIR__ . '/../../src/Editor.php';
require_once __DIR__ . '/config.php';

use Kanneh\Datatable\Editor,
    Kanneh\Datatable\Field;

$mdUsers = Editor::inst(array(
	"db" => $DRIVER,
    "username" => $DBUSER,
    "password" => $DBPASSWORD,
),"users","id")
->fields(
    Field::inst('id','id')
        ->dbAttr('INT PRIMARY KEY AUTO_INCREMENT'),
    Field::inst('fullname','fullname')
        ->dbAttr('VARCHAR(100)'),
    Field::inst('email','email')
        ->dbAttr('VARCHAR(100)'),
    Field::inst('phone','phone')
        ->dbAttr('VARCHAR(25)'),
    Field::inst('address','address')
        ->dbAttr('VARCHAR(255)'),
    Field::inst('created_at','created_at')
        ->dbAttr('DATETIME DEFAULT CURRENT_TIMESTAMP'),
    Field::inst('updated_at','updated_at')
        ->dbAttr('DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
    Field::inst('password','password')
        ->dbAttr('VARCHAR(255)')
);

