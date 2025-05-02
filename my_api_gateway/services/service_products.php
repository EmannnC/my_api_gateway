<?php
header('Content-Type: application/json');

$products = [
    ["sku" => "A123", "productName" => "Widget"],
    ["sku" => "B456", "productName" => "Gadget"]
];

echo json_encode($products);
