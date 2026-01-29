<?php
/**
 * AJAX-контроллер входа (bratko.notes).
 *
 * @author   Артём Братко
 * @link     https://arbratko.ru/
 */

namespace Bratko\Notes\Controller;

use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Auth extends Controller
{
    public function configureActions()
    {
        return [
            "login" => [
                "prefilters" => [
                    new Csrf(),
                ],
            ],
        ];
    }

    /**
     * Вход пользователя (AJAX)
     * @param string $login
     * @param string $password
     * @return array{success: bool, errors?: string[]}
     */
    public function loginAction($login = "", $password = "")
    {
        global $USER;

        if ($USER && $USER->IsAuthorized()) {
            return ["success" => true];
        }

        $login = trim((string)$login);
        $password = (string)$password;

        $errors = [];

        if ($login === "" || $password === "") {
            $errors[] = Loc::getMessage("BRATKO_NOTES_AUTH_ERROR_FILL_LOGIN_PASSWORD");
        } else {
            $result = $USER->Login($login, $password, "Y");
            if (is_array($result) && ($result["TYPE"] ?? "") === "ERROR") {
                $errors[] = strip_tags((string)($result["MESSAGE"] ?? ""));
            }
        }

        if (!empty($errors)) {
            return ["success" => false, "errors" => $errors];
        }

        return ["success" => true];
    }
}
