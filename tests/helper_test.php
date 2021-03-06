<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\viglink\tests;

class helper_test extends \phpbb_test_case
{
	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\cache\service */
	protected $cache;

	public function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		include_once($phpbb_root_path . 'includes/functions.' . $phpEx);

		$this->cache = $this->getMockBuilder('\phpbb\cache\service')
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * Test data for test_set_viglink_services
	 */
	public function set_viglink_services_data()
	{
		return array(
			array(
				'1.0.0', // Board version is less than current version on branch 1, get setting from latest version
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'allow_viglink_global'		=> true,
						'allow_viglink_phpbb'		=> true,
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
						'allow_viglink_global'		=> false,
						'allow_viglink_phpbb'		=> false,
					),
				),
				array(
					'allow_viglink_global'		=> false,
					'allow_viglink_phpbb'		=> false,
				),
			),
			array(
				'1.0.1', // Board version is equal to current version on branch 1, get setting from latest version
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'allow_viglink_global'		=> false,
						'allow_viglink_phpbb'		=> true,
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
						'allow_viglink_global'		=> true,
						'allow_viglink_phpbb'		=> false,
					),
				),
				array(
					'allow_viglink_global'		=> true,
					'allow_viglink_phpbb'		=> false,
				),
			),
			array(
				'1.0.1-a1', // Board version is less than current version on branch 1, get setting from latest version
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1-a2',
						'allow_viglink_global'		=> true,
						'allow_viglink_phpbb'		=> false,
					),
					'1.1'	=> array(
						'current'		=> '1.1.0',
						'allow_viglink_global'		=> false,
						'allow_viglink_phpbb'		=> true,
					),
				),
				array(
					'allow_viglink_global'		=> false,
					'allow_viglink_phpbb'		=> true,
				),
			),
			array(
				'1.1.0', // Board version is less than current version on branch 2
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'allow_viglink_global'		=> false,
						'allow_viglink_phpbb'		=> false,
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
						'allow_viglink_global'		=> true,
						'allow_viglink_phpbb'		=> true,
					),
				),
				array(
					'allow_viglink_global'		=> true,
					'allow_viglink_phpbb'		=> true,
				),
			),
			array(
				'1.1.1', // Board version is equal to current version on branch 2
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'allow_viglink_global'		=> true,
						'allow_viglink_phpbb'		=> true,
					),
					'1.1'	=> array(
						'current'		=> '1.1.1',
						'allow_viglink_global'		=> false,
						'allow_viglink_phpbb'		=> false,
					),
				),
				array(
					'allow_viglink_global'		=> false,
					'allow_viglink_phpbb'		=> false,
				),
			),
			array(
				'1.1.0-a1', // Board version is less than current version on branch 2
				array(
					'1.0'	=> array(
						'current'		=> '1.0.1',
						'allow_viglink_global'		=> false,
						'allow_viglink_phpbb'		=> false,
					),
					'1.1'	=> array(
						'current'		=> '1.1.0-a2',
						'allow_viglink_global'		=> true,
						'allow_viglink_phpbb'		=> true,
					),
				),
				array(
					'allow_viglink_global'		=> true,
					'allow_viglink_phpbb'		=> true,
				),
			),
			array(
				'1.1.0', // Current version data not available, existing values unchanged
				array(),
				array(
					'allow_viglink_global'		=> true,
					'allow_viglink_phpbb'		=> true,
				),
			),
		);
	}

	/**
	 * Test the set_viglink_services method
	 *
	 * @dataProvider set_viglink_services_data
	 */
	public function test_set_viglink_services($current_version, $versions, $expected)
	{
		$config = new \phpbb\config\config(array(
			'version' => $current_version,
			'allow_viglink_global' => 1,
			'allow_viglink_phpbb' => 1,
		));

		/** @var $viglink_helper \PHPUnit_Framework_MockObject_MockObject|\phpbb\viglink\acp\viglink_helper */
		$viglink_helper = $this
			->getMockBuilder('\phpbb\viglink\acp\viglink_helper')
			->setMethods(array(
				'get_versions_matching_stability',
			))
			->setConstructorArgs(array(
				$this->cache,
				$config,
				new \phpbb\file_downloader(),
				new \phpbb\user('\phpbb\datetime'),
			))
			->getMock()
		;

		$viglink_helper->expects($this->any())
			->method('get_versions_matching_stability')
			->will($this->returnValue($versions));

		$viglink_helper->set_viglink_services();

		foreach ($expected as $config_name => $expected_value)
		{
			$this->assertEquals($expected_value, $config[$config_name]);
		}
	}
}
