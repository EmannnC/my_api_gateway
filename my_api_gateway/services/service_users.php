<?php
header('Content-Type: application/json');

$users = [
    ["id" => 1, "name" => "Alice"],
    ["id" => 2, "name" => "Bob"]
];

echo json_encode($users);
