<?php


function retWrapper($payload, $code = 200, $msg = 'ok')
{
    echo json_encode([
        'code' => $code,
        'msg' => $msg,
        'payload' => $payload,
    ]);
}

$root_dir = "upload";

header('Access-Control-Allow-Origin:http://localhost:8080');

class Actions
{
    public function upload()
    {
        //$cover = $_REQUEST['cover'];
       // $singleDir = $_REQUEST['singleDir'];
        $path = $_REQUEST['path'];
        global $root_dir;
        $target_dir = $root_dir . $path . '/';
//        if ($singleDir) {
//            $target_dir .= time();
//        }
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
            if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"][$i], $_SERVER['DOCUMENT_ROOT'] . '/' . $target_file)) {
                $moveErrorNames[] = $_FILES["fileToUpload"]["name"][$i];
            } else {
                $successFiles[] = $_FILES["fileToUpload"]["name"][$i];
            }
        }

        if (count($moveErrorNames) == 0) {

            $returnUrls = [];

            foreach ($successFiles as $item) {
                $url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $target_dir . $item;
                $returnUrls[] = $url;
            }
            retWrapper([
                'urls' => $returnUrls,
                'overwrites' => $existFiles,
            ]);
        } else {
            retWrapper(null, 206, '有一部分成功进来辣，但是' . join(',', $moveErrorNames) . '失败了呢');

        }
    }

    public function checkExist()
    {
        $names = $_REQUEST['names'];
        global $root_dir;
        $existFiles = [];
        for ($i = 0; $i < count($names); $i++) {
            $target_file = $root_dir . $names[$i];
            if (file_exists($target_file)) {
                $existFiles[] = $names[$i];
            }
        }
        retWrapper([
            'exists' => $existFiles
        ]);
    }

    public function fileList()
    {
        $dir = $_REQUEST['dir'];
        $arr = scandir('./upload' . $dir);
        $ret = [];
        foreach ($arr as $item) {
            $obj = [
                'name' => $item,
                'is_dir' => is_dir($_SERVER['DOCUMENT_ROOT'] . '/upload/' . $dir . '/' . $item)
            ];
            $ret[] = $obj;

        }
        retWrapper($ret);
    }

    public function mkdir()
    {
        $name = $_REQUEST['path'];
        mkdir('./upload' . $name, 0777, true);
        retWrapper(null);
    }

    public function deleteFile()
    {
        $path = $_REQUEST['path'];
        unlink($_SERVER['DOCUMENT_ROOT'] . '/upload/'.$path);
        retWrapper(null);
    }

    public function test()
    {
        retWrapper();

    }
}

error_reporting(E_ALL);
set_error_handler('handle_error');
function handle_error($no, $msg, $file, $line)
{
    retWrapper(null, 500,$no.$msg);
    exit();
}

$action = $_GET['action'];
$class = new ReflectionClass(Actions::class);
$inst = $class->newInstance();
$method = $class->getMethod($action);
$result = $method->invoke($inst);

?>
