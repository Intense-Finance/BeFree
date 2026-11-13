<?php
/**
 * BeFree OpenCart Theme
 *
 * Copyright (C) 2026 BeFree OpenCart Theme contributors
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * Generated with Grok (xAI). This credit is requested, not an extra restriction.
 * Licensed under the GNU General Public License version 3. See LICENSE.
 *
 * Run from the repository root: php tests/optional_text.php
 */
declare(strict_types=1);

namespace Opencart\System\Engine {
	class Controller {
		public object $config;

		public function __construct(object $config) {
			$this->config = $config;
		}
	}
}

namespace {
	/**
	 * Same contract as OpenCart 4.0.0.0 through 4.1.0.4 system/engine/config.php:
	 * get() is '' for a missing key, and has() is false only when the key was
	 * never set. A saved blank is stored and has() is true.
	 */
	final class OcConfig {
		/** @var array<string, mixed> */
		private array $data = [];

		public function set(string $key, mixed $value): void {
			$this->data[$key] = $value;
		}

		public function get(string $key): mixed {
			return isset($this->data[$key]) ? $this->data[$key] : '';
		}

		public function has(string $key): bool {
			return isset($this->data[$key]);
		}
	}

	$root = dirname(__DIR__);
	require $root . '/catalog/controller/startup/befree.php';

	$failures = 0;

	function check(bool $ok, string $message): void {
		global $failures;

		if ($ok) {
			echo "ok  $message\n";
			return;
		}

		$failures++;
		fwrite(STDERR, "FAIL  $message\n");
	}

	function textOf(OcConfig $config, string $key, string $fallback): string {
		$controller = new Opencart\Catalog\Controller\Extension\Befree\Startup\Befree($config);
		$method = new ReflectionMethod($controller, 'text');
		$result = $method->invoke($controller, $key, $fallback);

		if (!is_string($result)) {
			throw new RuntimeException('text() did not return a string');
		}

		return $result;
	}

	function callPrivate(OcConfig $config, string $name, string $arg): string {
		$controller = new Opencart\Catalog\Controller\Extension\Befree\Startup\Befree($config);
		$method = new ReflectionMethod($controller, $name);
		$result = $method->invoke($controller, $arg);

		if (!is_string($result)) {
			throw new RuntimeException($name . '() did not return a string');
		}

		return $result;
	}

	$lead = 'Manufacturer categories stay in the bar above. Product Use, underneath, searches the same products by how a buyer asks for them.';
	$config = new OcConfig();

	check(textOf($config, 'theme_befree_hub2_name', 'Hub 2') === 'Hub 2', 'missing hub 2 name keeps the sample');
	check($config->get('theme_befree_hub2_name') === '', 'OpenCart get() is empty for a missing key');
	check($config->has('theme_befree_hub2_name') === false, 'OpenCart has() is false for a missing key');

	$config->set('theme_befree_hub2_name', '');
	check(textOf($config, 'theme_befree_hub2_name', 'Hub 2') === '', 'saved blank hub 2 name stays blank');

	$config->set('theme_befree_hub2_name', '   ');
	check(textOf($config, 'theme_befree_hub2_name', 'Hub 2') === '', 'whitespace-only hub 2 name stays blank');

	$config->set('theme_befree_hub2_name', 'Hub 2');
	check(textOf($config, 'theme_befree_hub2_name', 'Hub 2') === 'Hub 2', 'saved sample hub 2 name is kept');

	$config->set('theme_befree_hub1_name', '  HUB1  ');
	check(textOf($config, 'theme_befree_hub1_name', 'Hub 1') === 'HUB1', 'saved hub name is trimmed, not replaced');

	$config->set('theme_befree_highlight', '');
	check(textOf($config, 'theme_befree_highlight', 'Your line') === '', 'saved blank highlight stays blank');

	$config->set('theme_befree_highlight', 'Pro1 line');
	check(textOf($config, 'theme_befree_highlight', 'Your line') === 'Pro1 line', 'saved highlight is kept');

	check(textOf(new OcConfig(), 'theme_befree_highlight', 'Your line') === 'Your line', 'missing highlight keeps the sample');
	check(textOf(new OcConfig(), 'theme_befree_hero_kicker', 'INDUSTRIAL CATALOG') === 'INDUSTRIAL CATALOG', 'missing hero kicker keeps the sample');
	check(textOf(new OcConfig(), 'theme_befree_hero_title', 'The products you already sell, easier to find.') === 'The products you already sell, easier to find.', 'missing hero title keeps the sample');
	check(textOf(new OcConfig(), 'theme_befree_hero_lead', $lead) === $lead, 'missing hero lead keeps the current sentence');

	$cleared = new OcConfig();
	$cleared->set('theme_befree_hero_lead', '');
	check(textOf($cleared, 'theme_befree_hero_lead', $lead) === '', 'saved blank hero lead stays blank');

	$kept = new OcConfig();
	$kept->set('theme_befree_hero_lead', $lead);
	check(textOf($kept, 'theme_befree_hero_lead', $lead) === $lead, 'saved hero lead is unchanged');

	$corrupt = new OcConfig();
	$corrupt->set('theme_befree_hub2_name', ['Hub 2']);
	check(textOf($corrupt, 'theme_befree_hub2_name', 'Hub 2') === 'Hub 2', 'non-text hub 2 value keeps the sample');

	$phone = new OcConfig();
	$phone->set('theme_befree_phone', '');
	check(textOf($phone, 'theme_befree_phone', '') === '', 'saved blank phone stays blank');
	check(textOf(new OcConfig(), 'theme_befree_phone', '') === '', 'missing phone stays blank');

	$admin = (string)file_get_contents($root . '/admin/controller/theme/befree.php');
	$catalog = (string)file_get_contents($root . '/catalog/controller/startup/befree.php');
	$home = (string)file_get_contents($root . '/catalog/view/template/common/home.twig');
	$admin_twig = (string)file_get_contents($root . '/admin/view/template/theme/befree.twig');
	$install = (string)file_get_contents($root . '/install.json');

	check(substr_count($admin, $lead) === 1, 'admin default hero lead matches the current sentence once');
	check(substr_count($catalog, $lead) === 1, 'storefront hero lead fallback matches the current sentence once');
	check(!str_contains($home, $lead), 'home template no longer hardcodes the hero lead');
	check(str_contains($home, 'befree.hero_lead'), 'home template prints the hero lead setting');
	check(str_contains($admin_twig, 'name="theme_befree_hero_lead"'), 'admin form edits the hero lead');
	check(str_contains($admin, 'array_key_exists($key, $setting)'), 'admin keeps a saved blank instead of refilling it');
	check(str_contains($catalog, 'theme_befree_gold'), 'color fallback is still present');
	check(str_contains($install, '"version": "1.2.2"'), 'install.json version is 1.2.2');
	check(str_contains($admin, "'theme_befree_twitter'"), 'admin default includes an x handle setting');
	check(str_contains($admin, "'theme_befree_hub1_map'"), 'admin default includes a hub map setting');
	check(str_contains($admin_twig, 'name="theme_befree_twitter"'), 'admin form edits the x handle');
	check(str_contains($admin_twig, 'name="theme_befree_hub1_map"'), 'admin form edits the hub map');
	check(str_contains($admin_twig, 'name="theme_befree_share_image"'), 'admin form edits the share image');
	check(str_contains($home, 'product.special'), 'home shelf can print a special price');
	check(str_contains($catalog, 'function twitterHandle'), 'storefront validates the x handle');
	check(str_contains($catalog, 'function httpUrl'), 'storefront validates public urls');

	check(textOf(new OcConfig(), 'theme_befree_twitter', '') === '', 'missing x handle stays blank');
	check(textOf(new OcConfig(), 'theme_befree_hub1_map', '') === '', 'missing hub map stays blank');
	check(textOf(new OcConfig(), 'theme_befree_share_description', '') === '', 'missing share description stays blank');
	check(textOf(new OcConfig(), 'theme_befree_share_image', '') === '', 'missing share image stays blank');

	$blank = new OcConfig();
	check(callPrivate($blank, 'httpUrl', '') === '', 'blank url stays blank');
	check(callPrivate($blank, 'httpUrl', 'https://example.com/map') === 'https://example.com/map', 'https url is kept');
	check(callPrivate($blank, 'httpUrl', 'http://example.com/map') === 'http://example.com/map', 'http url is kept');
	check(callPrivate($blank, 'httpUrl', 'javascript:alert(1)') === '', 'javascript url is dropped');
	check(callPrivate($blank, 'httpUrl', 'https://example.com/a b') === '', 'url with a space is dropped');
	check(callPrivate($blank, 'twitterHandle', '') === '', 'blank x handle stays blank');
	check(callPrivate($blank, 'twitterHandle', '@ExampleStore') === '@ExampleStore', 'x handle keeps a leading @');
	check(callPrivate($blank, 'twitterHandle', 'ExampleStore') === '@ExampleStore', 'x handle gains a leading @');
	check(callPrivate($blank, 'twitterHandle', 'not a handle!') === '', 'invalid x handle is dropped');

	$bundle = '';
	$scan = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

	foreach ($scan as $file) {
		$path = $file->getPathname();

		if (str_contains($path, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR) || str_contains($path, DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR)) {
			continue;
		}

		$ext = strtolower((string)$file->getExtension());

		if (!in_array($ext, ['php', 'twig', 'css', 'js', 'json', 'md'], true)) {
			continue;
		}

		$bundle .= (string)file_get_contents($path);
	}

	foreach ([
		'slop' . 'core',
		'GT' . 'M-MSJZ6HTD',
		'G-PQYN' . 'MDGBZ2',
		'IF' . 'Finance',
		'if' . '.finance',
		'maps' . '.app.goo.gl',
		'pri' . 'ntify',
		'Age' . 'nC',
		'EVE-' . '1000',
		'Piper' . 'Pepper',
		'Intense ' . 'Finance'
	] as $needle) {
		check(stripos($bundle, $needle) === false, 'theme text has no store-specific value');
	}

	if ($failures > 0) {
		fwrite(STDERR, "$failures failed\n");
		exit(1);
	}

	echo "all passed\n";
}
