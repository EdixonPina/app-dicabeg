<?php

namespace V1\Users;

use PDO;
use Db\PgSqlConnection;
use Tools\GeneralMethods;

class UsersQuerys extends PgSqlConnection
{
    private const SET = 'user_id, email, invite_code, username, names, lastnames, age, avatar, phone, points, movile_data, create_date, update_date';

    public static function search($where)
    {
        $sql = "SELECT COUNT(user_id)
                FROM users
                WHERE " . $where . " = ?";

        $query = self::connection()->prepare($sql);
        $query->execute([
            $_REQUEST[$where]
        ]);
        $result = $query->fetch(PDO::FETCH_OBJ);
        return $result->count;
    }

    public static function select($where, $fields = self::SET)
    {
        $sql = "SELECT " . $fields
            . " FROM users
                WHERE " . $where . " = ?";

        $query = self::connection()->prepare($sql);
        $query->execute([
            $_REQUEST[$where]
        ]);
        return GeneralMethods::processSelect($query, false);
    }

    public static function selectAlls($fields = self::SET)
    {
        $sql = "SELECT " . $fields
            . " FROM users";

        $query = self::connection()->prepare($sql);
        $query->execute();
        return GeneralMethods::processSelect($query);
    }

    public static function selectById($fields = self::SET)
    {
        $sql = "SELECT " . $fields
            . " FROM users
                WHERE user_id = ?";

        $query = self::connection()->prepare($sql);
        $query->execute([
            $_GET['id']
        ]);
        return GeneralMethods::processSelect($query);
    }

    public static function insert($arraySet)
    {
        $sql = "INSERT INTO users (user_id, email, password, invite_code, registration_code, username, create_date)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $query = self::connection()->prepare($sql);
        $query->execute([
            $_GET['id'],
            $arraySet[0],
            $arraySet[1],
            $arraySet[2],
            $arraySet[3],
            $arraySet[4],
            date('Y-m-d H:i')
        ]);
        return GeneralMethods::processQuery($query);
    }

    public static function update($arraySet)
    {
        $sql = "UPDATE users
                SET email = ?, password = ?, username = ?, names = ?, lastnames = ?, age = ?, avatar = ?, phone = ?, points = ?, movile_data = ?, update_date = ?
                WHERE user_id = ?";

        $query = self::connection()->prepare($sql);
        $query->execute([
            $arraySet[0],
            $arraySet[1],
            $arraySet[2],
            $arraySet[3],
            $arraySet[4],
            $arraySet[5],
            $arraySet[6],
            $arraySet[7],
            $arraySet[8],
            $arraySet[9],
            date('Y-m-d H:i'),
            $_GET['id']
        ]);
        return GeneralMethods::processQuery($query);
    }

    public static function delete()
    {
        $sql = "DELETE FROM users
                WHERE user_id = ?";

        $query = self::connection()->prepare($sql);
        $query->execute([
            $_GET['id']
        ]);
        return GeneralMethods::processDelete($query);
    }
}
