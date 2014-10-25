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
			'email'    => 'unittest@unittest.com',
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
		$emailId = UserEmail::where('user_id', $user->id)->where('primary', Flags::NO)->first()->id;

		$this->be($user);
		$this->call('GET', "user/emails/{$user->hash}/remove/{$emailId}");

		$this->assertSessionHas('messages.success');
		$this->assertEquals(UserEmail::find($emailId), null);
	}

}
