<?php

namespace Libraries;

use Dotenv\Dotenv;

class PhpDotEnv
{
   public function __construct()
   {
      if (empty($_ENV)) {
         $environmentDeveloper = Dotenv::create(__DIR__ . '../../../../');
         $environmentDeveloper->load();
      }

      define('APP_ENV', getenv('APP_ENV'));
      define('DATABASE_URL', getenv('DATABASE_URL'));
      define('ACCESS_KEY', getenv('ACCESS_KEY'));
      define('REFRESH_KEY', getenv('REFRESH_KEY'));

      define('SENDGRID_API_KEY', getenv('SENDGRID_API_KEY'));

      define('ONESIGNAL_USER_AUTH_KEY', getenv('ONESIGNAL_USER_AUTH_KEY'));
      define('ONESIGNAL_APP_ID', getenv('ONESIGNAL_APP_ID'));
      define('ONESIGNAL_REST_API_KEY', getenv('ONESIGNAL_REST_API_KEY'));
   }
}
