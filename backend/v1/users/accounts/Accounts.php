<?php

require_once '../../tools/Gui.php';
require_once '../../tools/GeneralMethods.php';
require_once '../data/Data.php';
require_once 'AccountsQuerys.php';

class Accounts extends AccountsQuerys
{
    public static function getAccountsAlls()
    {
        $query = self::selectAlls();
        $result = GeneralMethods::processAlls($query);
        if ($result) {
            return $result;
        } else self::error();
    }

    public static function getAccountsById()
    {
        $query = self::selectById($_GET['id']);
        $result = GeneralMethods::processById($query);
        if ($result) {
            return $result;
        } else self::error();
    }

    public static function insertAccount()
    {
        $existingUser = self::checkout('email');
        if (!$existingUser) {
            $_GET['id'] = Gui::generate();
            $arrayAccount[] = $_REQUEST['email'];
            $arrayAccount[] = Security::encryptPassword();

            $result = self::insert($arrayAccount);
            self::interpretResult($result);
            Data::insertData(); // TODO: Usar la clase abstracta o la clase query. Ver esto con mas detalle en la integracion de (accounts-data) despues

            $arrayResponse[] = [
                'Successful' => 'Created Users',
                'id' => $_GET['id']
            ];
        } else throw new Exception('User exist', 400);

        return $arrayResponse;
    }

    public static function updateAccount()
    {
        $existingUser = self::checkout('id');
        if ($existingUser) {
            foreach ($_REQUEST as $key => $value) {
                $value = ($key == 'password') ? Security::encryptPassword() : $value;
            }
            $result = self::update($key, $value);
            self::interpretResult($result);

            $arrayResponse[] = ['Successful' => 'Updated user account'];
        } else self::error();

        return $arrayResponse;
    }

    public static function deleteAccount()
    {
        $existingUser = self::checkout('id');
        if ($existingUser) {
            $result = DataQuerys::delete();
            self::interpretResult($result);

            $result = self::delete();
            self::interpretResult($result);

            $arrayResponse[] = ['Successful' => 'Deleted user account'];
        } else self::error();

        return $arrayResponse;
    }

    private function interpretResult($result)
    {
        $error = $result->errorInfo();
        $errorExist = !is_null($error[1]);
        if ($errorExist) {
            throw new Exception($error[2], 400);
        }
    }

    private function checkout($field)
    {
        if ($field == 'id') {
            $result = self::selectById($_GET['id']);
        } else if ($field == 'email') {
            $result = self::selectById($_REQUEST['email'], 'email');
        }
        $rows = $result->rowCount();
        return $rows ? true : false;
    }

    private function error()
    {
        throw new Exception('User does not exist', 400);
    }
}