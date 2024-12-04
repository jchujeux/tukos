<?php
try {
    $url = "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm/records?select=date%2C%20ff%2C%20dd&where=date%20%3E%20date%272024-11-28%27%20and%20libgeo%20%3D%20%22Athis-Mons%22%20&order_by=date%20DESC&limit=20";
    $data = file_get_contents($url);
    $decodedData = json_decode($data, true);
    echo var_dump($decodedData);
} catch(Exception $e) {
    print $e->getMessage();
}