<?php

namespace V1\Users;

use Db\Querys;
use Exception;
use Tools\Gui;
use Tools\JsonResponse;
use Tools\Security;
use V1\Referrals\ReferralsQuerys;
use V1\Sessions\SessionsQuerys;
use V1\Users\Referrals\Referrals;
use V1\Users\UsersQuerys;

class Users
{
    protected const SET = 'user_id, email, invite_code, registration_code, username, names, lastnames, age, avatar, phone, points, movile_data, create_date, update_date';
    protected const TIME = 'Y-m-d H:i:00';

    public static function getUsersAlls()
    {
        $userQuery = new Querys('users');
        $arrayUser = $userQuery->selectAll(self::SET);
        if ($arrayUser) {
            JsonResponse::read('users', $arrayUser);
        } else throw new Exception('not found resource', 404);
    }

    public static function getUserById()
    {
        $userQuery = new Querys('users');
        $arrayUser = $userQuery->select('user_id', $_GET['id'], self::SET);
        if ($arrayUser) {
            $user = $arrayUser[0];
            JsonResponse::read('user', $user);
        } else throw new Exception('not found resource', 404);
    }

    public static function createUser()
    {
        $userQuery = new Querys('users');
        $referredQuery = new Querys('referrals');

        $arrayUser = $userQuery->select('email', $_REQUEST['email'], 'user_id');
        if (!$arrayUser) {
            // validacion para el codigo de registro
            $registrationCode = $_REQUEST['invite_code'] ?? null;
            if (!is_null($registrationCode)) {
                $arrayId = $userQuery->select('invite_code', $registrationCode, 'user_id');
                $user_id = $arrayId[0]->user_id;
                if (!$user_id) throw new Exception('invite code incorrect', 400);
            }
            $id = Gui::generate();

            $email = $_REQUEST['email'];
            $password = Security::encryptPassword($_REQUEST['password']);
            $inviteCode = Gui::generate();
            $username = substr($email, 0, strpos($email, '@'));

            $_arrayUser['user_id'] = $id;
            $_arrayUser['email'] = $email;
            $_arrayUser['password'] = $password;
            $_arrayUser['invite_code'] = $inviteCode;
            $_arrayUser['registration_code'] = $registrationCode;
            $_arrayUser['username'] = $username;
            date_default_timezone_set('America/Caracas');
            $_arrayUser['create_date'] = date(self::TIME);
            $userQuery->insert($_arrayUser);

            $arrayReferred['user_id'] = $id;
            $arrayReferred['invite_code'] = $inviteCode;
            $referredQuery->insert($arrayReferred);

            if (!is_null($registrationCode)) {
                $_GET['id'] = $user_id;
                $info = Referrals::createReferred($id);
            } else $info = null;

            unset($_arrayUser['password']);
            $path = 'https://' . $_SERVER['SERVER_NAME'] . '/v1/sessions/';
            JsonResponse::created('user', $_arrayUser, $path, $info);

        } else throw new Exception('email exist', 400);
    }

    public static function updateUser()
    {
        $userQuery = new Querys('users');

        $arrayUser = $userQuery->select('user_id', $_GET['id'], self::SET . ', password');
        $user = (array)$arrayUser[0];

        // se descartan los codigos para referido
        unset($user['invite_code'], $user['registration_code']);
        $newUser = $_REQUEST;

        foreach ($user as $_key => $_value) {
            $_keyFound = false;
            foreach ($newUser as $key => $value) {
                if ($_key == $key) {
                    $_arrayUser[$_key] = ($key == 'password') ?
                        Security::encryptPassword($_REQUEST['password']) :
                        $value;
                    $_keyFound = true;
                }
            }
            if (!$_keyFound and $_key != 'user_id') {
                $_arrayUser[$_key] = $_value;
            }
        }
        date_default_timezone_set('America/Caracas');
        $_arrayUser['update_date'] = date(self::TIME);

        $userQuery->update('user_id', $_GET['id'], $_arrayUser);
        unset($_arrayUser['password']);
        JsonResponse::updated('user', $_arrayUser);
    }

    public static function removeUser()
    {
        // $sessionQuery = new Querys('sessions');
        $userQuery = new Querys('users');
        // $referredQuery = new Querys('referrals');

        $arrayUser = $userQuery->select('user_id', $_GET['id'], 'registration_code');
        $registrationCode = $arrayUser[0]->registration_code;

        // $sessionQuery->delete('user_id', $_GET['id']);
        // $userQuery->delete('user_id', $_GET['id']);
        // $referredQuery->delete('user_id', $_GET['id']);

        if (!is_null($registrationCode)) {
            $arrayUser = $userQuery->select('invite_code', $registrationCode, 'user_id');
            $user_id = $arrayUser[0]->user_id;
            Referrals::removeReferred($user_id);
        }

        JsonResponse::removed();
    }
}
