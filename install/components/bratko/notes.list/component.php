<?php
/**
 * Компонент bratko:notes.list.
 *
 * @author   Артём Братко
 * @link     https://arbratko.ru/
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!Loader::includeModule("bratko.notes")) {
    ShowError(Loc::getMessage("BRATKO_NOTES_MODULE_NOT_INSTALLED"));
    return;
}

$this->includeComponentTemplate();