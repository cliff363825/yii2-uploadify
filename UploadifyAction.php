<?php
namespace cliff363825\uploadify;

use Yii;
use yii\base\Action;

/**
 * Class UploadifyAction
 * @package cliff363825\uploadify
 * @property int $maxSize
 */
class UploadifyAction extends Action
{
    /**
     * 文件上传根路径
     * @var string
     */
    public $basePath = '@webroot';
    /**
     * 文件上传根url
     * @var string
     */
    public $baseUrl = '@web';
    /**
     * 文件保存相对路径
     * @var string
     */
    public $savePath = 'uploads';
    /**
     * 文件保存子目录路径
     * @var array|\Closure|array
     */
    public $subPath = ['date', 'Ym/d'];
    /**
     * @var string
     */
    public $uniqueSalt = '';
    /**
     * @var string
     */
    public $fileObjName = 'Filedata';
    /**
     * a list of file name extensions that are allowed to be uploaded.
     * 定义允许上传的文件扩展名
     * @var array
     */
    public $extensions = ['jpg', 'jpeg', 'gif', 'png'];
    /**
     * the maximum number of bytes required for the uploaded file.
     * 最大文件大小
     * @var int
     */
    private $_maxSize = 1000000;

    /**
     * Runs the action
     */
    public function run()
    {
        // Define a destination
        $basePath = rtrim(Yii::getAlias($this->basePath), '\\/') . '/';
        $baseUrl = rtrim(Yii::getAlias($this->baseUrl), '\\/') . '/';
        $timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : '';
        $token = isset($_POST['token']) ? $_POST['token'] : '';
        $verifyToken = md5($this->uniqueSalt . $timestamp);
        $fileObjName = $this->fileObjName;
        $maxSize = $this->getMaxSize();
        if (empty($_FILES)) {
            $this->sendJson(1, 'File not found.');
        }
        if ($token != $verifyToken) {
            $this->sendJson(1, 'Invalid token.');
        }
        if (!empty($_FILES[$fileObjName]['error'])) {
            $this->sendJson(1, 'Upload failed:' . $_FILES[$fileObjName]['error']);
        }
        $tempFile = $_FILES[$fileObjName]['tmp_name'];
        $fileName = $_FILES[$fileObjName]['name'];
        $fileSize = $_FILES[$fileObjName]['size'];
        // Validate the file size
        if ($fileSize > $maxSize) {
            $this->sendJson(1, 'File too large.');
        }
        // Validate the file type
        $fileTypes = $this->extensions; // File extensions
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        if (!in_array($fileExt, $fileTypes)) {
            $this->sendJson(1, 'Invalid file type.');
        }
        $savePath = rtrim($this->savePath, '\\/') . '/';
        $savePath .= rtrim($this->resolveSubPath($this->subPath, $fileName), '\\/') . '/';
        if (!is_dir($basePath . $savePath)) {
            mkdir($basePath . $savePath, 0755, true);
        }
        $newFilename = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $fileExt;
        $filePath = $basePath . $savePath . $newFilename;
        $fileUrl = $baseUrl . $savePath . $newFilename;
        if (!move_uploaded_file($tempFile, $filePath)) {
            $this->sendJson(1, 'Server error.');
        }
        $this->sendJson(0, 'OK', $savePath . $newFilename);
    }

    /**
     * @param int $error
     * @param string $message
     * @param string $url
     */
    protected function sendJson($error, $message = '', $url = '')
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'error' => $error,
            'message' => $message,
            'url' => $url,
        ]);
        exit;
    }

    /**
     * @return int
     */
    public function getMaxSize()
    {
        return $this->_maxSize;
    }

    /**
     * @param int $maxSize
     */
    public function setMaxSize($maxSize)
    {
        $this->_maxSize = (int)$maxSize;
    }

    /**
     * @param array|\Closure|string $subPath
     * @param string $fileName
     * @return string
     */
    private function resolveSubPath($subPath, $fileName)
    {
        $path = '';
        if ($subPath instanceof \Closure) {
            $path = call_user_func($subPath, $fileName);
        } elseif (is_array($subPath)) {
            $func = array_shift($subPath);
            $params = $subPath;
            $path = call_user_func_array($func, $params);
        } elseif (is_string($subPath)) {
            $path = $subPath;
        }
        return $path;
    }
}