<?php

require '../../../config/database.php';
require '../../../config/autoload.php';


$post = new Post($db);

if (isset($_GET['type']) && isset($_GET['index'])) {
    echo $post->getBlockHtml($_GET['type'], $_GET['index']);
}
