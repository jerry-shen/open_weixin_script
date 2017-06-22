<?php 
/**
 * @author hailong.huang@eub-inc.com
 * @version 2017-03-08
 * @name mp 平台数据拉取脚本模型基础类
 */
namespace Util;
use Medoo\Medoo;

class Model extends Medoo
{
    /**
     * 表字段名称 
     *
     * @var mixed
     */
    protected $tableFields = [];

    /**
     * 格式化之后的数据 
     *
     * @var mixed
     */
    protected $formatData = [];

    /**
     * 记录条数 
     *
     * @var int
     */
    protected $batchMaxRecordCount = 0;

	/**
	 * medoo insert 操作扩展
	 * @todo   针对 medoo insert 方法增加 `ON DUPLICATE KEY UPDATE` 扩展
	 * @param  [type] $table [表名]
	 * @param  [type] $datas [需要插入的数据]
	 * @return [type]        [description]
	 */
    public function insertOrUpdate($table, $datas)
    {

		$stack = [];
		$columns = [];
		$fields = [];

		if (!isset($datas[ 0 ]))
		{
			$datas = [$datas];
		}

		foreach ($datas as $data)
		{
			foreach ($data as $key => $value)
			{
				$columns[] = $key;
			}
		}

		$columns = array_unique($columns);

		foreach ($datas as $data)
		{
			$values = [];

			foreach ($columns as $key)
			{
				if (!isset($data[$key]))
				{
					$values[] = 'NULL';
				}
				else
				{
					$value = $data[$key];

					switch (gettype($value))
					{
						case 'NULL':
							$values[] = 'NULL';
							break;

						case 'array':
							preg_match("/\(JSON\)\s*([\w]+)/i", $key, $column_match);

							$values[] = isset($column_match[ 0 ]) ?
								$this->quote(json_encode($value)) :
								$this->quote(serialize($value));
							break;

						case 'boolean':
							$values[] = ($value ? '1' : '0');
							break;

						case 'integer':
						case 'double':
						case 'string':
							$values[] = $this->fnQuote($key, $value);
							break;
					}
				}
			}

			$stack[] = '(' . implode($values, ', ') . ')';
		}

		foreach ($columns as $key)
		{
			$fields[] = $this->columnQuote(preg_replace("/^(\(JSON\)\s*|#)/i", "", $key));
		}

		$updates = array();

		foreach ($fields as $key => $value)
		{
			$updates[] = "{$value}=VALUES({$value})";
		}

		$this->sql = 'INSERT INTO ' . $this->tableQuote($table) . ' (' . implode(', ', $fields) . ') VALUES ' . implode(', ', $stack) . ' ON DUPLICATE KEY UPDATE ' . implode(',', $updates);

		return $this->exec($this->sql);

	}

	/**
	 * 获取插入数据库的字增长ID 
	 * @author Jerry Shen <haifei.shen@eub-inc.com>
	 * @version 2017-06-21
	 *
	 * @return void
	 */
    public function lastInsertId ()
    {

		return $this->query('select last_insert_id()')->fetchAll()[0][0];

	}

    /**
     * 批量插入数据  
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-21
     *
     * @return void
     */
    public function batchInsert($table = '')
    {
        $tableName = empty($table) ? $this->tableName : $table;

        return $this->insertOrUpdate($tableName, $this->formatData);    
    }

    /**
     * 格式化数据 
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-21
     *
     * @param mixed $data
     * @return void
     */
    public function format($data)
    {
        unset($data['encrypt']);

        $data['extra'] = json_encode($data);

        array_push($this->formatData, $this->arrangeFields($data));

        $this->batchMaxRecordCount++;
    }

    /**
     * 重新计数  
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-21
     *
     * @return void
     */
    public function reset()
    {
        $this->batchMaxRecordCount = 0;
        $this->formatData = [];
    }

    /**
     * 获取当前数据条数 
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-06-21
     *
     * @return void
     */
    public function getRecordCount()
    {
        return $this->batchMaxRecordCount; 
    }

	/**
	 * 字段映射 
	 * @author Jerry Shen <haifei.shen@eub-inc.com>
	 * @version 2017-06-21
	 *
	 * @param mixed $rows
	 * @return void
	 */
    protected function arrangeFields($row, $fields = [])
    {

        $fields = empty($fields) ? $this->tableFields : $fields;

		$data = [];

		foreach ($fields as $k => $v) {

            if (!isset($row[$k])) continue;

			if ($v == 'create_time') {

				$data[$v] = date('Y-m-d H:i:s', $row[$k]);

			} elseif ($v == 'copyright_check_result') {

				$data[$v] = json_encode($row[$k]);

			} else {

				$data[$v] = empty($row[$k]) ? '' : $row[$k];

			}

		}

		return $data;
	}
}
