<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

define('BASE_URL', 'http://localhost');

require_once __DIR__ . '/../classes/BlockData.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Upload.php';
require_once __DIR__ . '/../utils/helpers.php';

class TestPDO extends PDO
{
    public function __construct()
    {
    }
}

$tests = 0;
$failures = 0;

function check($condition, $label)
{
    global $tests, $failures;

    $tests++;

    if ($condition) {
        echo "  PASS  {$label}\n";
        return;
    }

    $failures++;
    echo "  FAIL  {$label}\n";
}

echo "BlockData::normalizeSubmittedBlocks\n";

$dropped = BlockData::normalizeSubmittedBlocks([['title' => 'no type here']]);
check($dropped === [], 'a block without a type is dropped');

$normalized = BlockData::normalizeSubmittedBlocks([[
    'type' => 'slider_gallery',
    'autoplay' => '1',
    'show_arrows' => '0',
    'slider_speed' => '7',
    'video_source' => 'bogus',
    'background_opacity_desktop' => 'abc',
    'gallery_data' => 'not-json',
    'selected_post_ids' => [3, 1, 2],
]]);
$block = $normalized[0];
check($block['autoplay'] === true, 'boolean "1" becomes true');
check($block['show_arrows'] === false, 'boolean "0" becomes false');
check($block['slider_speed'] === 7, 'integer string becomes int');
check($block['video_source'] === 'url', 'invalid enum falls back to default');
check($block['background_opacity_desktop'] === '1.0', 'invalid decimal falls back to default');
check($block['gallery_data'] === '[]', 'invalid JSON becomes an empty array');
check($block['selected_post_ids'] === '3,1,2', 'post id array becomes a csv string');
check($block['order_num'] === 1, 'order_num is assigned from position');

$valid = BlockData::normalizeSubmittedBlocks([['type' => 'hero', 'hero_layout' => 'left']]);
check($valid[0]['hero_layout'] === 'left', 'a valid enum value is kept');

echo "User password policy\n";

$user = new User(null);
check($user->validatePassword('Str0ng!Passw0rd') === true, 'a strong password is accepted');
check($user->validatePassword('Ab1!xyz') === false, 'a short password is rejected');
check($user->validatePassword('str0ng!passw0rd') === false, 'a password without uppercase is rejected');
check($user->validatePassword('Str0ngPassw0rd') === false, 'a password without a special character is rejected');
check($user->validatePassword('Strong!Password') === false, 'a password without a digit is rejected');

echo "Upload validation\n";

$groups = Upload::getSupportedMediaGroups();
check(in_array('image', $groups, true) && in_array('video', $groups, true) && in_array('audio', $groups, true), 'supported groups include image, video and audio');
check(Upload::isSupportedMediaGroup('image') === true, 'image is a supported group');
check(Upload::isSupportedMediaGroup('document') === false, 'document is not a supported group');

$upload = new Upload(new TestPDO());
$reflection = new ReflectionObject($upload);

$getExtension = $reflection->getMethod('getFileExtension');
$getExtension->setAccessible(true);
check($getExtension->invoke($upload, 'Photo.JPG') === 'jpg', 'a file extension is lower-cased');
check($getExtension->invoke($upload, 'evil.php') === 'php', 'a php extension is detected');

$sanitize = $reflection->getMethod('sanitizeFileBaseName');
$sanitize->setAccessible(true);
check($sanitize->invoke($upload, 'My Photo!.jpg') === 'my-photo', 'a base name is sanitized');

$isAllowed = $reflection->getMethod('isAllowedFile');
$isAllowed->setAccessible(true);
check($isAllowed->invoke($upload, 'jpg', 'image/jpeg') === true, 'jpg with image/jpeg is allowed');
check($isAllowed->invoke($upload, 'php', 'text/html') === false, 'php is not allowed');
check($isAllowed->invoke($upload, 'jpg', 'text/html') === false, 'jpg with a mismatched mime is rejected');

echo "Pagination math\n";

$emptyPage = alpiCalculatePagination(0, 10, 1);
check($emptyPage['totalPages'] === 1 && $emptyPage['offset'] === 0, 'an empty set yields one page at offset 0');

$secondPage = alpiCalculatePagination(25, 10, 2);
check($secondPage['totalPages'] === 3 && $secondPage['offset'] === 10, 'page 2 of 25 items at 10/page offsets by 10');

$beyondLast = alpiCalculatePagination(25, 10, 99);
check($beyondLast['currentPage'] === 3 && $beyondLast['offset'] === 20, 'an out-of-range page clamps to the last page');

$belowFirst = alpiCalculatePagination(25, 10, 0);
check($belowFirst['currentPage'] === 1 && $belowFirst['offset'] === 0, 'a page below 1 clamps to the first page');

$badPerPage = alpiCalculatePagination(25, 0, 1);
check($badPerPage['perPage'] === 10, 'a non-positive per-page falls back to 10');

echo "\n{$tests} checks run, {$failures} failure(s)\n";

exit($failures === 0 ? 0 : 1);
