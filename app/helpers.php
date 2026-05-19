<?php

use Carbon\Carbon;

/**
 * แสดงวันที่แบบไทย พ.ศ. (เช่น 19 พ.ค. 2569)
 */
function thaiDate(mixed $date): string
{
    if (!$date) return '—';
    $c = $date instanceof Carbon ? $date : Carbon::parse($date);
    return $c->locale('th')->translatedFormat('j M') . ' ' . ($c->year + 543);
}

/**
 * แสดงวันที่และเวลาแบบไทย พ.ศ. (เช่น 19 พ.ค. 2569 13:00 น.)
 */
function thaiDateTime(mixed $date): string
{
    if (!$date) return '—';
    $c = $date instanceof Carbon ? $date : Carbon::parse($date);
    return $c->locale('th')->translatedFormat('j M') . ' ' . ($c->year + 543) . ' ' . $c->format('H:i') . ' น.';
}

/**
 * แสดงวันที่และเวลา (มีวินาที) แบบไทย พ.ศ. (เช่น 19 พ.ค. 2569 13:00:00 น.)
 */
function thaiDateTimeSec(mixed $date): string
{
    if (!$date) return '—';
    $c = $date instanceof Carbon ? $date : Carbon::parse($date);
    return $c->locale('th')->translatedFormat('j M') . ' ' . ($c->year + 543) . ' ' . $c->format('H:i:s') . ' น.';
}
