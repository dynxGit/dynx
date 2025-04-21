<?php

namespace crud\views;

/** -----------------------------------------------------------------------------------------------------
 * CRUD manage
 * 
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @copyright Copyright &copy; Szincsák András
 * @author Szincsák András <andras@szincsak.hu>
 * @version 1.0
 * ------------------------------------------------------------------------------------------------------ */


use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use crud\assets\CrudAsset;

CrudAsset::register($this);

$crud = $this->context;
$modelName = end(explode("\\", $crud->crudModel));

$this->title = Yii::t('dynx/views', 'Manage '. $modelName);
$right = "$modelName.Edit";
$root = Yii::$app->controller->uniqueId;

?>
<?= $crud->htmlBeforeTitle; ?>
<?php if (Yii::$app->user->can($right)) { ?>
    <a href="<?= Url::base(); ?>/create" class='btn btn-primary float-end crudCreate'><i class="fa fa-plus"></i> <?= Yii::t('dynx/views', 'Create ' . $modelName); ?></a>
<?php } ?>
<h1><?= $crud->pageTitle; ?></h1>
<?= $crud->htmlAfterTitle; ?>
<div id="DTscript"></div>
<table id="DT" style="width:100%" class="table table-hover display  compact cell-border order-column" data-root="<?= $root ?>">
    <?= $crud->generateTemplateTable() ?>
</table>

<script>
    let DTconfig = <?= Json::encode($crud->dataTableOptions($crud), JSON_OBJECT_AS_ARRAY); ?>;
    let DTcrudText = <?= $crud->crudTextJson ?>;
</script>

<?php




?>
