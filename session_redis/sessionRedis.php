<?php



class Redis_Session implements SessionHandlerInterface
{

    private $handle;
    private $lifetime;
    private $prefix = '101hr:';

    private static $redis = null;

    private static $sessionHandler = null;

    public static function init(){
        if(is_null(self::$sessionHandler)) {
            self::$sessionHandler = new self();
        }

        return self::$sessionHandler;
    }

    /**
     * open session
     * @param string $save_path
     * @param string $session_name
     * @return bool
     */
    public function open($save_path, $session_name)
    {

        if (is_null(self::$redis)) {
            $handle = new Redis();
            $handle->pconnect(gethostbyname(getenv('REDIS_HOST')), getenv('REDIS_PORT'));
            $handle->auth(getenv('REDIS_PASS'));

            self::$redis = $handle;
        }

        $this->handle = self::$redis;

        $this->lifetime = 28800;
        return true;
    }

    /**
     * close session
     * @return bool
     */
    public function close()
    {
        $this->gc($this->lifetime);
        $this->handle->close();
        $this->handle = null;
        return true;
    }

    /**
     * read session by session_id
     * @param string $session_id
     * @return mixed
     */
    public function read($session_id)
    {
        $session_id = $this->prefix . $session_id;
        $data = $this->handle->get($session_id);
        $this->handle->expire($session_id, $this->lifetime);
        return serialize($data);
    }

    /**
     * write session by session_id
     * @param string $session_id
     * @param string $session_data
     * @return mixed
     */
    public function write($session_id, $session_data)
    {
        $session_id = $this->prefix . $session_id;
        $this->handle->set($session_id, $session_data);
        return $this->handle->expire($session_id, $this->lifetime);
    }

    /**
     * delete session_id
     * @param string $session_id
     * @return mixed
     */
    public function destroy($session_id)
    {
        return $this->handle->rm($this->prefix . $session_id);
    }

    /**
     * this function is no use because of redis expire
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }

}


try {
    $handler = new Redis_Session();
    session_set_save_handler($handler, true);
    session_start();
    $_SESSION['tt'] = 'asdfasdfasdf';
echo $_SESSION['tt'];
} catch (Exception $e){
    file_put_contents('aaaa.txt', "Exception: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
}










