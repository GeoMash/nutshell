<?php
/**
 * This class has been written to give HipHop PHP
 * IteratorAggregate support.
 * 
 * @author Timothy Chandler <tim.chandler@spinifexgroup.com>
 * @since 21/03/2011
 * @version 1.0
 * @package nutshell-plugin
 */
if (!interface_exists('IteratorAggregate'))
{
	/**
	 * @package nutshell-plugin
	 */
	interface IteratorAggregate extends Traversable
	{
		
	}
}
?>