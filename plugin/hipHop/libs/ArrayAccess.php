<?php
/**
 * This class has been written to give HipHop PHP
 * ArrayAccess support.
 * 
 * @author Timothy Chandler <tim.chandler@spinifexgroup.com>
 * @package nutshell-plugin
 * @since 21/03/2011
 * @version 1.0
 */
if (!interface_exists('ArrayAccess'))
{
	/**
	 * @package nutshell-plugin
	 */
	interface ArrayAccess
	{
		public function offsetGet($offset);
		public function offsetSet($offset,$value);
		public function offsetExists($offset);
		public function offsetUnset($offset);
	}
}
?>