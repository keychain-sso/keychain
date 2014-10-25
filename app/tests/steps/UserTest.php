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

		if (File::exists($upload))
		{
			File::delete($upload);
		}
		else
		{
			$this->assertTrue(false);
		}

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

		if (File::exists($upload))
		{
			File::delete($upload);
		}
		else
		{
			$this->assertTrue(false);
		}

		$this->assertRedirectedTo("user/avatar/{$user->hash}");
	}

}
