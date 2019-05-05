<?php

namespace V2\Controllers;

use Exception;
use V2\Modules\Time;
use V2\Modules\Format;
use V2\Database\Querys;
use V2\Modules\Security;
use V2\Modules\Diffusion;
use V2\Libraries\SendGrid;
use V2\Modules\JsonResponse;
use V2\Modules\EmailTemplate;
use V2\Interfaces\IController;

class UserController implements IController
{
    public static function index()
    {
        $arrayUser = Querys::table('users')
            ->select(self::USERS_COLUMNS)
            ->group(GROUP_NRO)
            ->getAll(function () {
                throw new Exception('not found users', 404);
            });

        JsonResponse::read('users', $arrayUser);
    }

    public static function show()
    {
        $user = Querys::table('users')
            ->select(self::USERS_COLUMNS)
            ->where('user_id', USERS_ID)
            ->get(function () {
                throw new Exception('user not found', 404);
            });

        JsonResponse::read('user', $user);
    }

    public static function store($body)
    {
        $userQuery = Querys::table('users');

        $userQuery->insert($arrayUser = [
            'user_id' => Security::generateID(),
            'email' => Format::email($body->email),
            'password' => Security::generateHash($body->password),
            'username' => substr($body->email, 0, strpos($body->email, '@')),
            'create_date' => Time::current()->utc
        ])->execute();

        // ADD: Validar recurrencia de codigos,
        // asegurar unicidad con generacion recursiva del mismo
        // TODO: si se general de forma temporal, con timer triger,
        // entonces no hara falta validar unicidad
        $code = Security::generateCode(6);

        Querys::table('accounts')->insert([
            'user_id' => $arrayUser['user_id'],
            'temporal_code' => $code,
            'invite_code' => Security::generateCode(8),
            'registration_code' => $body->invite_code ?? null
        ])->execute();

        /*
        // validacion para el codigo de invitacion
        if (isset($body->invite_code)) {
            $user_id = $userQuery->select('user_id')
                ->where('invite_code', $body->invite_code)
                ->get(function () {
                    throw new Exception('invite code incorrect', 400);
                });
            define('REFARRALS_ID', $user_id);
        } else $body->invite_code = null;
         */


        // if (isset($body->invite_code)) { // TODO: migrar esto a la V2
        //     define('USERS_ID', $arrayUser['user_id']);
        //     $info = ReferredController::store();
        // } else $info = null;

        // Diffusion::sendNotification();



        // TODO: El idioma debe ser determinado en el
        // futuro mediante la config del usuario
        Diffusion::sendEmail(
            $arrayUser['email'],
            EmailTemplate::accountActivation($code, 'spanish')
        );

        // $info['email'] = $emailResult;

        $path = 'https://' . $_SERVER['SERVER_NAME'] . '/v2/accounts/activation';
        JsonResponse::created('user', (object)$arrayUser, $path, $info = null);
    }

    public static function update($body)
    {
        Querys::table('users')->update($arrayUser = [
            'email' => isset($body->email) ?
                Format::email($body->email) : null,

            'password' => isset($body->password) ?
                Security::generateHash($body->password) : null,

            'phone' => isset($body->phone) ?
                Format::phone($body->phone) : null,

            'username' => $body->username ?? null,
            'names' => $body->names ?? null,
            'lastnames' => $body->lastnames ?? null,
            'age' => $body->age ?? null,
            'avatar' => $body->avatar ?? null,
            'points' => $body->points ?? null,
            'money' => $body->money ?? null,

            'update_date' => Time::current()->utc
        ])->where('user_id', USERS_ID)->execute();

        JsonResponse::updated('user', (object)$arrayUser);
    }

    public static function destroy()
    {
        Querys::table('accounts')->delete()
            ->where('user_id', USERS_ID)
            ->execute();

        // Querys::table('referrals')->delete('referrals')
        //     ->where('referred_id', USERS_ID)
        //     ->execute();

        // Querys::table('history')->delete('history')
        //     ->where('user_id', USERS_ID)
        //     ->execute(function(){
        // throw new Exception("Error deleted", 500);
        // });

        Querys::table('users')->delete()
            ->where('user_id', USERS_ID)
            ->execute();

        JsonResponse::removed();
    }
}
