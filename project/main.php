<?php
/**
 * @author Pierre-Henry Soria <me@ph7.me>
 */

$win = new SDPanel();

$win->setCaption('My Native PHP App, Hahaha');

$mainTable = new Table();

$button = new Button();
$button->setCaption('Click on me, pleaaaseee!');
$button->onTap(Helper::clickMe());

$actionBar = new ActionBar();
$actionBar->setClass('applicationBars');
$win->addControl($actionBar);

$mainTable->addControl($button, 1, 1);
$win->addControl($mainTable);

class Helper
{
    const API_URL = 'http://www.devxtend.com/Gecko/samples/3_2/rest/main_load_data';

    const YOUTUBE_VIDEO_URL = 'https://www.youtube.com/watch?v=01DCgOBBHJ0';

    public static function clickMe()
    {
        echo 'Hayayayaya!';
    }

    public static function loadData()
    {
        $hc = new \HttpClient();
        $rs = $hc->Execute('GET', self::API_URL);

        $struct = [
            [
                'id' => DataType::Character(6),
                'name' => DataType::Character(100)
            ]
        ];

        Data::FromJson($struct, $rs);
    }
}
