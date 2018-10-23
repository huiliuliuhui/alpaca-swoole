<?php
/**
 * 文件名称：errorHandlerException.class.php
 * 摘 要：自定义错误异常类 该类继承至PHP内置的错误异常类
 */
class errorHandlerException extends ErrorException
{
    public static $localCode = array(
        E_COMPILE_ERROR => 4001,
        E_COMPILE_WARNING => 4002,
        E_CORE_ERROR => 4003,
        E_CORE_WARNING => 4004,
        E_DEPRECATED => 4005,
        E_ERROR => 4006,
        E_NOTICE => 4007,
        E_PARSE => 4008,
        E_RECOVERABLE_ERROR => 4009,
        E_STRICT => 4010,
        E_USER_DEPRECATED => 4011,
        E_USER_ERROR => 4012,
        E_USER_NOTICE => 4013,
        E_USER_WARNING => 4014,
        E_WARNING => 4015,
        4016 => 4016,
    );
    public static $localName = array(
        E_COMPILE_ERROR => 'PHP Compile Error',
        E_COMPILE_WARNING => 'PHP Compile Warning',
        E_CORE_ERROR => 'PHP Core Error',
        E_CORE_WARNING => 'PHP Core Warning',
        E_DEPRECATED => 'PHP Deprecated Warning',
        E_ERROR => 'PHP Fatal Error',
        E_NOTICE => 'PHP Notice',
        E_PARSE => 'PHP Parse Error',
        E_RECOVERABLE_ERROR => 'PHP Recoverable Error',
        E_STRICT => 'PHP Strict Warning',
        E_USER_DEPRECATED => 'PHP User Deprecated Warning',
        E_USER_ERROR => 'PHP User Error',
        E_USER_NOTICE => 'PHP User Notice',
        E_USER_WARNING => 'PHP User Warning',
        E_WARNING => 'PHP Warning',
        4016 => 'Customer`s Error',
    );

    /**
     * 方  法：构造函数
     * 摘  要：相关知识请查看 http://php.net/manual/en/errorexception.construct.php
     *
     * 参  数：string  $message  异常信息(可选)
     *    int   $code   异常代码(可选)
     *    int   $severity
     *    string  $filename  异常文件(可选)
     *    int   $line   异常的行数(可选)
     *   Exception $previous 上一个异常(可选)
     *
     * 返  回：void
     */
    public function __construct($message = '', $code = 0, $severity = 1, $filename = __FILE__, $line = __LINE__, Exception $previous = null)
    {
        parent::__construct($message, $code, $severity, $filename, $line, $previous);
    }

    /**
     * 方  法：是否是致命性错误
     * 参  数：array $error
     * 返  回：boolean
     */
    public static function isFatalError($error)
    {
        $fatalErrors = array(
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_CORE_WARNING,
            E_COMPILE_ERROR,
            E_COMPILE_WARNING
        );
        return isset($error['type']) && in_array($error['type'], $fatalErrors);
    }

    /**
     * 方  法：根据原始的错误代码得到本地的错误代码
     * 参  数：int $code
     * 返  回：int $localCode
     */
    public static function getLocalCode($code)
    {
        return isset(self::$localCode[$code]) ? self::$localCode[$code] : self::$localCode[4016];
    }

    /**
     * 方  法：根据原始的错误代码获取用户友好型名称
     * 参  数：int
     * 返  回：string $name
     */
    public static function getName($code)
    {
        return isset(self::$localName[$code]) ? self::$localName[$code] : self::$localName[4016];
    }
}