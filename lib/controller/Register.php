<?php
/**
 * AJAX-контроллер регистрации (bratko.notes).
 *
 * @author   Артём Братко
 * @link     https://arbratko.ru/
 */

namespace Bratko\Notes\Controller;

use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Register extends Controller
{
    public function configureActions()
    {
        return [
            "register" => [
                "prefilters" => [
                    new Csrf(),
                ],
            ],
        ];
    }

    /**
     * Регистрация пользователя (AJAX)
     * @param string $name
     * @param string $login
     * @param string $email
     * @param string $password
     * @param string $confirm
     * @return array{success: bool, errors?: string[]}
     */
    public function registerAction($name = "", $login = "", $email = "", $password = "", $confirm = "")
    {
        global $USER;

        if ($USER && $USER->IsAuthorized()) {
            return ["success" => true];
        }

        $name = trim((string)$name);
        $login = trim((string)$login);
        $email = trim((string)$email);
        $password = (string)$password;
        $confirm = (string)$confirm;

        $errors = [];

        if ($name === "" || $login === "" || $email === "" || $password === "" || $confirm === "") {
            $errors[] = Loc::getMessage("BRATKO_NOTES_REGISTER_ERROR_FILL_ALL");
        } elseif ($password !== $confirm) {
            $errors[] = Loc::getMessage("BRATKO_NOTES_REGISTER_ERROR_PASSWORD_MISMATCH");
        } else {
            $user = new \CUser();
            $result = $user->Register($login, $name, "", $password, $confirm, $email);
            if (is_array($result) && ($result["TYPE"] ?? "") === "ERROR") {
                $msg = trim(strip_tags((string)($result["MESSAGE"] ?? "")));
                $errors[] = $msg !== ""
                    ? $msg
                    : Loc::getMessage("BRATKO_NOTES_REGISTER_ERROR_USER_EXISTS");
            } elseif (is_array($result) && !empty($result["ID"])) {
                if ($USER) {
                    $USER->Authorize((int)$result["ID"]);
                } else {
                    $errors[] = Loc::getMessage("BRATKO_NOTES_REGISTER_ERROR_AUTHORIZE_FAIL");
                }
            } else {
                $errors[] = Loc::getMessage("BRATKO_NOTES_REGISTER_ERROR_CREATE_USER");
            }
        }

        if (!empty($errors)) {
            return ["success" => false, "errors" => $errors];
        }

        return ["success" => true];
    }
}
