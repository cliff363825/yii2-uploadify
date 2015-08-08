<?php
namespace cliff363825\uploadify;

use yii\web\AssetBundle;

/**
 * Class UploadifyAsset
 * @package cliff363825\uploadify
 */
class UploadifyAsset extends AssetBundle
{
    public $sourcePath = '@cliff363825/uploadify/assets';
    public $js = [];
    public $css = [
        'uploadify.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (YII_DEBUG) {
            $this->js[] = 'jquery.uploadify.js';
        } else {
            $this->js[] = 'jquery.uploadify.min.js';
        }
    }
}