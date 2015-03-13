<?php
error_reporting(E_ALL | E_STRICT | E_DEPRECATED);
$key_file = __DIR__ . '/key.pem';
$pkey = null;
$config = array(
    'digest_alg' => 'sha512',
    'private_key_bits' => 4096,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'config' => __DIR__ . '/openssl.cnf'
);
if (! is_file($key_file)) {
    $pkey = openssl_pkey_new($config);
    openssl_pkey_export_to_file($pkey, $key_file, NULL, $config);
} else {
    $pkey = openssl_pkey_get_private(file_get_contents($key_file));
}
$priv_key = '';
openssl_pkey_export($pkey, $priv_key, NULL, $config);
$key_detail = openssl_pkey_get_details($pkey);
$pub_key = $key_detail['key'];
openssl_pkey_free($pkey);
echo $priv_key, PHP_EOL, $pub_key;
file_put_contents($key_file.'.pub', $pub_key);
