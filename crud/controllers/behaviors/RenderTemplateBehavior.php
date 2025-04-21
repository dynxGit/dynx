<?php

namespace crud\controllers\behaviors;

/**
 * CRUDController class file
 * @author Szincsák András <andras@szincsak.hu>
 * @copyright Copyright &copy; Szincsák András
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

use Yii;
use yii\base\Behavior;
use yii\helpers\Json;


class RenderTemplateBehavior extends Behavior
{
    public function getCrudTextJson(){
      return  Json::encode([
        'buttons'=>[
            'confirm'=>"OK",
            'cancel'=>Yii::t("dynx/views","Cancel")
        ],
        'delete'=>[
            'title'=>Yii::t("dynx/views","Delete Row"),
            'html'=>Yii::t("dynx/views","Do you really want to delete this item?"),
            "icon"=>'question'
        ]
        ]);
    }
    /**
     * Format Money in datatable column if not in export !
     * @param string $currency
     * @return void
     */
    public function dtFormatMoney($currency = " Ft")
    {
        $script = <<<JS
        function ( data, type, row, meta ) {
            if(type=='export')return data;
            var display = $.fn.dataTable.render.number( ' ', ',',0,''," {$currency}" ).display;
            return '<small class="money">'+display( data )+'</small>';
            }
        JS;
        return $this->escaped($script);
    }


    /**
     * Boolean as Fontawsome icon (Check/Ban) if not in  export !
     * @return void
     */
    public function dtBooleanToIcon()
    {
        $script = <<<JS
        function ( data, type, row, meta ) {
            if(type=='export')return data;
            var display = data==1?'check text-success':'ban text-danger';
            return '<i data-value=\"'+data+'\" class=\"fas fa-'+display+'\"></i>';
        }
        JS;
        return $this->escaped($script);
    }
    /**
     * Add data class to column if not in  export !
     * @return void
     */
    public function dtDateBlock()
    {
        $script = <<<JS
            function ( data, type, row, meta ) {
            if(type=='export')return data;
            return data?'<small class=\"date\">'+data+'</small>':'-';}

        JS;
        return $this->escaped($script);
    }
    /**
     * Add Popup link related to this controller and action from $link
     * @return void
     */
    public function dtPopupLink($link="edit")
    {
        $model=end(explode("\\",strtolower($this->owner->crudModel)));
        $url="/".$this->owner->uniqueId ."/".$link;
        $script = <<<JS
        function ( data, type, row, meta ) {return '<a class="crudpopup"  rel="{$model}"  href="{$url}?id='+row['DT_RowId']+'">'+data+'</a>';}
        JS;
        return $this->escaped($script);
    }

    public function escaped($string){
        return new \yii\web\JsExpression(preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $string));
       
    }
}
