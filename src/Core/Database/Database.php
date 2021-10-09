<?php

namespace App\Core\Database;

use DateTime;
use PDO;
use PDOException;

class Database
{
    private string $name;
    private string $host;
    private string $user;
    private string $password;
    private ?PDO $pdo = null;


    public function __construct($name, $host, $user, $password)
    {
        $this->name = $name;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
    }


    private function getPDO(): PDO
    {
        if ($this->pdo === null) {
            try {
                $pdo = new PDO('mysql:dbname='.$this->name.';host='.$this->host, $this->user, $this->password);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->exec('set names utf8');
                $this->pdo = $pdo;
            } catch (PDOException $e) {
                die('Database connection failed: '.$e->getMessage());
            }
        }

        return $this->pdo;
    }


    /**
     * @return array|mixed
     */
    public function query(string $statement, string $class = null, bool $one = false)
    {
        $q = $this->getPDO()->query($statement);
        if ($class === null) {
            $q->setFetchMode(PDO::FETCH_OBJ);
        } else {
            $entity = new $class();
            $q->setFetchMode(PDO::FETCH_INTO, $entity);
        }
        if ($one) {
            $data = $q->fetch();
        } else {
            $data = $q->fetchAll();
        }

        return $data;
    }


    /**
     * @return array|mixed
     */
    public function prepare(string $statement, array $params, $class = null, bool $one = false)
    {
        $q = $this->getPDO()->prepare($statement);
        $q->execute($params);

        if ($class === null) {
            $q->setFetchMode(PDO::FETCH_OBJ);
        } else {
            $q->setFetchMode(PDO::FETCH_CLASS, $class, [$this]);
        }

        if ($one) {
            $data = $q->fetch();
        } else {
            $data = $q->fetchAll();
        }

        return $data;
    }


    public function prepareBool(string $statement, ?array $parameters = []): bool
    {
        $q = $this->getPDO()->prepare($statement);

        if (!empty($parameters)) {
            foreach (array_values($parameters) as $key => $parameter) {
                if (is_array($parameter)) {
                    $parameter = json_encode($parameter);
                }
                if (is_bool($parameter)) {
                    $parameter = ($parameter ? 1 : 0);
                }
                if ($parameter instanceof DateTime) {
                    $parameter = $parameter->format('Y-m-d H:i:s');
                }
                $q->bindValue($key+1, $parameter);
            }
        }

        return $q->execute();
    }


    public function lastInsertId(): int
    {
        return $this->getPDO()->lastInsertId();
    }
}
