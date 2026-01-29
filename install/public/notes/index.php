<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

global $USER, $APPLICATION;

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
if ($USER && $USER->IsAuthorized() && $request->getQuery("logout") === "yes") {
    $USER->Logout();
    LocalRedirect($APPLICATION->GetCurPageParam("", ["logout"]));
}

$APPLICATION->SetTitle("Заметки");

$APPLICATION->IncludeComponent(
    "bratko:notes.list",
    "",
    []
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");

