<?php
/**
 * Шаблон компонента bratko:notes.auth.
 *
 * @author   Артём Братко
 * @link     https://arbratko.ru/
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$errorText = "";
if (!empty($arResult["ERRORS"])) {
    $errorText = implode("\n", $arResult["ERRORS"]);
}
?>

<p class="auth__error <?= $errorText ? "auth__error--visible" : ""; ?>" id="authError" aria-hidden="<?= $errorText ? "false" : "true"; ?>" role="alert">
    <?= htmlspecialcharsbx($errorText); ?>
</p>

<form class="auth__form" id="authForm" action="<?= htmlspecialcharsbx($APPLICATION->GetCurPageParam()); ?>" method="post" novalidate>
    <?= bitrix_sessid_post(); ?>
    <input type="hidden" name="BRATKO_NOTES_AUTH" value="Y" />
    <input
        type="text"
        class="auth__input"
        id="authLogin"
        name="USER_LOGIN"
        placeholder="<?= Loc::getMessage("NOTES_AUTH_PLACEHOLDER_LOGIN"); ?>"
        value="<?= htmlspecialcharsbx($arResult["LAST_LOGIN"]); ?>"
        autocomplete="username"
        required
    >
    <input
        type="password"
        class="auth__input"
        id="authPassword"
        name="USER_PASSWORD"
        placeholder="<?= Loc::getMessage("NOTES_AUTH_PLACEHOLDER_PASSWORD"); ?>"
        autocomplete="current-password"
        required
    >
    <button type="submit" class="auth__submit"><?= Loc::getMessage("NOTES_AUTH_BTN_SUBMIT"); ?></button>
</form>
