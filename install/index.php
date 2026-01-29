<?php
/**
 * Установщик/деинсталлятор модуля bratko.notes.
 *
 * @author   Артём Братко
 * @link     https://arbratko.ru/
 */

use Bitrix\Main\Application;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class bratko_notes extends CModule
{
    public $MODULE_ID = "bratko.notes";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . "/../version.php";

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("BRATKO_NOTES_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("BRATKO_NOTES_MODULE_DESC");
        $this->PARTNER_NAME = Loc::getMessage("BRATKO_NOTES_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("BRATKO_NOTES_PARTNER_URI");
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if (!$this->InstallDB()) {
            $APPLICATION->ThrowException(Loc::getMessage("BRATKO_NOTES_INSTALL_ERROR"));
            return false;
        }

        RegisterModule($this->MODULE_ID);
        if (!$this->InstallFiles()) {
            UnRegisterModule($this->MODULE_ID);
            $APPLICATION->ThrowException(Loc::getMessage("BRATKO_NOTES_INSTALL_FILES_ERROR"));
            return false;
        }

        return true;
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        if (!$this->UnInstallDB()) {
            $APPLICATION->ThrowException(Loc::getMessage("BRATKO_NOTES_UNINSTALL_ERROR"));
            return false;
        }

        $this->UnInstallFiles();
        UnRegisterModule($this->MODULE_ID);

        return true;
    }

    public function InstallDB()
    {
        global $DB;

        if (!class_exists(\Bratko\Notes\NotesTable::class)) {
            require_once __DIR__ . "/../lib/notesTable.php";
        }

        $connection = Application::getConnection();
        $tableName = \Bratko\Notes\NotesTable::getTableName();

        if ($connection->isTableExists($tableName)) {
            return true;
        }

        $errors = $DB->RunSQLBatch(__DIR__ . "/db/mysql/install.sql");
        if ($errors !== false) {
            return false;
        }

        return true;
    }

    public function UnInstallDB()
    {
        global $DB;

        if (!class_exists(\Bratko\Notes\NotesTable::class)) {
            require_once __DIR__ . "/../lib/notesTable.php";
        }

        $connection = Application::getConnection();
        $tableName = \Bratko\Notes\NotesTable::getTableName();

        if (!$connection->isTableExists($tableName)) {
            return true;
        }

        $errors = $DB->RunSQLBatch(__DIR__ . "/db/mysql/uninstall.sql");
        if ($errors !== false) {
            return false;
        }

        return true;
    }

    public function InstallFiles()
    {
        $docRoot = $_SERVER["DOCUMENT_ROOT"];
        $componentsDir = $docRoot . "/local/components";
        if (!Directory::isDirectoryExists($componentsDir)) {
            if (!Directory::createDirectory($componentsDir)) {
                Debug::writeToFile(
                    ["path" => $componentsDir],
                    Loc::getMessage("BRATKO_NOTES_LOG_CREATE_DIR_ERROR"),
                    "/bitrix/log/bratko.notes.log"
                );
                return false;
            }
        }

        $componentsCopied = CopyDirFiles(
            __DIR__ . "/components",
            $componentsDir,
            true,
            true
        );

        $jsCopied = CopyDirFiles(
            __DIR__ . "/js",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js",
            true,
            true
        );

        $publicCopied = CopyDirFiles(
            __DIR__ . "/public",
            $_SERVER["DOCUMENT_ROOT"],
            true,
            true
        );

        if (!$componentsCopied || !$jsCopied || !$publicCopied) {
            Debug::writeToFile(
                [
                    "components" => $componentsCopied,
                    "js" => $jsCopied,
                    "public" => $publicCopied,
                ],
                Loc::getMessage("BRATKO_NOTES_LOG_COPY_FILES_ERROR"),
                "/bitrix/log/bratko.notes.log"
            );
            return false;
        }

        return true;
    }

    public function UnInstallFiles()
    {
        $dirsToRemove = [
            "/local/components/bratko/notes.list",
            "/bitrix/js/bratko.notes",
            "/notes",
        ];

        foreach ($dirsToRemove as $path) {
            DeleteDirFilesEx($path);
        }

        return true;
    }
}

