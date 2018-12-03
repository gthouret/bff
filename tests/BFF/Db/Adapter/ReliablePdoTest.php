<?php

namespace BFF\Test\Db\Adapter;

use BFF\Db\Adapter\ReliablePdo;
use BFF\Db\PdoDsn;
use BFF\Services;
use BFF\Test\TestCase;
use PDO;

class ReliablePdoTest extends TestCase
{
    public function testConstructPingAndConnectNew()
    {
        $dbConfig = Services::config()->get('db');
        $dsn = PdoDsn::fromConfig($dbConfig);

        $options[PDO::ATTR_PERSISTENT] = false;
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_SILENT;
        $options[PDO::ATTR_EMULATE_PREPARES] = false;

        $pdo = new ReliablePdo($dsn, $dbConfig['user'], $dbConfig['pass'], $options);

        $this->assertTrue($pdo->ping());

        $query = "SELECT CONNECTION_ID() AS id";
        $stmt = $pdo->query($query);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        $idOld = $res['id'];

        $newPdo = $pdo->connectNew();
        $this->assertTrue($newPdo->ping());

        $stmt = $newPdo->query($query);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        $idNew = $res['id'];

        $this->assertNotEquals($idOld, $idNew);
    }
}