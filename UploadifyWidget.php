<?php
namespace cliff363825\uploadify;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * Class UploadifyWidget
 * @package cliff363825\uploadify
 */
class UploadifyWidget extends InputWidget
{
    /**
     * The name of this widget.
     */
    const PLUGIN_NAME = 'Uploadify';
    /**
     * @var string
     */
    public $uniqueSalt = '';
    /**
     * @var array the Uploadify plugin options.
     * @see http://www.uploadify.com/documentation/
     */
    public $clientOptions = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerClientScript();
        if ($this->hasModel()) {
            echo Html::activeHiddenInput($this->model, $this->attribute, ['id' => null])
                . Html::activeInput('file', $this->model, $this->attribute, $this->options);
        } else {
            echo Html::hiddenInput($this->name, $this->value, ['id' => null])
                . Html::input('file', $this->name, $this->value, $this->options);
        }
    }

    /**
     * Registers the needed client script and options.
     */
    public function registerClientScript()
    {
        $view = $this->getView();
        $this->initClientOptions();
        $asset = UploadifyAsset::register($view);
        if (empty($this->clientOptions['swf'])) {
            $this->clientOptions['swf'] = $asset->baseUrl . '/uploadify.swf';
        }
        $id = $this->options['id'];
        $js = "
(function($){
    $('#{$id}').uploadify(" . Json::encode($this->clientOptions) . ");
})(jQuery);
";
        $view->registerJs($js);
    }

    /**
     * Initializes client options
     */
    protected function initClientOptions()
    {
        $options = array_merge($this->defaultOptions(), $this->clientOptions);
        // $_POST['_csrf'] = ...
        $options['formData'][Yii::$app->request->csrfParam] = Yii::$app->request->getCsrfToken();
        // $_POST['PHPSESSID'] = ...
        $options['formData'][Yii::$app->session->name] = Yii::$app->session->id;
        $this->clientOptions = $options;
    }

    /**
     * Default client options
     * @return array
     */
    protected function defaultOptions()
    {
        $timestamp = time();
        return [
            'formData' => [
                'timestamp' => $timestamp,
                'token' => md5($this->uniqueSalt . $timestamp),
            ],
        ];
    }
}