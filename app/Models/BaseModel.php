<?php
/**
 * Author: 刘辉
 *
 */
namespace App\Models;
use Classes\Db;
use Kernel\App;

class BaseModel
{
    protected $prefix;
    public static $transaction_count = 0;

    /**
     * @var PDO
     */
    protected $link;
    protected $error;
    protected $code; //错误码

    /**
     * @var Db\Connection
     */
    protected $connection = [];
    protected static $links = [];
    // 数据库查询对象
    protected $query;
    // 当前模型名称
    protected $name;
    // 数据表名称
    protected $table;
    // 当前类名称
    protected $class;

    protected $host;

    protected $config;

    protected $redis = null;
    protected $log = null;


    //构造函数
    public function __construct()
    {

        $configService = App::make("Config");
        $appConfig = $configService->getConfig();
        $this->redis = App::make("redis");
        $this->log = App::make("Log");

        $mysql_config = $appConfig['mysql'];
        $this->class = get_class($this);
        $model = $this->class;
        if (!isset(self::$links[$model])) {
            // 设置当前模型 确保查询返回模型对象
            $query = Db::connect($mysql_config)->model($model);
            // 设置当前数据表和模型名
            if (!empty($this->table)) {
                $query->setTable($this->table);
            } else {
                $this->class = get_class($this);
                if (empty($this->name)) {
                    // 当前模型名
                    $name = str_replace('\\', '/', $this->class);
                    $this->name = basename(str_replace("Model", "", $name));
                    $query->name(strtolower($this->name));
                } else {
                    $query->name(strtolower($this->name));
                }
            }

            if (!empty($this->pk)) {
                $query->pk($this->pk);
            }

            self::$links[$model] = $query;
        }

        // 返回当前模型的数据库查询对象
        $this->connection = self::$links[$model];
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->connection, $method], $args);
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getTableName()
    {
        if (isset($this->prefix)) {
            return $this->prefix . $this->table;
        }
        return $this->table;
    }

    /**
     * 生成唯一ID
     * @return boolean | int
     */
    public function generateUniqueId()
    {
        //改为从数据库获取自增
        try {
            $res = $this->connection->execute("INSERT INTO `smart_auto` (`__ID__`) VALUE (NULL)");
            if ($res) {
                return $this->connection->getLastInsID();
            }
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            self::setCode(-1);
        }
        return false;
    }


    public function array2object($array)
    {
        if (is_array($array)) {
            $obj = new StdClass();
            foreach ($array as $key => $val) {
                $obj->$key = $val;
            }
        } else {
            $obj = $array;
        }
        return $obj;
    }

    public function object2array($e)
    {
        $e = (array)$e;
        foreach ($e as $k => $v) {
            if (gettype($v) == 'resource') return;
            if (gettype($v) == 'object' || gettype($v) == 'array')
                $e[$k] = (array)$this->object2array($v);
        }
        return $e;
    }

    public function objectArray2array($objectArray)
    {
        $out = [];
        foreach ($objectArray as $object) {
            $object = (array)$object;
            foreach ($object as $k => $v) {
                if (gettype($v) == 'resource') return;
                if (gettype($v) == 'object' || gettype($v) == 'array')
                    $object[$k] = (array)$this->object2array($v);
            }
            $out[] = $object;
        }
        return $out;
    }

    public function setTable($table)
    {
        $this->table = $table;
    }



}
