<?php

namespace app\admin\controller\import;

use app\common\controller\Backend;
use think\Config;
use think\Db;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
use fast\Pinyin;

/**
 * 数据导入辅助
 *
 * @icon fa fa-circle-o
 */
class Log extends Backend
{

    /**
     * Log模型对象
     * @var \app\admin\model\import\Log
     */
    protected $model = null;
    /**
     * 是否开启数据限制
     * 支持auth/personal
     * 表示按权限判断/仅限个人
     * 默认为禁用,若启用请务必保证表中存在admin_id字段
     */
    protected $dataLimit = true;
    /**
     * 数据限制字段
     */
    protected $dataLimitField = 'admin_id';
    /**
     * 是否开启Validate验证
     */
    protected $modelValidate = true;
    /**
     * 数据限制开启时自动填充限制字段值
     */
    protected $dataLimitFieldAutoFill = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\import\Log;
        $this->view->assign("statusList", $this->model->getStatusList());
        $config = get_addon_config('import');
        $exclude= explode("\n",$config['exclude']);
        foreach ($exclude as $key => $val) {
            $exclude[$key]=trim($val);
        }
        $tableList = array('' => '请选择');
        $list = \think\Db::query("SHOW TABLES");
        foreach ($list as $key => $row) {
            if(!in_array(reset($row),$exclude)){
                $table = \think\Db::query("show table status like '" . reset($row) . "'");
                $tableList[reset($row)] = reset($row) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $table[0]['Comment'];
            }
        }
        $this->view->assign("hidden_num", $this->model->where('status', 'hidden')->count());
        $this->view->assign("tableList", $tableList);
        $this->view->assign("from", $this->request->request('from'));
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $step = $params['step'];
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    //新建表导入处理
                    if ($params['newtable']) {
                        $prefix = Config::get('database.prefix');
                        $table = $prefix . $params['newtable'];
                        $check = db()->query("SHOW TABLES LIKE '%{$table}%';");
                        if ($check) {
                            $this->error(__($params['newtable'] . '表已经存在'));
                        }
                    }
                    else {
                        if (!$params['table'])   $this->error('未选择目标表');
                    }

                    $fileData = $this->fileData($params);

                    $fileData['params'] = http_build_query($params);
                    $fileData['newtable'] = $params['newtable'];
                    if (!$step) {
                        $this->success('匹配到' . $fileData['count'] . '列，开始预览', '', $fileData);
                    }
                    $insert = $fileData['insert'];
                    $fieldArr = $fileData['fieldArr'];
                    //	dump($insert);
                    //是否包含admin_id字段
                    $has_admin_id = false;
                    foreach ($fieldArr as $name => $key) {
                        if ($key == 'admin_id') {
                            $has_admin_id = true;
                            break;
                        }
                    }
                    if ($has_admin_id) {
                        foreach ($insert as &$val) {
                            if (!isset($val['admin_id']) || empty($val['admin_id'])) {
                                $val['admin_id'] = $this->auth->isLogin() ? $this->auth->id : 0;
                            }
                        }
                    }
                    $prefix = Config::get('database.prefix');
                    $res = Db::name(str_replace($prefix, "", $params['table']))->insertAll($insert);
                    if ($res) {
                        $params['status'] = 'normal';
                        $result = $this->model->allowField(true)->save($params);
                    }
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success('导入成功' . count($insert) . '条记录', '', array('count' => count($insert)));
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }

            $this->error(__('Parameter %s can not be empty', ''));
        }
        // $upload=Config::get('upload');
        // $upload['uploadurl'] ='ajax/upload2';
        // $this->assignconfig("upload", $upload);
        return $this->view->fetch();
    }


    /**
     * 编辑
     *
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    $params['newtable'] = '';
                    $fileData = $this->fileData($params);
                    $fileData['params'] = http_build_query($params);
                    $this->success('匹配到' . $fileData['count'] . '列，开始预览', '', $fileData);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success('设置成功', url('doimport', ['ids' => $row['id']]));
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $row['params'] = http_build_query(array(
            'table' => $row['table'],
            'row' => $row['row'],
            'head_type' => $row['head_type'],
            'path' => $row['path'],
        ));

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    public function preview()
    {
        $params = $this->request->param();
        if (!isset($params["path"])) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isAjax()) {
            $fileData = $this->fileData($params);

            if (isset($params["columns"])) {
                return json(array("code" => 1, "data" => $fileData['field']));
            }
            return json($fileData['data']);
        }
    }


    protected function fileData($params)
    {
        $file = $params['path'];
        $row = $params['row'];
        $sheet = 0;
        if (!$file) {
            $this->error(__('Parameter %s can not be empty', 'file'));
        }
        $filePath = ROOT_PATH . DS . 'public' . DS . $file;
        if (!is_file($filePath)) {
            $this->error(__('No results were found'));
        }
        //实例化reader
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            $this->error(__('Unknown data format'));
        }
        if ($ext === 'csv') {
            $file = fopen($filePath, 'r');
            $filePath = tempnam(sys_get_temp_dir(), 'import_csv');
            $fp = fopen($filePath, "w");
            $n = 0;
            while ($line = fgets($file)) {
                $line = rtrim($line, "\n\r\0");
                $encoding = mb_detect_encoding($line, ['utf-8', 'gbk', 'latin1', 'big5']);
                if ($encoding != 'utf-8') {
                    $line = mb_convert_encoding($line, 'utf-8', $encoding);
                }
                if ($n == 0 || preg_match('/^".*"$/', $line)) {
                    fwrite($fp, $line . "\n");
                } else {
                    fwrite($fp, '"' . str_replace(['"', ','], ['""', '","'], $line) . "\"\n");
                }
                $n++;
            }
            fclose($file) || fclose($fp);

            $reader = new Csv();
        } elseif ($ext === 'xls') {
            $reader = new Xls();
        } else {
            $reader = new Xlsx();
        }

        $prefix = Config::get('database.prefix');
        //导入文件首行类型,默认是注释,如果需要使用字段名称请使用name
        $importHeadType = $params['head_type'];
        $table = $params['table'];
        $database = \think\Config::get('database.database');
        $fieldArr = [];
        $notnull = [];

        if (!$params['newtable']) {
            $pk = Db::getTableInfo($table, 'pk');
            $list = db()->query("SELECT COLUMN_NAME,COLUMN_COMMENT,COLUMN_TYPE,IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?",
                [$table, $database]);
            foreach ($list as $k => $v) {
                if ($v['COLUMN_NAME'] !== $pk) {
                    if ($importHeadType == 'comment') {
                        if ($v['COLUMN_COMMENT']) {
                            $importField[] = $v['COLUMN_COMMENT'];
                            if ($v['IS_NULLABLE']) {
                                $notnull[] = $v['COLUMN_COMMENT'];
                            }
                        }
                        $fieldArr[$v['COLUMN_COMMENT']] = $v;//['COLUMN_NAME']
                    } else {
                        $importField[] = $v['COLUMN_NAME'];
                        if ($v['IS_NULLABLE']) {
                            $notnull[] = $v['COLUMN_NAME'];
                        }
                        $fieldArr[$v['COLUMN_NAME']] = $v;//['COLUMN_NAME']
                    }
                }
            }
        }


        $insert = [];
        $allData = [];
        $count = 0;
        try {
            if (!$PHPExcel = $reader->load($filePath)) {
                $this->error(__('Unknown data format'));
            }
            $currentSheet = $PHPExcel->getSheet($sheet);  //读取文件中的第一个工作表
            $allColumn = $currentSheet->getHighestDataColumn(); //取得最大的列号
            $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
            $maxColumnNumber = Coordinate::columnIndexFromString($allColumn);
            $fields = [];

            for ($currentRow = 1; $currentRow <= 1; $currentRow++) {
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $fields[] = $val;
                    $col[] = array(
                        'title' => $val,
                        'class' => isset($fieldArr[$val]) ? 'success' : '-',
                        'type' => isset($fieldArr[$val]) ? $fieldArr[$val]['COLUMN_TYPE'] : '--',
                        'field' => $val,
                        'fieldName' => isset($fieldArr[$val]) ? $fieldArr[$val]['COLUMN_NAME'] : '--'//Pinyin::get($val),
                    );
                    if (isset($fieldArr[$val])) {
                        $count += 1;
                    }
                }
            }
            for ($currentRow = $row; $currentRow <= $allRow; $currentRow++) {
                $values = [];
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $values[] = is_null($val) ? '' : $val;
                }
                $rows = [];
                $all = [];
                $temp = array_combine($fields, $values);

                foreach ($temp as $k => $v) {
                    if (isset($fieldArr[$k]) && $k !== '') {
                        $rows[$fieldArr[$k]['COLUMN_NAME']] = $v;
                    }
                    $all[$k] = $v;
                }

                if ($rows) {
                    $insert[] = $rows;
                }
                $allData[] = $all;
            }
            //dump($fieldArr); .
            /* ["管理员ID"] =&gt; array(4) {
                ["COLUMN_NAME"] =&gt; string(8) "admin_id"
                ["COLUMN_COMMENT"] =&gt; string(11) "管理员ID"
                ["COLUMN_TYPE"] =&gt; string(16) "int(10) unsigned"
                ["IS_NULLABLE"] =&gt; string(2) "NO"
              }*/
            return array(
                'field' => $col,
                'fieldArr' => $fieldArr,
                'data' => $allData,
                'insert' => $insert,
                'excelField' => $fields,
                'count' => $count
            );

        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}
