<?php
/**
 * ORM для таблицы заметок (bratko.notes).
 *
 * @author   Артём Братко
 * @link     https://arbratko.ru/
 */

namespace Bratko\Notes;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Type\DateTime;

class NotesTable extends DataManager
{
    public static function getTableName()
    {
        return "bratko_notes";
    }

    public static function getMap()
    {
        return [
            (new IntegerField("ID"))
                ->configurePrimary(true)
                ->configureAutocomplete(true),
            (new IntegerField("AUTHOR_ID"))
                ->configureRequired(true),
            (new StringField("TITLE"))
                ->configureRequired(true)
                ->configureSize(255),
            (new TextField("CONTENT"))
                ->configureRequired(false),
            (new DatetimeField("CREATED_AT"))
                ->configureRequired(true)
                ->configureDefaultValue(static function () {
                    return new DateTime();
                }),
            (new DatetimeField("UPDATED_AT"))
                ->configureRequired(true)
                ->configureDefaultValue(static function () {
                    return new DateTime();
                }),
        ];
    }

    public static function onBeforeAdd(Event $event)
    {
        $result = new EventResult();
        $result->modifyFields([
            "CREATED_AT" => new DateTime(),
            "UPDATED_AT" => new DateTime(),
        ]);

        return $result;
    }

    public static function onBeforeUpdate(Event $event)
    {
        $result = new EventResult();
        $result->modifyFields([
            "UPDATED_AT" => new DateTime(),
        ]);

        return $result;
    }
}

