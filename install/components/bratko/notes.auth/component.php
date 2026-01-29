<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION;

if ($USER && $USER->IsAuthorized()) {
    return;
}

$arResult = [
    "ERRORS" => [],
    "LAST_LOGIN" => "",
];

$request = Context::getCurrent()->getRequest();
if ($request->isPost() && $request->getPost("BRATKO_NOTES_AUTH") === "Y") {
    if (!check_bitrix_sessid()) {
        $arResult["ERRORS"][] = Loc::getMessage("NOTES_AUTH_ERROR_SESSION");
    } else {
        $login = trim((string)$request->getPost("USER_LOGIN"));
        $password = (string)$request->getPost("USER_PASSWORD");
        $arResult["LAST_LOGIN"] = $login;

        if ($login === "" || $password === "") {
            $arResult["ERRORS"][] = Loc::getMessage("NOTES_AUTH_ERROR_FILL_LOGIN_PASSWORD");
        } else {
            $result = $USER->Login($login, $password, "Y");
            if (is_array($result) && ($result["TYPE"] ?? "") === "ERROR") {
                $arResult["ERRORS"][] = strip_tags((string)$result["MESSAGE"]);
            } else {
                LocalRedirect($APPLICATION->GetCurPageParam());
            }
        }
    }
}

$this->IncludeComponentTemplate();
