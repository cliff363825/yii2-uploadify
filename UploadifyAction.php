<?php
namespace cliff363825\uploadify;

use Yii;
use yii\base\Action;

/**
 * Class UploadifyAction
 * @package cliff363825\uploadify
 * @property string $targetPath
 * @property string $targetUrl
 * @property int $maxSize
 */
class UploadifyAction extends Action
{
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
     * the file path used to save the uploaded file
     * 文件保存目录路径
     * @var string
     */
    private $_targetPath = '@webroot/uploads';
    /**
     * 文件保存目录URL
     * @var string
     */
    private $_targetUrl = '@web/uploads';
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
        $targetPath = $this->getTargetPath() . '/';
        $targetUrl = $this->getTargetUrl() . '/';

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
        $ymd = date('Ym/d');
        $targetPath .= $ymd . '/';
        $targetUrl .= $ymd . '/';
        if (!file_exists($targetPath)) {
            mkdir($targetPath, 0777, true);
        }
        $newFilename = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $fileExt;
        $targetFilePath = $targetPath . $newFilename;
        $targetFileUrl = $targetUrl . $newFilename;
        if (!move_uploaded_file($tempFile, $targetFilePath)) {
            $this->sendJson(1, 'Server error.');
        }
        $this->sendJson(0, 'OK', $targetFileUrl);
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
     * @return string
     */
    public function getTargetPath()
    {
        return rtrim(Yii::getAlias($this->_targetPath), '\\/');
    }

    /**
     * @param string $targetPath
     */
    public function setTargetPath($targetPath)
    {
        $this->_targetPath = $targetPath;
    }

    /**
     * @return string
     */
    public function getTargetUrl()
    {
        return rtrim(Yii::getAlias($this->_targetUrl), '\\/');
    }

    /**
     * @param string $targetUrl
     */
    public function setTargetUrl($targetUrl)
    {
        $this->_targetUrl = $targetUrl;
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
}