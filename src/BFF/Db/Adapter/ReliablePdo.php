<?php

namespace BFF\Db\Adapter;

class ReliablePdo extends \PDO
{
    const MYSQL_HAS_GONE_AWAY = 2006;
    const MIN_CONNECTION_TIMEOUT = 120;

    protected $lastPing = 0;

    protected $dsn;
    protected $username;
    protected $passwd;
    protected $options;

    public function __construct(string $dsn, string $username, string $passwd, array $options)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->passwd = $passwd;
        $this->options = $options;
        parent::__construct($dsn, $username, $passwd, $options);
    }

    public function ping() : bool
    {
        /* Rate limit pings based on minimum expected connection timeout */
        if (time() > $this->lastPing + self::MIN_CONNECTION_TIMEOUT) {
            try {
                $this->query('SELECT 1');
                $this->lastPing = time();
            } catch (\PDOException $e) {
                if ($e->getCode() == self::MYSQL_HAS_GONE_AWAY) {
                    return false;
                } else {
                    throw $e;
                }
            }
        }

        return true;
    }

    public function connectNew() : \PDO
    {
        return new ReliablePdo($this->dsn, $this->username, $this->passwd, $this->options);
    }
}