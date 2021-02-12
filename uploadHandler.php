<?php

function failure($code, $msg)
{
    echo json_encode(
        array('code' => $code,
            'msg' => $msg,
        ));
    exit();
}

function success($successFileNames,$overWriteFileNames)
{
    global $target_dir;
    $returnUrls = array();

    foreach ($successFileNames as $item) {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $target_dir . $item;
        $returnUrls[] = $url;
    }
    echo json_encode(array('code' => '100',
        'payload' => array(
            'urls' => $returnUrls,
            'overwrites' => $overWriteFileNames,
        )
    ));
    exit();
}

function getRandomNum()
{
    return rand(10000, 99999);
}

$target_dir = "upload/";
function main()
{
    $cover = $_REQUEST['cover'];
    $singleDir = $_REQUEST['singleDir'];

    global $target_dir;

    if ($singleDir) {
        $target_dir .= time();
    }

    $existFiles = array();
    for ($i = 0; $i < count($_FILES["fileToUpload"]['error']); $i++) {
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"][$i]);
        if (file_exists($target_file)) {
            $existFiles[] = $_FILES["fileToUpload"]["name"][$i];
        }
    }

    $successFiles = array();
    $moveErrorNames = array();
    for ($i = 0; $i < count($_FILES["fileToUpload"]['error']); $i++) {
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"][$i]);
        if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"][$i], $target_file)) {
            $moveErrorNames[] = $_FILES["fileToUpload"]["name"][$i];
        } else {
            $successFiles[] = $_FILES["fileToUpload"]["name"][$i];
        }
    }

    if (count($moveErrorNames) == 0) {
        success($successFiles,$existFiles);
    } else {
        failure('206', '有一部分成功进来辣，但是' . join(',', $moveErrorNames) . '失败了呢');
    }
}

function checkExist()
{
    $names = $_REQUEST['names'];
    global $target_dir;
    $existFiles = [];
    for ($i = 0; $i < count($names); $i++) {
        $target_file = $target_dir . $names[$i];
        if (file_exists($target_file)) {
            $existFiles[] = $names[$i];
        }
    }

    echo json_encode([
        'code' => 200,
        'payload' => [
            'exists' => $existFiles
        ]
    ]);
}

$action = $_GET['action'];
switch ($action) {
    case 'upload':
        main();
        break;
    case 'checkExist':
        checkExist();
        break;

}

?>
