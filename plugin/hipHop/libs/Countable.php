<?php
/**
 * This class has been written to give HipHop PHP
 * Countable support.
 * 
 * @author Timothy Chandler <tim.chandler@spinifexgroup.com>
 * @since 21/03/2011
 * @version 1.0
 * @package nutshell-plugin
 */
if (!interface_exists('Countable'))
{
	/**
	 * @package nutshell-plugin
	 */
	interface Countable
	{
		public function count();
	}
}
?>