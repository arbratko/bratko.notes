<?php
/**
 * Описание компонента bratko:notes.auth.
 *
 * @author   Артём Братко
 * @link     https://arbratko.ru/
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = [
    "NAME" => Loc::getMessage("NOTES_AUTH_NAME"),
    "DESCRIPTION" => Loc::getMessage("NOTES_AUTH_DESCRIPTION"),
    "PATH" => [
        "ID" => "bratko",
        "NAME" => "Bratko",
    ],
];
