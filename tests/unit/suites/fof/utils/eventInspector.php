<?php
/**
 * @package	    FrameworkOnFramework.UnitTest
 * @subpackage  Utils
 *
 * @copyright   Copyright (C) 2010-2016 Akeeba Ltd. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

class F0FUtilsObservableEventInspector extends F0FUtilsObservableEvent
{
	/**
	 * Mock Event Method
	 *
	 * @param   null  $var1  Var 1
	 * @param   null  $var2  Var 2
	 *
	 * @return mixed A value to test against
	 */
	public function onTestEvent($var1 = null, $var2 = null)
	{

		$return = '';

		if (is_string($var1))
		{
			$return .= $var1;
		}

		if (is_string($var2))
		{
			$return .= $var2;
		}

		if (is_array($var1))
		{
			$return .= implode('', $var1);
		}

		return $return;
	}
}
//@codingStandardsIgnoreStart
/**
 * Mock function to test event system in JEventDispatcher
 *
 * @return string Static string "JEventDispatcherMockFunction executed"
 *
 * @since 11.3
 */
function F0FUtilsObservableEventMockFunction()
{
	return 'F0FUtilsObservableDispatcherMockFunction executed';
}