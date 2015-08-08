Uploadify Widget For Yii2
=========================
Uploadify Widget For Yii2

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist cliff363825/yii2-uploadify "*"
```

or add

```
"cliff363825/yii2-uploadify": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \cliff363825\uploadify\UploadifyWidget::widget([
    'clientOptions' => [
        'uploader' => Url::to(['uploadify']),
        'multi' => false,
        'onUploadSuccess' => new JsExpression("
function (file, data, response) {
    data = $.parseJSON(data);
    if (data.error == 0) {
        var url = data.url;
        $('ul.image_list').html('<li><img src=\"' + url + '\" alt=\"\" class=\"img-thumbnail\"></li>');
        $('input[name=\"...\"]').val(url);
    } else {
        alert(data.message);
    }
}"
        ),
    ],
]); ?>
```