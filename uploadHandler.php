<?php

function failure($code, $msg)
{
    echo json_encode(
        array('code' => $code,
            'msg' => $msg,
        ));
    exit();
}

function success($successFileNames)
{
    global $target_dir;
    $returnUrls=array();

    foreach ($successFileNames as $item) {
        $url = 'http://'.$_SERVER['HTTP_HOST'] .'/'. $target_dir.$item;
        $returnUrls[] = $url;
    }
    echo json_encode(array('code' => '100',
        'payload'=>array(
            'urls' => $returnUrls
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

    if (count($existFiles) != 0) {
        failure('500', join(",", $existFiles) . '人家已经有了呢');
    } else {
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
            success($successFiles);
        } else {
            failure('206', '有一部分成功进来辣，但是' . join(',', $moveErrorNames) . '失败了呢');
        }
    }
}

main();

?>
