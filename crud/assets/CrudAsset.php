<?php

namespace crud\assets;

use Yii;
use yii\web\AssetBundle;


class CrudAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . DIRECTORY_SEPARATOR . '';
    public function init()
    {
        parent::init();
        if (Yii::$app->language != 'en') {
            $i18n = 'js/i18n/json/' . Yii::$app->language . '.json'; // dynamic file added
            if (file_exists($this->sourcePath . "/" . $i18n))  $this->js[] = $i18n;
            $this->js[]="//cdn.jsdelivr.net/npm/jquery-colorbox@1.6.4/i18n/jquery.colorbox-" . Yii::$app->language .".js";
        }
    }

    public $css = [
        'css/datatables.css',
        'css/crud.datatables.css',
        'css/form.css',
        '//cdn.jsdelivr.net/npm/jquery-colorbox@1.6.4/example3/colorbox.min.css'
    ];
    public $js = [
        'js/datatables.js',
        'js/crud.datatables.js',
        '//cdn.jsdelivr.net/npm/jquery-colorbox@1.6.4/jquery.colorbox.min.js'
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
        'yii\bootstrap5\BootstrapAsset',
        'yii\bootstrap5\BootstrapPluginAsset'
    ];
    /**
     * Returns the actual URL for the specified asset.
     * @see \yii\web\AssetManager::getAssetUrl() for details on how URL is obtained.
     * @param string $asset the asset path. This should be one of the assets listed in [[js]] or [[css]].
     * @return string the actual URL for the specified asset.
     */
    public static function getAssetUrl($asset)
    {
        $view = Yii::$app->getView();
        $assetManager = $view->getAssetManager();
        $bundle = $assetManager->getBundle(static::class);
        return $assetManager->getAssetUrl($bundle, $asset);
    }
}
