<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$errorText = "";
if (!empty($arResult["ERRORS"])) {
    $errorText = implode("\n", $arResult["ERRORS"]);
}
$isSuccess = !empty($arResult["SUCCESS"]);
?>

<p class="auth-modal__error" id="registerError" aria-hidden="true" role="alert"></p>

<div id="registerFormBlock" class="auth-modal__form-block<?= $isSuccess ? " auth-modal__form-block--hidden" : ""; ?>">
    <form class="auth-modal__form" id="registerForm" novalidate>
        <?= bitrix_sessid_post(); ?>
        <input
            type="text"
            class="auth-modal__input"
            id="registerName"
            name="REGISTER_NAME"
            placeholder="<?= Loc::getMessage("NOTES_REGISTER_PLACEHOLDER_NAME"); ?>"
            autocomplete="name"
            value="<?= htmlspecialcharsbx($arResult["VALUES"]["NAME"]); ?>"
            required
        >
        <input
            type="text"
            class="auth-modal__input"
            id="registerLogin"
            name="REGISTER_LOGIN"
            placeholder="<?= Loc::getMessage("NOTES_REGISTER_PLACEHOLDER_LOGIN"); ?>"
            autocomplete="username"
            value="<?= htmlspecialcharsbx($arResult["VALUES"]["LOGIN"]); ?>"
            required
        >
        <input
            type="email"
            class="auth-modal__input"
            id="registerEmail"
            name="REGISTER_EMAIL"
            placeholder="<?= Loc::getMessage("NOTES_REGISTER_PLACEHOLDER_EMAIL"); ?>"
            autocomplete="email"
            value="<?= htmlspecialcharsbx($arResult["VALUES"]["EMAIL"]); ?>"
            required
        >
        <input
            type="password"
            class="auth-modal__input"
            id="registerPassword"
            name="REGISTER_PASSWORD"
            placeholder="<?= Loc::getMessage("NOTES_REGISTER_PLACEHOLDER_PASSWORD"); ?>"
            autocomplete="new-password"
            required
        >
        <input
            type="password"
            class="auth-modal__input"
            id="registerPasswordConfirm"
            name="REGISTER_PASSWORD_CONFIRM"
            placeholder="<?= Loc::getMessage("NOTES_REGISTER_PLACEHOLDER_PASSWORD_CONFIRM"); ?>"
            autocomplete="new-password"
            required
        >
        <div class="auth-modal__buttons">
            <button type="button" class="auth-modal__btn auth-modal__btn--cancel" id="registerModalCancel"><?= Loc::getMessage("NOTES_REGISTER_BTN_CANCEL"); ?></button>
            <button type="submit" class="auth-modal__btn auth-modal__btn--submit" id="registerSubmitBtn"><?= Loc::getMessage("NOTES_REGISTER_BTN_SUBMIT"); ?></button>
        </div>
    </form>
</div>

<div id="registerSuccessBlock" class="auth-modal__success-block<?= !$isSuccess ? " auth-modal__success-block--hidden" : ""; ?>">
    <div class="auth-modal__success">
        <div class="auth-modal__success-title"><?= Loc::getMessage("NOTES_REGISTER_SUCCESS_TITLE"); ?></div>
        <div class="auth-modal__timer-line"><?= Loc::getMessage("NOTES_REGISTER_TIMER_LINE"); ?></div>
        <div class="auth-modal__timer-value" id="registerSuccessTimer" data-seconds="3" aria-live="polite">3</div>
    </div>
</div>
