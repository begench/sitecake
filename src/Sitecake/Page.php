<?php

namespace Sitecake;

use \phpQuery;

class Page {

	protected $sourceHtml;

	protected $doc;

	protected $_containers;

	public function __construct($html) {
		$this->sourceHtml = $html;		
		$this->doc = phpQuery::newDocument($html);
	}

	public function __toString() {
		return (string)$this->doc;
	}

	public function render() {
		$this->adjustNavLinks();
		return $this->doc;
	}

	public function prefixResourceUrls($prefix) {
		foreach (phpQuery::pq('a, img',	$this->doc) as $node) {
			HtmlUtils::prefixNodeAttrs($node, 'src,href,srcset', $prefix, function($url) {
				return Utils::isResourceUrl($url);
			});
		}		
	}

	public function unprefixResourceUrls($prefix) {
		foreach (phpQuery::pq('a, img',	$this->doc) as $node) {
			HtmlUtils::unprefixNodeAttrs($node, 'src,href,srcset', $prefix, function($url) {
				return Utils::isResourceUrl($url);
			});
		}				
	}

	public function listResourceUrls() {
		$urls = array();
		foreach ($this->containerNodes() as $container) {
			$urls = array_merge($urls, $this->listContainerResourceUrls($container));
		}
		return $urls;
	}

	/**
	 * Lists URLs (href attribute values) from selected nav items.
	 * 
	 * @param  string $selector CSS selector for nav items 
	 * @return array           list of URLs in nav items
	 */
	public function listNavUrls($selector) {
		$urls = array();
		foreach (phpQuery::pq($selector, $this->doc) as $node) {
			$el = phpQuery::pq($node, $this->doc);
			$urls[$el->attr('href')] = $el->text();
		}
		return $urls;
	}

	/**
	 * Sets the given nav HTML content into element(s) referenced by the
	 * given CSS selector.
	 *  
	 * @param string $selector one or more nav elements to set the HTML to
	 * @param string $html the nav HTML content to set
	 */
	public function setNav($selector, $html) {
		foreach (phpQuery::pq($selector, $this->doc) as $node) {
			$el = phpQuery::pq($node, $this->doc);
			$el->html($html);
		}		
	}

	/**
	 * Adds the 'noindex' meta tag in the page header, if not present.
	 */
	public function addRobotsNoIndex() {
		if (phpQuery::pq('meta[content="noindex"]', $this->doc)->count() === 0) {
			phpQuery::pq('head', $this->doc)->prepend('<meta name="robots" content="noindex"/>');
		}
	}

	/**
	 * Removes the 'noindex' meta tag from the page header, if present.
	 */
	public function removeRobotsNoIndex() {
		phpQuery::pq('meta[content="noindex"]', $this->doc)->remove();
	}

	/**
	 * Checks if the 'noindex' metatag is present.
	 * @return boolean true if the 'noindex' meta tag is present
	 */
	public function isRobotsNoIndex() {
		return (phpQuery::pq('meta[content="noindex"]', $this->doc)->count() > 0);
	}

	/**
	 * Reads the page description meta tag.
	 * 
	 * @return string       current description text
	 */
	public function getPageDescription() {
		$text = '';
		$tag = phpQuery::pq('meta[name="description"]', $this->doc);
		if ($tag->count() > 0) {
			$text = phpQuery::pq($tag->elements[0])->attr('content');
		}
		return $text;
	}

	/**
	 * Sets the page description meta tag with the given content.
	 */
	public function setPageDescription($text) {
		$meta = phpQuery::pq('meta[name="description"]', $this->doc);
		if ($text === '') {
			$meta->remove();
		} else {
			if ($meta->count() > 0) {
				$meta->attr('content', $text);
			} else {
				phpQuery::pq('head', $this->doc)->prepend('<meta name="description" content="'.$text.'"/>');
			}
		}
	}

	public function setContainerContent($containerName, $content) {
		foreach (phpQuery::pq('.sc-content-' . $containerName, $this->doc) as $node) {
			$container = phpQuery::pq($node, $this->doc);
			$container->html($content);
		}
	}

	public function addMetadata() {
		if (phpQuery::pq('meta[content="sitecake"]', $this->doc)->count() === 0) {
			phpQuery::pq('head', $this->doc)->prepend('<meta name="application-name" content="sitecake"/>');
		}
	}

	public function removeMetadata() {
		phpQuery::pq('meta[content="sitecake"]', $this->doc)->remove();
	}

	public function ensurePageId() {
		$this->addMetadata();
		phpQuery::pq('meta[content="sitecake"]', $this->doc)->attr('data-pageid', Utils::id());
	}

	public function pageId() {
		return phpQuery::pq('meta[content="sitecake"]', $this->doc)->attr('data-pageid');
	}

	public function removePageId() {
		phpQuery::pq('meta[content="sitecake"]', $this->doc)->removeAttr('data-pageid');
	}

	public function appendCodeToHead($code) {
		HtmlUtils::appendToHead($this->doc, $code);		
	}

	protected function listContainerResourceUrls($container) {
		$urls = array();
		$html = (string)phpQuery::pq($container, $this->doc);
		preg_match_all("/[^\\s\"',]*(?:files|images)\\/[^\\s]*\\-sc[0-9a-f]{13}[^\.]*\\.[0-9a-zA-Z]+/", 
			$html, $matches);
		foreach ($matches[0] as $match) {
			if (Utils::isResourceUrl($match)) {
				array_push($urls, $match);	
			}
		}
		return $urls;
	}

	protected function containerNodes() {
		$containers = array();
		foreach (phpQuery::pq('[class*="sc-content"]', $this->doc) as $node) {
			$container = phpQuery::pq($node, $this->doc);
			$class = $container->attr('class');
			if (preg_match('/(^|\s)sc\-content(\-[^\s]+)*(\s|$)/', $class, $matches)) {
				array_push($containers, $container);
			}			
		}
		return $containers;
	}

	public function normalizeContainerNames() {
		foreach ($this->containerNodes() as $node) {
			$container = phpQuery::pq($node, $this->doc);
			$class = $container->attr('class');
			if (preg_match('/(^|\s)sc\-content($|\s)/', $class, $matches)) {
				$container->addClass('sc-content-_cnt_' . uniqid());
			}
		}		
	}
	
	public function cleanupContainerNames() {
		foreach ($this->containerNodes() as $node) {
			$container = phpQuery::pq($node, $this->doc);
			$class = $container->attr('class');
			if (preg_match('/(^|\s)(sc\-content\-_cnt_[^\s]+)/', 
					$class, $matches)) {
				$container->removeClass($matches[2]);
			}
		}		
	}
	
	/**
	 * Returns a list of container names.
	 * 
	 * @return array a list of container names
	 */
	public function containers() {
		if (!$this->_containers) {
			$this->_containers = array();
			foreach ($this->containerNodes() as $container) {
				preg_match('/(^|\s)sc-content-([^\s]+)/', 
					$container->attr('class'), $matches);
				if (isset($matches[2])) {
					array_push($this->_containers, $matches[2]);
				}
			}					
		}
		return $this->_containers;
	}

	protected function adjustNavLinks() {
		foreach (phpQuery::pq('a', $this->doc) as $node) {
			$href = $node->getAttribute('href');
			if (!Utils::isExternalNavLink($href)) {
				$node->setAttribute('href', 'sitecake.php?page=' . $href);
			}
		}
	}	
}