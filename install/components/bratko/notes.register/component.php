<?php
/**
 * Компонент bratko:notes.register.
 *
 * @author   Артём Братко
 * @link     https://arbratko.ru/
 */

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
    "VALUES" => [
        "NAME" => "",
        "LOGIN" => "",
        "EMAIL" => "",
    ],
    "SUCCESS" => false,
    "REDIRECT_URL" => $APPLICATION->GetCurPageParam(),
];

$request = Context::getCurrent()->getRequest();
if ($request->isPost() && $request->getPost("BRATKO_NOTES_REGISTER") === "Y") {
    if (!check_bitrix_sessid()) {
        $arResult["ERRORS"][] = Loc::getMessage("NOTES_REGISTER_ERROR_SESSION");
    } else {
        $name = trim((string)$request->getPost("REGISTER_NAME"));
        $login = trim((string)$request->getPost("REGISTER_LOGIN"));
        $email = trim((string)$request->getPost("REGISTER_EMAIL"));
        $password = (string)$request->getPost("REGISTER_PASSWORD");
        $confirm = (string)$request->getPost("REGISTER_PASSWORD_CONFIRM");

        $arResult["VALUES"]["NAME"] = $name;
        $arResult["VALUES"]["LOGIN"] = $login;
        $arResult["VALUES"]["EMAIL"] = $email;

        if ($name === "" || $login === "" || $email === "" || $password === "" || $confirm === "") {
            $arResult["ERRORS"][] = Loc::getMessage("NOTES_REGISTER_ERROR_FILL_ALL");
        } elseif ($password !== $confirm) {
            $arResult["ERRORS"][] = Loc::getMessage("NOTES_REGISTER_ERROR_PASSWORD_MISMATCH");
        } else {
            $user = new CUser();
            $result = $user->Register($login, $name, "", $password, $confirm, $email);
            if (is_array($result) && ($result["TYPE"] ?? "") === "ERROR") {
                $msg = trim(strip_tags((string)($result["MESSAGE"] ?? "")));
                $arResult["ERRORS"][] = $msg !== ""
                    ? $msg
                    : Loc::getMessage("NOTES_REGISTER_ERROR_USER_EXISTS");
            } elseif (is_array($result) && !empty($result["ID"])) {
                if ($USER) {
                    $USER->Authorize((int)$result["ID"]);
                } else {
                    $arResult["ERRORS"][] = Loc::getMessage("NOTES_REGISTER_ERROR_AUTHORIZE_FAIL");
                }
                $arResult["SUCCESS"] = true;
            } else {
                $arResult["ERRORS"][] = Loc::getMessage("NOTES_REGISTER_ERROR_CREATE_USER");
            }
        }
    }
}

$this->IncludeComponentTemplate();
