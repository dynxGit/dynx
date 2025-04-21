<?php

namespace crud\controllers;

/**
 * CRUDController class file
 * @author Szincsák András <andras@szincsak.hu>
 * @copyright Copyright &copy; Szincsák András
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use crud\controllers\behaviors\DataTableBehavior;
use crud\controllers\behaviors\RenderTemplateBehavior;


use function PHPUnit\Framework\fileExists;

class CRUDController extends Controller
{
    public $pageTitle = "CRUD";

    public $closeAfterSave=false;
    public  $crudModel ;

    /**
     * Columns of datatable 
     * '*attribute-name*'=>[
     *  'header'=>'*text or html*',
     *  'filter'=>false,        or string of input type (text, number, datetime, switch(true/false)) or array of filtervalues for dropdown filter,
     *  'search'=>[             array or string Combine condition and type like "OrLike"
     *      'type'=>'like',     comparison in where (like,in,eq(=), biger(>)),less(<), ) 
     *      'condition'=>'and',     condition in where (and,or) 
     *  ],        
     *  'inputwidget'=>text,    editor (create/edit) form input widget (text,email,number,tel, textarea,HTMLeditor)
     *  'htmlValue'=»'',         readable text of attribute (status model::STATUS_ACTIVE but shown 'Active' by AR statusText method)
     *  'empty'=>'Select...',             dropdown empty text ( only filter is array)
     *  'render'=>empty,        or predefined datatables rendering templates (dtFormatMoney, dtPopupLink etc.) - check renderTemplate behavior
     *  'options'=>[
     *      'width'=>false,     no width  or col width (px or %)
     *      'align'=>left,      text align in col (left,right,center)
     *      'visible'=>true,    if false column is not rendered
     *      'orderable'=>true,    if false column is not orderable
     *  ],   
     * ]  OR simple add buttons for CRUD actions
     *  'actionGroup'           array of action buttons 'read','update','delete'
    
     * @var array
     */
    public $columns = [];

    /**
     * Actions in last column (view,edit,delete or custom)
     *
     * @var array
     */
    public $columnActions = [];


    /**
     * Layout inside editor (create, edit)
     * 'create'=>false,          only required attributes or array of attributes
     * 'left'=>false,            content of the left side 
     *  'edit'=>[
     *   [
     *     'label'=>'*html*',    Tab caption and tab content title
     *     'icon'=>'',           Fontawsome icon for tab caption ('fas fa-edit')  
     *     'blocks'=>[
     *        "class" => "col-md-6",        class of the block
     *        "content" =>"{render:info}",   content of the block - check editorBlock behavior
     *     ]    
     *   ],
     *        [... next tab structure]
     *  ],
     * 'options'=>[
     *   'editAfterCreate'=>false,  after create close popup and back to datatable or open edit view
     *  ]
     * @var array
     */
    public $editorLayout = [];

    /**
     * DataTable row css class js code  
     * for example 
     * $this->rowCssClass = "var trclass=data['status'].match(/\'text-(.*)\'/);"
     * . "if(trclass)$(row).addClass(\"table-\"+trclass[1]);";
     *
     * @var string
     */
    public $rowCssClass = "";

    /**
     * Html formmatted text (pl info panel) before Title
     * You
     * @var string
     */
    public $htmlBeforeTitle = "";

    /**
     * Html formmatted text (pl description) under Title
     * @var string
     */
    public $htmlAfterTitle = "";

    /**
     * Filename of export file
     * for example "userlist_{date}"
     * default: "export_[ActiveRecord::class]_{date}"
     * @var string
     */
    public $exportFileName = "";

    public $tableReorder = "";



    public function behaviors()
    {
        return [
            'renderTemplate' => RenderTemplateBehavior::class,
            'datatable' => DataTableBehavior::class,
        ];
    }
    public function configuration() {}
    public function init()
    {
        parent::init();

        $this->configuration();
    }


    /**
     *  Manage CRUD 
     */
    public function actionManage()
    {
        return  $this->render("@crud/views/manage", ['model' => $this->crudModel]);
    }
    /**
     * Edit Record
     */
    public function actionEdit($id)
    {
        $this->layout = 'popup';
        $model = $this->crudModel::findOne(['id' => $id]);
        if ($model) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                if($this->closeAfterSave)
                echo "<script>top.$.colorbox.close();</script>";
            }

            return  $this->render("@crud/views/edit", ['model' => $model]);
        }
    }
        /**
     * Delete Model by Id
     */
    public function actionRemove($id){
        $this->enableCsrfValidation = false;
        $result=['status'=>false];
        if(Yii::$app->request->isDelete&&!Yii::$app->user->isGuest){
        $model = $this->crudModel::findOne(['id'=>$id]);
        if($model){
            $result['status']=$model->delete();
        }
        }else
        throw new HttpException(403,"Invalid request", 403);


        return $this->asJson($result);

    }
    
    /**
     * Load data to DataTable
     */
    public function actionLoad()
    {
        $model = $this->crudModel;
        $result = "";
        $items = [];
        if ($model) {
            $fields = $this->owner->columns;
            $draw = isset($_POST['draw']) ? ($_POST['draw']) : 1;
            $filters = $order=[];
            if (isset($_POST['columns'])) {
                $cols = $_POST['columns'];
                foreach ($cols as $idx => $col) {
                    $field = isset($fields[$col['data']]) ? $fields[$col['data']] : [];
                    if (strlen($col['search']['value']) > 0) {

                        $filters[] = $this->setFilter($field, $col['data'], $col['search']['value']);
                    }
                }
                if (isset($_POST['order'])) {
                    $orders = $_POST['order'];
                    foreach ($orders as $idx => $ord) {
                       if(isset($ord['column'])&&isset($cols[$ord['column']])){
                        $columns=$cols[$ord['column']];
                       $field = isset($columns['data']) ? $columns['data'] : [];
                           $order[$field] = $ord['dir']=="asc"?SORT_ASC:SORT_DESC;
                       }
                    }
                }
            }
            $dataModel = $model::find();
            if ($filters)
                foreach ($filters as $filter) {
                    switch ($filter[0]) {
                        case "or":
                            $dataModel->orWhere($filter[1]);
                            break;
                        default:
                            $dataModel->andWhere($filter[1]);
                            break;
                    }
                }
                if ($order)
                   $dataModel->orderBy($order);

            $data = $dataModel->all();
            foreach ($data as $row) {
                $i = [];
                $i['DT_RowId'] = $row->id;
                foreach ($fields as $attr => $cfg) {
                    $attribute = (is_array($cfg)) ? $attr : $cfg;
                    preg_match('/^action(.*)/', $attribute, $action);
                    if ($action) {
                    } else {
                        $ops = $this->getColOptions($cfg);

                        if ($ops['visible']) {
                            $value = isset($cfg['htmlValue']) ? $row[$cfg['htmlValue']] : $row[$attribute];
                            $i[$attribute] = $value;
                            //   $i['text'] = $value;
                            if (is_array($cfg)) {
                                $i[$attribute] = isset($cfg['htmlValue']) ? $row[$cfg['htmlValue']] : $value;
                            }
                        }
                    }
                }
                if ($i) {
                    array_push($items, $i);
                }
                //$items[] = ($row->attributes);
            }
        }
        $json = [
            "draw" => $draw,
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $items,
       //     "columns" => $_POST['columns'],
        ];
        return json_encode($json);
    }

    /**
     * Generate filename from $exportFileName property
     * @return string
     */
    public function getExpFileName()
    {
        $filename = ($this->exportFileName) ? $this->exportFileName : "export_" . strtolower($this->crudModel) . "_{date}";
        $filename = str_replace("{date}", "" . date("Ymd-Hi"), $filename);
        return $filename;
    }
}
