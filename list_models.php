<?php
// filters models for 'nex-agi'
$ch = curl_init('https://openrouter.ai/api/v1/models');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$found = [];

if (isset($data['data'])) {
    foreach ($data['data'] as $model) {
        if (strpos($model['id'], 'nex-agi') !== false) {
            $found[] = $model['id'];
        }
        if (strpos($model['id'], 'deepseek') !== false && strpos($model['id'], 'free') !== false) {
             $found[] = "RELATED: " . $model['id'];
        }
    }
}

print_r($found);
?>
