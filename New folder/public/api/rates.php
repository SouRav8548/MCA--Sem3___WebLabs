<?php
// api/rates.php
require_once __DIR__ . '/../inc/db.php';
// Use an external reliable API like exchangerate.host (free) or fixer's paid endpoint.
// This script proxies the request and caches responses to avoid exposing API keys.


$cacheFile = sys_get_temp_dir() . '/rates_cache.json';
$cacheTtl = 300; // 5 minutes
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
$data = json_decode(file_get_contents($cacheFile), true);
} else {
// Example: use exchangerate.host free endpoint (no key required)
$endpoint = 'https://api.exchangerate.host/latest?base=USD';
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$resp = curl_exec($ch);
curl_close($ch);
$data = json_decode($resp, true);
if (isset($data['rates'])) file_put_contents($cacheFile, json_encode($data));
}


// If called with from/to/amount, compute
$from = $_GET['from'] ?? null; $to = $_GET['to'] ?? null; $amount = floatval($_GET['amount'] ?? 1);
header('Content-Type: application/json');
if ($from && $to && isset($data['rates'][$from]) && isset($data['rates'][$to])) {
// convert amount
$rate = ($data['rates'][$to] / $data['rates'][$from]);
echo json_encode(['result' => $amount * $rate]);
exit;
}


echo json_encode($data);