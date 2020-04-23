<?php
/**
 * A simple server to return all dates for all locations supported by Yasumi
 */
set_time_limit(0);
require 'vendor/autoload.php';

if (isset($_GET['start']) && isset($_GET['end'])) {
    $years = range($_GET['start'], $_GET['end']);
} else {
    $years = range(2019, 2022);
}

//Grab all providers supported by this library and retrieve all holidays
//for the specified year range
$root = './src/Yasumi/Provider';
$files = glob("{$root}/{,*/,*/*/,*/*/*/}*.php", GLOB_BRACE);
$items = [];
$errors = [];
foreach ($files as $file) {
    $class = str_replace([$root.'/', '.php'], '', $file);
    foreach ($years as $year) {
        try {
            $location = Yasumi\Yasumi::create($class, $year);
            if (substr_count($location::ID, '-') > 1) {
                throw new \Exception("Unsupported location type " . $location::ID);
            }

            foreach ($location as $holiday) {
                $type = $holiday->getType();
                $name = $holiday->getName();
                $date = (string)$holiday;
                $ts = strtotime($date);
                $day = date('D', $ts);
                $item = [
                    'requested_year' => $year,
                    'name' => $name,
                    'short_name' => $holiday->shortName,
                    'type' => $type,
                    'date' => $date,
                    'day' => $day,
                    'location' => $location::ID,
                    'translations' => $holiday->translations
                ];
                if (isset($holiday->substitutedHoliday)) {
                    $item['original_date'] = $holiday->substitutedHoliday;
                }
                $items[] = $item;
            }
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

echo json_encode([
    'items' => $items,
    'errors' => $errors
], \JSON_PRETTY_PRINT);