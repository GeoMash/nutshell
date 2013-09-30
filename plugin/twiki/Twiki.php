<?php
namespace nutshell\plugin\twiki
{
	use nutshell\core\plugin\Plugin;
	use nutshell\behaviour\Native;
	use nutshell\behaviour\Singleton;
	use nutshell\plugin\twiki\lib\Text_Wiki;
	

	/**
	 * Twiki means TEXTUAL Wiki. This plugin is based on the PEAR textual wiki.
	 * You can have a look at the public method test() to see how to use this class.
	 * Known bugs:
	 *    '<code>' and '</code> can't be placed in the very same line such as '<code> ... </code>'. The wiki crashes in this case. 
	 */
	class Twiki extends Plugin implements Singleton
	{
		protected $wiki;

		public function init()
		{
			$this->wiki = new Text_Wiki();
		}
		
		/**
		 * This function transforms a wiki text into HTML text.
		 * @param string $text
		 */
		public function transform($text)
		{
			return $this->wiki->transform($text);
		}
		
		/**
		 * This method runs a test that shows an example.
		 * You can create an action inside of a controller for testing as follows:
		 * public function testWiki()
		   {
			$w = $this->plugin->Twiki();
			echo $w->test();		
		   }
		 */
		public function test()
		{
			/**
			 * The wiki text to be transformed into HTML.
			 * @var string
			 */
			$text =
'
Shows the index:

[[toc]]
----
//emphasis text// 
**strong text**
//**emphasis and strong**//
{{teletype text}}
@@--- delete text +++ insert text @@
@@--- delete only @@
@@+++ insert only @@
----
Headings:
			
+++  Level 3 Heading
	
++++ Level 4 Heading

+++++  Level 5 Heading

++++++ Level 6 Heading
			
----
			
* Bullet one
 * Sub-bullet
			 
# Numero uno
# Number two
 # Sub-item
 
# Number one
 * Bullet
 * Bullet
# Number two
 * Bullet
 * Bullet
  * Sub-bullet
   # Sub-sub-number
   # Sub-sub-number
# Number three
 * Bullet
 * Bullet
----
			
Shows a link: [http://pear.php.net PEAR]
			
Shows an image: http://c2.com/sig/wiki.gif

highlight php code: 
<code type="php">
echo "hello"; 
</code>

<code>
echo "Just some code"; 
</code>
----
Shows a table:

|| cell one || cell two ||
|||| big ol line ||
|| cell four || cell five ||
|| cell six || here\'s a very long cell ||
----
<html> oi </html>
';
			// returns the HTML
			return $this->transform($text);
		}
	}
}