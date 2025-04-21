<?php

namespace crud\controllers\behaviors;

/**
 * DataTableBehavior class file
 * @author Szincsák András <andras@szincsak.hu>
 * @copyright Copyright &copy; Szincsák András
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

use crud\assets\CrudAsset;
use Yii;
use yii\base\Behavior;
use yii\web\JsExpression;
use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;

class DataTableBehavior extends Behavior
{
    public function getColOptions($cfg)
    {
        $colOptions = [
            'width' => false,
            'align' => 'left',
            'visible' => true,
            'orderable' => true,
        ];
        return isset($cfg['options']) ? ArrayHelper::merge($colOptions, $cfg['options']) : $colOptions;
    }

    public function generateTemplateTable()
    {
        $crud = $this->owner;
        $result = $filters = "";
        $idx = 0;

        foreach ($crud->columns as $attr => $cfg) {
            $filter = "";
            $attribute = (is_array($cfg)) ? $attr : $cfg;
            preg_match('/^action(.*)/', $attribute, $action);
            if ($action) {
                $result .= "<th title='$action[1]' class='icon' data-width='20'></th>\n";
            } else {
                $ops = $this->getColOptions($cfg);
                if ($ops['visible']) {
                    $title = isset($cfg['header']) ? $cfg['header'] : $attribute;
                    $headData = "data-column='$attribute'";
                    $headData .= $ops['width'] ? "width='$ops[width]' data-width='" . $ops['width'] . "'" : "";
                    $headData .= $ops['align'] ? "data-class='text-" . $ops['align'] . "'" : "";
                    $result .= "<th $headData >$title</th>\n";

                    //Set Filters
                    if (isset($cfg['filter'])) {
                        $htmlOptions = [
                            'class' => 'DTfilter',
                            'data-idx' => $idx,
                        ];
                        if (is_array($cfg['filter'])) {
                            $htmlOptions['prompt'] = isset($cfg['empty']) ? $cfg['empty'] : "Select...";
                            $filter = Html::dropDownList("filter_$idx", "", $cfg['filter'], $htmlOptions);
                        } else {
                            switch ($cfg['filter']) {
                                case "number":
                                case "email":
                                case "tel":
                                    $htmlOptions['type'] = $cfg['filter'];
                                    $filter = Html::textInput("filter_$idx", "", $htmlOptions);
                                    break;

                                case "YesNo":
                                    $filter_values = [
                                        0 => Yii::t('common', 'No'),
                                        1 => Yii::t('common', 'Yes')
                                    ];
                                    $htmlOptions['empty'] = '';
                                    $filter = Html::dropDownList("filter_$idx", "", $filter_values, $htmlOptions);
                                    break;

                                default:;
                                    $filter = Html::textInput("filter_$idx", "", $htmlOptions);
                                    break;
                            }
                        }
                    }
                }
            }
            $filters .= "<td >$filter</td>\n";
            $idx++;
        }
        return "<thead><tr>\n$result</tr>\n</thead><tbody></tbody><tfoot><tr>$filters</tr></tfoot>";
    }


    /**
     * Datatable JS column generation
     * @return array columns
     */
    public function generateJsColumns()
    {
        $crud = $this->owner;
        $columns = [];
        $idx = 0;
        foreach ($crud->columns as $attr => $cfg) {
            $ops = $this->getColOptions($cfg);
            $visible =  $ops['visible'];
            if ($visible) {
                $attribute = (is_array($cfg)) ? $attr : $cfg;
                if ($attribute == "actionGroup") {
                    $actionTemplate = "<span title ='{action}' class='row_{action}'><i class='{actionIcon}'/></i></span>";
                    $actions = "";
                    foreach ($cfg as $action) {
                        switch ($action) {
                            case "read":
                                $actions .= str_replace(["{action}", "{actionIcon}"], [$action, "fas fa-eye"], $actionTemplate);
                                break;
                            case "update":
                                $actions .= str_replace(["{action}", "{actionIcon}"], [$action, "fas fa-edit"], $actionTemplate);
                                break;
                            case "delete":
                                $actions .= str_replace(["{action}", "{actionIcon}"], [$action, "fas fa-trash"], $actionTemplate);
                                break;
                        }
                    }
                    $columns[] = [
                        "data" => null,
                        "width" => (count($cfg)*15)."px",
                        "type" => "icon",
                        "class" => "icon",
                        "defaultContent" =>  $actions,
                        "orderable" => false,
                        "searchable" => false
                    ];
     
                } else {
                    $column = ['data' => $attribute];
                    $column['searchable'] = (isset($cfg['filter']) && $cfg['filter'] !== false) ? 1 : 0;
                    $column['visible'] =  $visible;
                    $column['orderable'] = $ops['orderable'];
                    if (($ops['width']))
                        $column['width'] = $ops['width'];
                    /* type */
                    if (isset($items['type']))
                        $column['type'] = $items['type'];

                    /** RENDER * */
                    if (isset($cfg['render']))
                        $column['render'] = $cfg['render'];
                    if ($crud->tableReorder == $attribute) {
                        $column['className'] = "reOrder";
                        $options['responsive'] = false;
                    }
                    $columns[] = $column;
                    $idx++;
                }
            }
        }
        return $columns;
    }

    function dataTableOptions()
    {
        $crud = $this->owner;
        $options = [
            'processing' => true,
            'serverSide' => true,
            'colReorder' => true,
            'stateSave' => true,
            "stateDuration" => -1,
            "dom" => 'Birtpli',
            "pageLength" => 300,
            'paging' => false,
            'scroller' => false,
            'responsive' => true,
            "orderMulti" => true,
            'deferRender' => true,
            "autoWidth" => false,
            'scrollY' => '100%',
            'scrollCollapse' => true,
            // 'searchBuilder'=> true,
            // 'fixedColumns'=> true,
            // 'fixedHeader'=> true,
            'ajax' => [
                'url' => "/" . $crud->uniqueId . "/load",
                'type' => 'POST',
            ],
            "language" => [
                "url" => CrudAsset::getAssetUrl('js/i18n/json/' . Yii::$app->language . '.json')
            ],
            'buttons' => [
                [
                    'extend' => 'colvis',
                    'text' => "<i class='fa fa-cog'></i>"
                ],
                [
                    'extend' => 'copy',
                    'text' => "<i class='far fa-copy' ></i>",
                    'exportOptions' => [
                        'columns' => ':visible',
                        'orthogonal' => 'export'
                    ]
                ],
                [
                    'extend' => 'pdfHtml5',
                    'text' => "<i class='far fa-file-pdf' ></i>"
                ],
                [
                    'extend' => 'excelHtml5',
                    'exportOptions' => [
                        'columns' => ':visible',
                        'orthogonal' => 'export'
                    ],
                    'text' => "<i class='fas fa-file-download'></i> Export",
                    'filename' => $crud->ExpFileName
                ],
            ],
            "stateLoaded" => new JsExpression(
                "function ( settings, data) {"
                    . " $.each(data.columns,function(idx,el){"
                    . " if(el.search.search){"
                    . " $('#filter__'+idx).val(el.search.search);"
                    . " }"
                    . " });"
                    . " }"
            )
        ];
        /* Reorder */
        if ($crud->tableReorder) {
            $options['rowReorder'] = ['dataSrc' => $crud->tableReorder];
        }
        if ($crud->rowCssClass)
            $options['createdRow'] = new JsExpression("function( row, data, dataIndex){" . $crud->rowCssClass . "}");

        $options['columns'] = $this->generateJsColumns();
        return $options;
    }
    /**
     * Set filter by field search params
     *
     * @param array $field
     * @param string $attribute
     * @param string $value
     * @return array
     */
    public static function setFilter($field, $attribute, $value)
    {
        $filter = [
            'type' => "like",
            'condition' => "and"
        ];
        if (is_array($field)) {
            $filter = [
                'type' => (isset($field['search']) && isset($field['search']['type'])) ? $field['search']['type'] : "like",
                'condition' => (isset($field['search']) && isset($field['search']['condition'])) ? $field['search']['condition'] : "and"
            ];
        } else {
            $code = preg_split("/^(Or|And)(.*)/gm", $field);
            $filter['condition'] = $code[0];
            $filter['type'] = $code[1];
        }

        switch ($filter['type']) {
            case "eq":
                $filter['type'] = "=";
                break;
            case "bigger":
                $filter['type'] = ">";
                break;
            case "less":
                $filter['type'] = "<";
                break;
        }
        return [$filter['condition'], [$filter['type'], $attribute, $value]];
    }
}
