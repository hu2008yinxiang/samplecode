<?php
include 'photo_normalize.php';
// delete all old pics
echo 'unlinking...', PHP_EOL;
array_map('unlink', glob(__DIR__ . '/../../www/app/config/iphotos/pics/*.jpg'));
echo 'unlinked.', PHP_EOL;
// copy rated pics
echo 'copying...', PHP_EOL;
foreach (array(
    __DIR__ . '/sum/h/*.jpg',
    __DIR__ . '/sum/m/*.jpg'
) as $pattern) {
    foreach (glob($pattern) as $file) {
        $dst = __DIR__ . '/../../www/app/config/iphotos/pics/' . basename($file);
        copy($file, $dst);
        pic_normalize($dst);
    }
}
echo 'copied.', PHP_EOL;
// re-generate photo_map
echo 'mapping...', PHP_EOL;
include __DIR__ . '/../photo_src/gender_sep.php';
echo 'mapped.', PHP_EOL;
// gen-map
$data = array(
    'male_size' => 0,
    'female_size' => 0,
    'male' => array(),
    'female' => array()
);
echo 're-mapping.', PHP_EOL;
$genders = loadData();
foreach (glob(__DIR__ . '/sum/h/*.jpg') as $file) {
    $name = basename($file, '.jpg');
    $gender = isset($genders[$name]) ? $genders[$name] : 'female';
    $data[$gender][] = $name . '.jpg';
}
// var_export($data);
$data['male_size'] = count($data['male']);
$data['female_size'] = count($data['female']);
echo 're-mapped.', PHP_EOL;
echo $data['male_size'], ' ', $data['female_size'], PHP_EOL;
Misc::cacheToFile($data, __DIR__ . '/h_photo_map.php');
copy(__DIR__ . '/h_photo_map.php', __DIR__ . '/../../www/app/config/h_photo_map.php');