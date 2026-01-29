<?php
/**
 * Модуль заметок (bratko.notes) — автозагрузка классов.
 *
 * @author   Артём Братко
 * @link     https://arbratko.ru/
 */

use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

Loader::registerAutoLoadClasses(
    "bratko.notes",
    [
        "Bratko\\Notes\\NotesTable" => "lib/notesTable.php",
        "Bratko\\Notes\\Controller\\Notes" => "lib/controller/notes.php",
        "Bratko\\Notes\\Controller\\Register" => "lib/controller/Register.php",
        "Bratko\\Notes\\Controller\\Auth" => "lib/controller/Auth.php",
    ]
);

