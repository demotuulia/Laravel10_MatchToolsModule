<?php

namespace Modules\Matches\Enums;

enum EMatchType: string
{
    case CHECK_BOX = 'check_box';
    case MULTIPLE_CHOOSE = 'multiple_choose';
    case RADIO_BUTTON = 'radio_button';
    case EQUAL = 'equal';
    case RANGE = 'range';
    case BIGGER_THAN = 'bigger_than';
    case SMALLER_THAN = 'smaller_than';
    case DATE_FROM = 'date_from';
    case DATE_TO = 'date_to';
    case MENU = 'menu';

    public static function value(string $name) {
        return constant("self::$name");
    }

    public static function multipleValueTypes() {
        return [
            (self::RANGE)->name,
            (self::MULTIPLE_CHOOSE)->name,
        ];
    }
}
