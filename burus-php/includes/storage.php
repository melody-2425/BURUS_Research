<?php

function loadJson($filename) {
    $path = __DIR__ . '/../data/' . $filename;

    if (!file_exists($path)) {
        return [];
    }

    $content = file_get_contents($path);
    $data = json_decode($content, true);

    return is_array($data) ? $data : [];
}

function saveJson($filename, $data)
{
    $path = __DIR__ . '/../data/' . $filename;
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
}

function getNextId($items)
{
    if (empty($items)) {
        return 1;
    }

    $ids = array_column($items, 'id');
    return max($ids) + 1;
}
?>
