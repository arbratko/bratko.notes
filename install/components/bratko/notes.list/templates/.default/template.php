<?php
/**
 * Шаблон компонента bratko:notes.list.
 *
 * @author   Артём Братко
 * @link     https://arbratko.ru/
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION;

$isAuthorized = isset($USER) && $USER->IsAuthorized();
$userName = "";
$logoutUrl = "";
if ($isAuthorized && $USER) {
    $userName = trim((string)$USER->GetFullName());
    if ($userName === "") {
        $userName = $USER->GetLogin();
    }
    $logoutUrl = $APPLICATION->GetCurPageParam("logout=yes", ["logout"]);
}

$messages = [
    "NOTES_LIST_AUTH_TITLE" => Loc::getMessage("NOTES_LIST_AUTH_TITLE"),
    "NOTES_LIST_AUTH_SUBTITLE" => Loc::getMessage("NOTES_LIST_AUTH_SUBTITLE"),
    "NOTES_LIST_AUTH_HINT" => Loc::getMessage("NOTES_LIST_AUTH_HINT"),
    "NOTES_LIST_AUTH_REGISTER_HINT" => Loc::getMessage("NOTES_LIST_AUTH_REGISTER_HINT"),
    "NOTES_LIST_AUTH_REGISTER_BTN" => Loc::getMessage("NOTES_LIST_AUTH_REGISTER_BTN"),
    "NOTES_LIST_MODAL_REGISTER_TITLE" => Loc::getMessage("NOTES_LIST_MODAL_REGISTER_TITLE"),
    "NOTES_LIST_MODAL_CLOSE_LABEL" => Loc::getMessage("NOTES_LIST_MODAL_CLOSE_LABEL"),
    "NOTES_LIST_HEADER_TITLE" => Loc::getMessage("NOTES_LIST_HEADER_TITLE"),
    "NOTES_LIST_HEADER_SUBTITLE" => Loc::getMessage("NOTES_LIST_HEADER_SUBTITLE"),
    "NOTES_LIST_HEADER_LOGOUT" => Loc::getMessage("NOTES_LIST_HEADER_LOGOUT"),
    "NOTES_LIST_PLACEHOLDER_TITLE" => Loc::getMessage("NOTES_LIST_PLACEHOLDER_TITLE"),
    "NOTES_LIST_PLACEHOLDER_BODY" => Loc::getMessage("NOTES_LIST_PLACEHOLDER_BODY"),
    "NOTES_LIST_ADD_NOTE_LABEL" => Loc::getMessage("NOTES_LIST_ADD_NOTE_LABEL"),
    "NOTES_LIST_ADD_BTN" => Loc::getMessage("NOTES_LIST_ADD_BTN"),
    "NOTES_LIST_EMPTY_TEXT" => Loc::getMessage("NOTES_LIST_EMPTY_TEXT"),
    "NOTES_LIST_EMPTY_HINT" => Loc::getMessage("NOTES_LIST_EMPTY_HINT"),
    "NOTES_LIST_LIST_LABEL" => Loc::getMessage("NOTES_LIST_LIST_LABEL"),
    "NOTES_LIST_FOOTER_COUNT" => Loc::getMessage("NOTES_LIST_FOOTER_COUNT"),
    "NOTES_LIST_EDIT_MODAL_TITLE" => Loc::getMessage("NOTES_LIST_EDIT_MODAL_TITLE"),
    "NOTES_LIST_EDIT_PLACEHOLDER_BODY" => Loc::getMessage("NOTES_LIST_EDIT_PLACEHOLDER_BODY"),
    "NOTES_LIST_MODAL_CANCEL" => Loc::getMessage("NOTES_LIST_MODAL_CANCEL"),
    "NOTES_LIST_MODAL_SAVE" => Loc::getMessage("NOTES_LIST_MODAL_SAVE"),
    "NOTES_LIST_BTN_EDIT_LABEL" => Loc::getMessage("NOTES_LIST_BTN_EDIT_LABEL"),
    "NOTES_LIST_BTN_DELETE_LABEL" => Loc::getMessage("NOTES_LIST_BTN_DELETE_LABEL"),
    "NOTES_AUTH_PLACEHOLDER_LOGIN" => Loc::getMessage("NOTES_AUTH_PLACEHOLDER_LOGIN"),
    "NOTES_AUTH_PLACEHOLDER_PASSWORD" => Loc::getMessage("NOTES_AUTH_PLACEHOLDER_PASSWORD"),
    "NOTES_AUTH_BTN_SUBMIT" => Loc::getMessage("NOTES_AUTH_BTN_SUBMIT"),
    "NOTES_REGISTER_SUCCESS_TITLE" => Loc::getMessage("NOTES_REGISTER_SUCCESS_TITLE"),
    "NOTES_REGISTER_TIMER_LINE" => Loc::getMessage("NOTES_REGISTER_TIMER_LINE"),
    "NOTES_REGISTER_PLACEHOLDER_NAME" => Loc::getMessage("NOTES_REGISTER_PLACEHOLDER_NAME"),
    "NOTES_REGISTER_PLACEHOLDER_LOGIN" => Loc::getMessage("NOTES_REGISTER_PLACEHOLDER_LOGIN"),
    "NOTES_REGISTER_PLACEHOLDER_EMAIL" => Loc::getMessage("NOTES_REGISTER_PLACEHOLDER_EMAIL"),
    "NOTES_REGISTER_PLACEHOLDER_PASSWORD" => Loc::getMessage("NOTES_REGISTER_PLACEHOLDER_PASSWORD"),
    "NOTES_REGISTER_PLACEHOLDER_PASSWORD_CONFIRM" => Loc::getMessage("NOTES_REGISTER_PLACEHOLDER_PASSWORD_CONFIRM"),
    "NOTES_REGISTER_BTN_CANCEL" => Loc::getMessage("NOTES_REGISTER_BTN_CANCEL"),
    "NOTES_REGISTER_BTN_SUBMIT" => Loc::getMessage("NOTES_REGISTER_BTN_SUBMIT"),
];

Extension::load('main.core');

$asset = Asset::getInstance();
$asset->addString('<link rel="preconnect" href="https://fonts.googleapis.com">');
$asset->addString('<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>');
$asset->addString('<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">');
$asset->addCss('/bitrix/js/bratko.notes/notes-app.css');
$asset->addJs('https://unpkg.com/react@18/umd/react.production.min.js');
$asset->addJs('https://unpkg.com/react-dom@18/umd/react-dom.production.min.js');
$asset->addJs('/bitrix/js/bratko.notes/notes-react.js');
?>
<script>
window.BRATKO_NOTES_INIT = <?= \CUtil::PhpToJSObject([
    "isAuthorized" => $isAuthorized,
    "userName" => $userName,
    "logoutUrl" => $logoutUrl,
    "messages" => $messages,
]) ?>;
</script>
<div id="bratko-notes-app" class="bratko-notes-app"></div>
