<?php
/**
 * AJAX-контроллер заметок (bratko.notes).
 *
 * @author   Артём Братко
 * @link     https://arbratko.ru/
 */

namespace Bratko\Notes\Controller;

use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Localization\Loc;
use Bratko\Notes\NotesTable;

Loc::loadMessages(__FILE__);

class Notes extends Controller
{
    public function configureActions()
    {
        return [
            "list" => [
                "prefilters" => [
                    new Authentication(),
                    new Csrf(),
                ],
            ],
            "add" => [
                "prefilters" => [
                    new Authentication(),
                    new Csrf(),
                ],
            ],
            "update" => [
                "prefilters" => [
                    new Authentication(),
                    new Csrf(),
                ],
            ],
            "delete" => [
                "prefilters" => [
                    new Authentication(),
                    new Csrf(),
                ],
            ],
        ];
    }

    public function listAction()
    {
        $userId = (int)$this->getCurrentUser()->getId();
        if ($userId <= 0) {
            $this->addError(new Error(Loc::getMessage("BRATKO_NOTES_AUTH_REQUIRED")));
            return null;
        }

        $items = NotesTable::getList([
            "select" => ["ID", "TITLE", "CONTENT", "CREATED_AT", "UPDATED_AT"],
            "filter" => ["=AUTHOR_ID" => $userId],
            "order" => ["UPDATED_AT" => "DESC"],
        ])->fetchAll();

        return [
            "items" => $items,
        ];
    }

    public function addAction($title, $content = "")
    {
        $userId = (int)$this->getCurrentUser()->getId();
        if ($userId <= 0) {
            $this->addError(new Error(Loc::getMessage("BRATKO_NOTES_AUTH_REQUIRED")));
            return null;
        }

        $title = trim((string)$title);
        $content = trim((string)$content);

        if ($title === "") {
            $this->addError(new Error(Loc::getMessage("BRATKO_NOTES_TITLE_REQUIRED")));
            return null;
        }

        try {
            $result = NotesTable::add([
                "AUTHOR_ID" => $userId,
                "TITLE" => $title,
                "CONTENT" => $content,
            ]);
        } catch (\Throwable $e) {
            Debug::writeToFile(
                $e->getMessage(),
                "Ошибка добавления заметки",
                "/log/bratko.notes.log"
            );
            $this->addError(new Error(Loc::getMessage("BRATKO_NOTES_INTERNAL_ERROR")));
            return null;
        }

        if (!$result->isSuccess()) {
            Debug::writeToFile(
                $result->getErrorMessages(),
                "Ошибка добавления заметки",
                "/log/bratko.notes.log"
            );
            $this->addErrors($result->getErrors());
            return null;
        }

        return [
            "id" => $result->getId(),
        ];
    }

    public function updateAction($id, $title, $content = "")
    {
        $userId = (int)$this->getCurrentUser()->getId();
        if ($userId <= 0) {
            $this->addError(new Error(Loc::getMessage("BRATKO_NOTES_AUTH_REQUIRED")));
            return null;
        }

        $id = (int)$id;
        $title = trim((string)$title);
        $content = trim((string)$content);

        if ($id <= 0) {
            $this->addError(new Error(Loc::getMessage("BRATKO_NOTES_ID_REQUIRED")));
            return null;
        }

        if ($title === "") {
            $this->addError(new Error(Loc::getMessage("BRATKO_NOTES_TITLE_REQUIRED")));
            return null;
        }

        $note = NotesTable::getList([
            "select" => ["ID"],
            "filter" => ["=ID" => $id, "=AUTHOR_ID" => $userId],
            "limit" => 1,
        ])->fetch();

        if (!$note) {
            $this->addError(new Error(Loc::getMessage("BRATKO_NOTES_NOT_FOUND")));
            return null;
        }

        try {
            $result = NotesTable::update($id, [
                "TITLE" => $title,
                "CONTENT" => $content,
            ]);
        } catch (\Throwable $e) {
            Debug::writeToFile(
                $e->getMessage(),
                "Ошибка обновления заметки",
                "/log/bratko.notes.log"
            );
            $this->addError(new Error(Loc::getMessage("BRATKO_NOTES_INTERNAL_ERROR")));
            return null;
        }

        if (!$result->isSuccess()) {
            Debug::writeToFile(
                $result->getErrorMessages(),
                "Ошибка обновления заметки",
                "/log/bratko.notes.log"
            );
            $this->addErrors($result->getErrors());
            return null;
        }

        return [
            "id" => $id,
        ];
    }

    public function deleteAction($id)
    {
        $userId = (int)$this->getCurrentUser()->getId();
        if ($userId <= 0) {
            $this->addError(new Error(Loc::getMessage("BRATKO_NOTES_AUTH_REQUIRED")));
            return null;
        }

        $id = (int)$id;
        if ($id <= 0) {
            $this->addError(new Error(Loc::getMessage("BRATKO_NOTES_ID_REQUIRED")));
            return null;
        }

        $note = NotesTable::getList([
            "select" => ["ID"],
            "filter" => ["=ID" => $id, "=AUTHOR_ID" => $userId],
            "limit" => 1,
        ])->fetch();

        if (!$note) {
            $this->addError(new Error(Loc::getMessage("BRATKO_NOTES_NOT_FOUND")));
            return null;
        }

        try {
            $result = NotesTable::delete($id);
        } catch (\Throwable $e) {
            Debug::writeToFile(
                $e->getMessage(),
                "Ошибка удаления заметки",
                "/log/bratko.notes.log"
            );
            $this->addError(new Error(Loc::getMessage("BRATKO_NOTES_INTERNAL_ERROR")));
            return null;
        }

        if (!$result->isSuccess()) {
            Debug::writeToFile(
                $result->getErrorMessages(),
                "Ошибка удаления заметки",
                "/log/bratko.notes.log"
            );
            $this->addErrors($result->getErrors());
            return null;
        }

        return [
            "id" => $id,
        ];
    }
}

