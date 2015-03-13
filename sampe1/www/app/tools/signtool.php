<?php
$content = '{"orderId":"12999763169054705759.1321508026818968","packageName":"com.droidhen.word","productId":"19000","purchaseTime":1358142613000,"purchaseState":0,"developerPayload":"1000022","purchaseToken":"qrxfurypniuujejhozgqvosv.AO-J1OwmK-0OcC6JvbZ1FQ8ow86tu-gygw5M003MjS9q-2oOjVw44Ds0G3ua7Thalf-f0lE3vfp0y5E93prykt0oY4kX3LJbbP2fM_8lRswJ1bE9Q7IT5Gc"}';
$content = '123456';
$sign = 'HyQAY0u+O5WzW5wAC6B6sHRqIyT10v2mce9dBjRUEyWLs3ylioCYuxWQdmbBx5xXRIJuJPDnCfF5uG/0gxn4jnKWSlsEvQl2LDr+jWz2/DFpfRGLnFeKDNbK8ZbbTvUcii6u14EAUKTpQb2EkCY5AkmXtY1gPK9phU56D8f3u4dm91UDoWgyp078AjHC/iPJqy5uKqF9/Giw2VgMvja30FKJWEPlYTW33li4E9w94nn45Q+Xwq58eU2S9Om4+MRKxGBbIU72tOngsVd5rpi+/PVhIAySCEUeNxildv+GhfPMWqAHtAroHaSA2bXHaxq9BDthS8vPtzXweNNVTcB/25DPhLQ9gcotM27M4aLrgNVtxhxJzYkGQ9mGXdh9XQ3EMGPnmIdpedhlojXvrdQrmF5ygFYfMy5GzoYHU1RfcpNDZPrACX3zce0cOdJVYxfdx7N6jMgUiRWUar/NY8ivOZcNDg4DRhBfqNKgAsKQQCPcA3K4oKF+LOA5fnYbAw/5gWlmUBxOmKhjQU91EqT7j3V1WVpyeasYmkJPORvBpr02ScVx2F71HZM11rRytveANEhpIUrI01auhMOySng353t+/VtG6eB4yvyMwVxyYK0nnGGAOm/0VqZJqo/OvphCE3m3w6iybSRDgvJJNiaHvVgyYJLXTQuxBFRu65FLquI=';
$sign = base64_decode($sign);

$key_file = __DIR__ . '/key.pem';
$pkey = openssl_pkey_get_private(file_get_contents($key_file));
$ukey = openssl_pkey_get_public(file_get_contents($key_file . '.pub'));

// openssl_sign($content, $sign, $pkey);
$result = openssl_verify($content, $sign, $ukey);
openssl_pkey_free($pkey);
openssl_pkey_free($ukey);
echo base64_encode($sign), PHP_EOL, $result;