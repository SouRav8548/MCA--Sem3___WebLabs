<?php
// Main Entry Point
require_once 'controllers/FactorialController.php';

$controller = new FactorialController();
$controller->handleRequest();
?>