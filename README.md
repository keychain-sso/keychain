# ![](https://avatars3.githubusercontent.com/u/7920184?s=32) Keychain [![Build Status](https://api.travis-ci.org/keychain-sso/keychain.svg)](https://travis-ci.org/keychain-sso/keychain)

Keychain is a SSO provider for enterprise. The project is currently in its inception phase.

## Download

Clone or download the `keychain` repository:

```
$ git clone https://github.com/keychain-sso/keychain.git
```

## Installation

The following commands need to be executed in the root folder of your keychain clone.

Keychain uses [Composer](https://getcomposer.org/) to manage dependencies. To fetch all required libraries, execute:
```
$ php composer.phar install
```

The next step is to set up your database. Key in your database server details in `app/config/database.php` and execute the following command:
```
$ php artisan migrate --seed
```

Next, you need to set up an encryption key for your keychain instance. Open `app/config/app.php` and set the `key` parameter to a 32 character alphanumeric hash.

Finally, you may log into Keychain with the following credentials:
 * Username: `admin@keychain.sso`
 * Password: `password`

## Reporting issues

Please report all issues on our [GitHub issue tracker](https://github.com/keychain-sso/keychain/issues).

### License

Keychain is licensed under the [BSD 3-Clause license](http://opensource.org/licenses/BSD-3-Clause).
