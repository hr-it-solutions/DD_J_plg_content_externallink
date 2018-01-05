<?php
/**
 * @package    DD_External_Link
 *
 * @author     HR IT-Solutions Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2017 - 2018 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * PlgContentDD_ExternalLink class.
 *
 * @since  1.0.0.0
 */
class PlgContentDD_ExternalLink extends JPlugin
{
	protected $app;

	protected $mode;

	protected $autoloadLanguage = true;

	/**
	 *
	 * @param  string   $context   The context of the content being passed to the plugin.
	 * @param  object   &$article  The article object.  Note $article->text is also available
	 * @param  mixed    &$params   The article params
	 * @param  integer  $page      The 'page' number
	 *
	 * @since  1.0.0.0
	 *
	 * @return bool
	 */
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		if ($this->app->isClient('administrator'))
		{
			return true;
		}

		if ($context == 'com_content.article')
		{
			// Get plugin parameter
			$this->mode = (string) $this->params->get('mode');

			$LinkDisclaimerText = JText::_('PLG_CONTENT_DD_EXTERNALLINK_DD_PUSHUPBOX_NOTE');

			preg_match_all('/<a\s*href="([^"]+)"[^>]+>/', $row->text, $matches, PREG_SET_ORDER);

			// Unset all target
			if ((int) $this->params->get('targetunset'))
			{
				$row->text = preg_replace('/target="(.*?)"/', '', $row->text);
			}

			$matchesURLS = [];

			// Remove duplicate
			foreach ($matches as $matche)
			{
				if (!in_array($matche[1], $matchesURLS))
				{
					array_push($matchesURLS, $matche[1]);
				}
			}

			foreach ($matchesURLS as $matche)
			{

				if ($this->isexternal($matche))
				{
					if ($this->mode === '1') // Target _Blank
					{
						$row->text = str_replace(// Search value
							'href="' . $matche . '"',

							// Replacement value
							" target=\"_blank\"" . " href=\"" . $matche . "\"",

							$row->text);
					}
					elseif ($this->mode === '2') // DD PushUpBox Mode
					{
						$PushUpContent = "<div class=\'pub_backlink\'>";
						$PushUpContent .= "<p class=\'disclaimer\'>" . $LinkDisclaimerText . "</p>";
						$PushUpContent .= "<p><a class=\'btn btn-primary\' href=\'" . $matche . "\' target=\'_blank\'>" .
							JText::_('PLG_CONTENT_DD_EXTERNALLINK_DD_PUSHUPBOX_LINKTEXT') . "</a></p>";
						$PushUpContent .= "<p><small>Link: " . $matche . "</small></p>";
						$PushUpContent .= "</div>";

						$row->text = str_replace(// Search value
							'href="' . $matche . '"',

							// Replacement value
							" onclick=\"DD_PushUpContent('" . $PushUpContent . "','Weblink')\"" . " rel=\"nofollow\"" . " href=\"javascript:void(0)\"",

							$row->text);
					}
				}
			}

			return true;
		}
	}

	/**
	 * Check if URL isexternal
	 *
	 * @param   string  $url  The url to check
	 *
	 * @return bool
	 */
	protected function isexternal($url)
	{
		$components = parse_url($url);
		$host       = JUri::getInstance()->getHost();

		if (!isset($components['host']))
		{
			return false;
		}

		if ($components['host'] == $host)
		{
			return false;
		}
		elseif ($components['host'] == 'www' . $host)
		{
			return false;
		}
		elseif (strpos($components['host'], $host, strlen($components['host']) - strlen($host)) !== false)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}
