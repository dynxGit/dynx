<?php

namespace crud\views;

/** -----------------------------------------------------------------------------------------------------
 * manage
 * 
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * I18n : http://cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Hungarian.json
 * @copyright Copyright &copy; Szincsák András
 * @author Szincsák András <andras@szincsak.hu>
 * @version 1.0
 * ------------------------------------------------------------------------------------------------------ */


use Yii;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use dynx\widgets\DynxTabs;
use crud\assets\CrudAsset;

$crud = $this->context;
$config = $crud->editorLayout;
CrudAsset::register($this);

?>
<h1 class='popuptitle'><?= Yii::t('dynx/views', "Edit {class-name}",['class-name'=> $crud->pageTitle]) ?> - <small><?= $model->name; ?></small></h1>
<div class='submitRight'>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'layout'=>'floating',
        'options' => ['autocomplete' => 'off'],
        'fieldConfig' => [
            //    'template' => "\n{label}\n{input}\n{error}",
            'template' => "\n{input}\n{label}\n{error}",
            'labelOptions' => ['class' => 'col-lg-4 col-form-label mr-lg-3'],
            'inputOptions' => ['class' => 'col-lg-3 form-control form-control-lg', 'autocomplete' => 'list '],
            'errorOptions' => ['class' => 'col-lg-7 invalid-feedback'],
        ],
    ]);
    $tabCol = isset($config['left']) ? 8 : 12;
    ?>
        <div class='float-end'>
            <button type='submit' class='btn btn-primary btn-block'><i class="fas fa-save"></i> Mentés</button>
        </div>
        <div class='row'>
            <div class='col-md-<?= $tabCol; ?>'>
                <?php
        $tabs = [];
        foreach ($config['edit'] as $idx => $tab) {
            $content = "";
            $useRow = true;
            
            if (isset($tab['blocks'])) {
                foreach ($tab['blocks'] as $block) {
                    if (isset($block['content'])) {
                        $blockClass = isset($block['class']) ? $block['class'] : "col-md-12";
                        $blockContent = $block['content'];
                        
                        $pattern = "/\{(.*?)\}/";
                        preg_match_all($pattern, $blockContent, $matches);
                        if (isset($matches[1]))
                        foreach ($matches[1] as $idx => $comm) {
                    $command = explode(":", $comm);
                    $attribute = isset($command[1]) ? $command[1] : $command[0];
                    if (isset($command[1])) {
                        switch ($command[0]) {
                            case "render":
                                //  $userRow = false;
                                $rendered = $this->render($command[1], ['model' => $model, 'form' => $form], true);
                                $blockContent = str_replace($matches[0][$idx], $rendered, $blockContent);
                                break;
                                case "method":
                                    $blockContent =   str_replace($matches[0][$idx], $model->{$command[1]}, $blockContent);
                                    
                                    break;
                                }
                            } else
                            $blockContent =  str_replace($matches[0][$idx], setField($form, $crud->columns, $model, $attribute), $blockContent);
                        }
                        $content .= "<div class='$blockClass'>\n    $blockContent</div>\n";
                    }
                }
            }
            $tabs[] = [
                'label' => $tab['label'],
                'content' =>  ($useRow) ? Html::tag("div", $content, ['class' => 'row']) : $content,
            ];
        }
        if (count($tab) == 1 && !isset($config['tab_alone']))
        echo ($tab[0]['content']);
    else {
        //   echo "<pre>". print_R($tabs,true)."</pre>";
        $tabID=strtolower(end(explode("\\",$crud->crudModel)));
        echo DynxTabs::widget( [
            'navType'=>'nav-pills',
            'id'=>"tab_$tabID"."_edit",
            'items'=>$tabs ]);
        }
        
        ?>
    </div>
    <div class='col-md-<?= 12 - $tabCol; ?>'>
        
        <?= isset($config['leftContent']) ? "<div class='result'>" . decodeContent($model, $config['leftContent']) . "</div>" : ""; ?>
        <? // $form->errorSummary($model); 
        ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
</div>

<?php

function decodeContent($model, $content)
{
    $pattern = "/\{(.*?)\}/";
    preg_match_all($pattern, $content, $matches);
    if (isset($matches[1]))
        foreach ($matches[1] as $idx => $fields) {
            $field = explode(":", $fields);
            if (isset($field[1]) && $field[0] == "model") {
                $content = str_replace($matches[0][$idx], $model->{$field[1]}, $content);
            }
        }
    return $content;
}

function setField($form, $fields, $model, $attr)
{
    $res = "";
    if (isset($fields[$attr])) {
        $field = $fields[$attr];
        $type = isset($field['editor']) ? $field['editor'] : "text";
        $format = isset($field['format']) ? $field['format'] : "html";
        $data = isset($field['filter']) ? $field['filter'] : [];
        $htmlOptions = isset($field['htmlOptions']) ? $field['htmlOptions'] : [];
        switch ($type) {
            case "email":
                $res = $form->field($model, $attr)->textInput(['type' => 'email']);
                break;

            case "select":
                $htmlOptions['prompt'] = Yii::t('dynx/form', "Choose item...");
                $res = $form->field($model, $attr)->dropDownList($data,$htmlOptions);
                break;

            case "html":
                $res = $form->fieldHtml($model, $attr, $htmlOptions);
                break;
            case "json":
                $res = $form->fieldJson($model, $attr, $htmlOptions);
            case "jsonMulti":
                $res = Yii::$app->controller()->widget("ext.helpers.DyJsonMultiEditor", ['mode' => 'edit', 'model' => $model, "attribute" => $attr]);
                $res = $form->fieldJson($model, $attr, $htmlOptions);
                break;

            case "switch":
                $res = $form->fieldSwitch($model, $attr, $htmlOptions);
                break;
            case "textarea":
                $res = $form->fieldTextArea($model, $attr, $htmlOptions);
                break;
            case "code":
                $res = $form->fieldCode($model, $attr, $format, $htmlOptions);
                break;
            case "number":
                $res = $form->fieldNum($model, $attr, $htmlOptions);
                break;
            case "money":
                $res = $form->fieldNum($model, $attr, $htmlOptions);
                break;

            default:
                $res = $form->field($model, $attr, $htmlOptions);
                break;
        }
    }
    return $res;
}
?>