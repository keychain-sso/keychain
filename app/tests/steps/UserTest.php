<?php

/**
 * Keychain
 *
 * SSO login provider for enterprise.
 *
 * @package     Keychain
 * @copyright   (c) Keychain Developers
 * @license     http://opensource.org/licenses/BSD-3-Clause
 * @link        https://github.com/keychain-sso/keychain
 * @since       Version 1.0
 * @filesource
 */

use \Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * UserTest class
 *
 * Unit test cases for UserController
 *
 * @package     Keychain
 * @subpackage  UnitTests
 */
class UserTest extends KeychainTestCase {

	/**
	 * Tests the getList method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetList()
	{
		$this->be(TestHelper::createUser(UserStatus::ACTIVE, true)->user);
		$this->call('GET', 'user/list');

		$this->assertResponseOk();
		$this->assertViewHas('users');
	}

	/**
	 * Tests the getCreate method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetCreate()
	{
		$this->be(TestHelper::createUser(UserStatus::ACTIVE, true)->user);
		$this->call('GET', 'user/create');

		$this->assertResponseOk();
		$this->assertViewHas('modal');
	}

	/**
	 * Tests the postCreate method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testPostCreate()
	{
		$this->be(TestHelper::createUser(UserStatus::ACTIVE, true)->user);

		$this->call('POST', 'user/create', array(
			'name'     => 'unittestname',
			'email'    => 'unittest@unittest.sso',
			'password' => 'somepassword',
		));

		$this->assertEquals(User::where('name', 'unittestname')->count(), 1);
	}

	/**
	 * Tests the getView method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetView()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;

		$this->be($user);
		$this->call('GET', "user/view/{$user->hash}");

		$this->assertResponseOk();
		$this->assertViewHas('user');
	}

	/**
	 * Tests the postView method of the controller with a small avatar
	 *
	 * @access public
	 * @return void
	 */
	public function testPostViewSmallAvatar()
	{
		$temp = '/tmp/keychain-'.md5(microtime()).'.png';
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$upload = public_path()."/uploads/avatars/{$user->hash}";

		File::copy(public_path().'/img/default-avatar.png', $temp);

		$this->be($user);

		$this->call('POST', "user/view", ['hash' => $user->hash], [
			'avatar' => new UploadedFile($temp, null)
		]);

		File::delete($upload);

		$this->assertFalse(File::exists($upload));
		$this->assertRedirectedTo("user/view/{$user->hash}");
	}

	/**
	 * Tests the postView method of the controller with a large avatar
	 *
	 * @access public
	 * @return void
	 */
	public function testPostViewLargeAvatar()
	{
		$size = Config::get('view.icon_size');
		$temp = '/tmp/keychain-'.md5(microtime()).'.png';
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$upload = public_path()."/uploads/avatars/{$user->hash}";

		File::copy(public_path().'/img/default-avatar.png', $temp);

		$image = Image::make($temp);
		$image->resize($size + 1, $size);
		$image->save();

		$this->be($user);

		$this->call('POST', "user/view", ['hash' => $user->hash], [
			'avatar' => new UploadedFile($temp, null)
		]);

		File::delete($upload);

		$this->assertFalse(File::exists($upload));
		$this->assertRedirectedTo("user/avatar/{$user->hash}");
	}

	/**
	 * Tests the getAvatar method of the controller without an action
	 *
	 * @access public
	 * @return void
	 */
	public function testGetAvatar()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;

		$this->be($user);
		$this->session(['user.avatar.resize' => true]);
		$this->call('GET', "user/avatar/{$user->hash}");

		$this->assertResponseOk();
		$this->assertViewHas('modal');
	}

	/**
	 * Tests the getAvatar method of the controller with the 'remove' action
	 *
	 * @access public
	 * @return void
	 */
	public function testGetAvatarRemove()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$upload = public_path()."/uploads/avatars/{$user->hash}";

		File::copy(public_path().'/img/default-avatar.png', $upload);

		$this->be($user);
		$this->session(['user.avatar.resize' => true]);
		$this->call('GET', "user/avatar/{$user->hash}/remove");

		$this->assertFalse(File::Exists($upload));
		$this->assertRedirectedTo("user/view/{$user->hash}");
	}

	/**
	 * Tests the postAvatar method of the controller without an action
	 *
	 * @access public
	 * @return void
	 */
	public function testPostAvatar()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$upload = public_path()."/uploads/avatars/{$user->hash}";

		File::copy(public_path().'/img/default-avatar.png', $upload);

		$this->be($user);
		$this->session(['user.avatar.resize' => true]);

		$this->call('POST', 'user/avatar', [
			'hash'          => $user->hash,
			'screen_width'  => '200',
			'screen_height' => '200',
			'width'         => '50',
			'height'        => '50',
			'x'             => '0',
			'y'             => '0',
		]);

		File::delete($upload);

		$this->assertFalse(File::exists($upload));
		$this->assertRedirectedTo("user/view/{$user->hash}");
	}

	/**
	 * Tests the getEdit method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetEdit()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;

		$this->be($user);
		$this->call('GET', "user/edit/{$user->hash}");

		$this->assertResponseOk();
		$this->assertViewHas('modal');
		$this->assertViewHas('fieldEdit');
	}

	/**
	 * Tests the getEdit method of the controller for users without permission
	 *
	 * @access public
	 * @return void
	 * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function testGetEditNoPermission()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$hash = User::first()->hash;

		$this->be($user);
		$this->call('GET', "user/edit/{$hash}");

		$this->assertResponseOk();
	}

	/**
	 * Tests the postEdit method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testPostEdit()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;

		$this->be($user);

		$this->call('POST', 'user/edit', [
			'hash'          => $user->hash,
			'name'          => 'unittestnew',
			'title'         => 'title',
			'gender'        => 'M',
			'date_of_birth' => '1980-01-01',
			'timezone'      => 'America/Chicago',
		]);

		$this->assertSessionHas('messages.success');
		$this->assertEquals(User::where('name', 'unittestnew')->count(), 1);
	}

	/**
	 * Tests the getEmails method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetEmails()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;

		$this->be($user);
		$this->call('GET', "user/emails/{$user->hash}");

		$this->assertResponseOk();
		$this->assertViewHas('modal');
		$this->assertViewHas('emails');
	}

	/**
	 * Tests the getEmails method of the controller with the 'remove' action
	 *
	 * @access public
	 * @return void
	 */
	public function testGetEmailsRemove()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$email = UserEmail::where('user_id', $user->id)->where('primary', Flags::NO)->first();

		$this->be($user);
		$this->call('GET', "user/emails/{$user->hash}/remove/{$email->id}");

		$this->assertSessionHas('messages.success');
		$this->assertEquals(UserEmail::find($email->id), null);
	}

	/**
	 * Tests the getEmails method of the controller with the 'verify' action
	 *
	 * @access public
	 * @return void
	 */
	public function testGetEmailsVerify()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;

		$email = UserEmail::where('user_id', $user->id)->where('primary', Flags::NO)->first();
		$email->verified = Flags::NO;
		$email->save();

		$this->be($user);
		$this->call('GET', "user/emails/{$user->hash}/verify/{$email->id}");

		$this->assertSessionHas('messages.success');
		$this->assertEquals(UserEmail::find($email->id)->verified, Flags::YES);
	}

	/**
	 * Tests the getEmails method of the controller with the 'primary' action
	 *
	 * @access public
	 * @return void
	 */
	public function testGetEmailsPrimary()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$email = UserEmail::where('user_id', $user->id)->where('primary', Flags::NO)->first();

		$this->be($user);
		$this->call('GET', "user/emails/{$user->hash}/primary/{$email->id}");

		$this->assertEquals(UserEmail::find($email->id)->primary, Flags::YES);
	}

	/**
	 * Tests the postEmails method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testPostEmails()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;

		$this->be($user);

		$this->call('POST', 'user/emails', [
			'hash'    => $user->hash,
			'email'   => 'unittest@unitnew.sso',
		]);

		$this->assertSessionHas('messages.success');
		$this->assertEquals(UserEmail::where('address', 'unittest@unitnew.sso')->count(), 1);
	}

	/**
	 * Tests the getKeys method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetKeys()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;

		$this->be($user);
		$this->call('GET', "user/keys/{$user->hash}");

		$this->assertResponseOk();
		$this->assertViewHas('modal');
		$this->assertViewHas('keys');
	}

	/**
	 * Tests the getKeys method of the controller with the 'remove' action
	 *
	 * @access public
	 * @return void
	 */
	public function testGetKeysRemove()
	{
		$data = TestHelper::createUser(UserStatus::ACTIVE);

		$this->be($data->user);
		$this->call('GET', "user/keys/{$data->user->hash}/remove/{$data->userKey->id}");

		$this->assertSessionHas('messages.success');
		$this->assertEquals(UserKey::find($data->userKey->id), null);
	}

	/**
	 * Tests the postKeys method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testPostKeys()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;

		$this->be($user);
		$this->call('POST', 'user/keys', [
			'hash'  => $user->hash,
			'title' => 'RSA Key',
			'key'   => 'ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAklOUpkDHrfHY17SbrmTIpNLTGK9Tjom/BWDSU'.
			           'GPl+nafzlHDTYW7hdI4yZ5ew18JH4JW9jbhUFrviQzM7xlELEVf4h9lFX5QVkbPppSwg0cda3'.
			           'Pbv7kOdJ/MTyBlWXFCR+HAo3FXRitBqxiX1nKhXpHAZsMciLq8V6RjsNAQwdsdMFvSlVK/7XA'.
			           't3FaoJoAsncM1Q9x5+3V0Ww68/eIFmb1zuUFljQJKprrX88XypNDvjYNby6vw/Pb0rwert/En'.
			           'mZ+AW4OZPnTPI89ZPmVMLuayrD2cE86Z/il8b+gw3r3+1nKatmIkjn2so1d01QraTlMqVSsbx'.
			           'NrRFi9wrf+M7Q== schacon@mylaptop.local'
		]);

		$this->assertSessionHas('messages.success');
		$this->assertEquals(UserKey::where('title', 'RSA Key')->count(), 1);
	}

	/**
	 * Tests the getSecurity method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetSecurity()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;

		$this->be($user);
		$this->call('GET', "user/security/{$user->hash}");

		$this->assertResponseOk();
		$this->assertViewHas('modal');
		$this->assertViewHas('sessions');
	}

	/**
	 * Tests the getSecurity method of the controller with the 'killall' action
	 *
	 * @access public
	 * @return void
	 */
	public function testGetSecurityKillall()
	{
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;

		$this->be($admin);

		DB::table('user_sessions')->insert(array(
			'id'          => Session::getId(),
			'user_id'     => $user->id,
			'ip_address'  => '192.168.1.1',
			'payload'     => '',
			'device_type' => DeviceTypes::TABLET,
		));

		$this->call('GET', "user/security/{$user->hash}/killall");

		$this->assertSessionHas('messages.success');
		$this->assertEquals(UserSession::where('user_id', $user->id)->count(), 0);
	}

	/**
	 * Tests the postSecurity method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testPostSecurity()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;

		$this->be($admin);

		$this->call('POST', 'user/security', [
			'hash'             => $user->hash,
			'old_password'     => 'unittest',
			'new_password'     => 'newpass',
			'confirm_password' => 'newpass',
			'status'           => UserStatus::ACTIVE,
		]);

		$newPass = User::find($user->id)->password;

		$this->assertSessionHas('messages.success');
		$this->assertTrue(Hash::check('newpass', $newPass));
	}

	/**
	 * Tests the getPermissions method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetPermissions()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;

		$this->be($user);
		$this->call('GET', "user/permissions/{$user->hash}");

		$this->assertResponseOk();
		$this->assertViewHas('modal');
		$this->assertViewHas('acl');
	}

	/**
	 * Tests the getDelete method of the controller
	 *
	 * @access public
	 * @return void
	 */
	public function testGetDelete()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE)->user;
		$admin = TestHelper::createUser(UserStatus::ACTIVE, true)->user;

		$this->be($admin);
		$this->call('GET', "user/delete/{$user->hash}");

		$this->assertRedirectedTo('user/list');
		$this->assertSessionHas('messages.success');
		$this->assertEquals(User::find($user->id), null);
	}

	/**
	 * Tests the getSearch method of the controller with 'format' as icons
	 *
	 * @access public
	 * @return void
	 */
	public function testGetSearchIcons()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;

		$this->be($user);
		$this->call('GET', 'user/search/icons', ['query' => 'Unit Test'], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

		$this->assertResponseOk();
		$this->assertViewHas('users');
	}

	/**
	 * Tests the getSearch method of the controller with 'format' as list
	 *
	 * @access public
	 * @return void
	 */
	public function testGetSearchList()
	{
		$user = TestHelper::createUser(UserStatus::ACTIVE, true)->user;

		$this->be($user);
		$this->call('GET', 'user/search/list', ['query' => 'Unit Test'], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);

		$this->assertResponseOk();
		$this->assertViewHas('items');
	}

}
