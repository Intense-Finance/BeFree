<?php
/**
 * BeFree OpenCart Theme
 *
 * Copyright (C) 2026 BeFree OpenCart Theme contributors
 * SPDX-License-Identifier: GPL-3.0-only
 *
 * Generated with Grok (xAI). This credit is requested, not an extra restriction.
 * Licensed under the GNU General Public License version 3. See LICENSE.
 */
namespace Opencart\Catalog\Controller\Extension\Befree\Startup;

class Befree extends \Opencart\System\Engine\Controller {
	public function index(): void {
		if (!$this->isActive()) {
			return;
		}

		// 4.0.x stops the event chain on the first non-null return, so this
		// listener returns nothing and runs early. 4.1 runs every listener and
		// ignores the return, so this listener runs late and keeps the route.
		$priority = version_compare($this->ocVersion(), '4.1.0.0', '>=') ? 999 : 0;
		$separator = version_compare($this->ocVersion(), '4.0.2.0', '>=') ? '.' : '|';

		$this->event->register(
			'view/*/before',
			new \Opencart\System\Engine\Action('extension/befree/startup/befree' . $separator . 'event'),
			$priority
		);
	}

	public function event(string &$route, array &$args, mixed &$code = null, mixed &$output = null): void {
		$targets = [
			'common/header',
			'common/footer',
			'common/home',
			'common/menu',
			'common/search',
			'common/cart',
			'product/thumb',
			'product/category',
			'product/product',
			'checkout/register'
		];

		if (!in_array($route, $targets, true)) {
			return;
		}

		if (in_array($route, ['common/header', 'common/footer', 'common/home', 'product/category', 'product/product'], true)) {
			$args['befree'] = $this->pack();
		}

		// Share cards are header-only. A failure here must not stop the page.
		if ($route === 'common/header') {
			try {
				$args['befree_share'] = $this->share();
			} catch (\Throwable $e) {
				$args['befree_share'] = ['product' => []];
			}
		}

		$route = 'extension/befree/' . $route;
	}

	private function isActive(): bool {
		$theme = (string)$this->config->get('config_theme');
		$selected = ($theme === 'befree' || $theme === 'theme_befree' || substr($theme, -7) === '/befree');

		if (!$selected) {
			return false;
		}

		$status = $this->config->get('theme_befree_status');

		if ($status === null || $status === '') {
			return true;
		}

		return (int)$status === 1;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function pack(): array {
		$lang = (string)$this->config->get('config_language');
		$language_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');
		$uses = $this->config->get('theme_befree_use');

		// A missing or empty Product Use table keeps the sample rows. Hide that
		// menu with the Product use section switch. Saved blank text fields are
		// different: text() lets those render nothing.
		if (!is_array($uses) || !$uses) {
			$uses = $this->defaultUses();
		}

		$use_rows = [];

		foreach ($uses as $use) {
			if (empty($use['name'])) {
				continue;
			}

			$code = preg_replace('/[^a-z0-9\-]/', '', strtolower((string)($use['code'] ?? $use['name']))) ?: 'use';

			$use_rows[] = [
				'code'     => $code,
				'name'     => (string)$use['name'],
				'kicker'   => (string)($use['kicker'] ?? ''),
				'keywords' => (string)($use['keywords'] ?? ''),
				'href'     => $this->url->link('extension/befree/finder/finder', 'language=' . $lang . '&use=' . $code)
			];
		}

		$categories = [];
		$query = $this->db->query("SELECT c.`category_id`, cd.`name` FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.`category_id` = cd.`category_id`) LEFT JOIN `" . DB_PREFIX . "category_to_store` c2s ON (c.`category_id` = c2s.`category_id`) WHERE c.`parent_id` = '0' AND c.`status` = '1' AND cd.`language_id` = '" . $language_id . "' AND c2s.`store_id` = '" . $store_id . "' ORDER BY c.`sort_order`, LCASE(cd.`name`)");

		foreach ($query->rows as $row) {
			$categories[] = [
				'name' => $row['name'],
				'href' => $this->url->link('product/category', 'language=' . $lang . '&path=' . (int)$row['category_id'])
			];
		}

		$shelf = [];
		$master = $this->masterSql('p');
		$group_id = (int)$this->config->get('config_customer_group_id');
		$products = $this->db->query("SELECT p.`product_id`, p.`model`, p.`price`, p.`image`, p.`tax_class_id`, pd.`name`, (SELECT ps.`price` FROM `" . DB_PREFIX . "product_special` ps WHERE ps.`product_id` = p.`product_id` AND ps.`customer_group_id` = '" . $group_id . "' AND ((ps.`date_start` = '0000-00-00' OR ps.`date_start` < NOW()) AND (ps.`date_end` = '0000-00-00' OR ps.`date_end` > NOW())) ORDER BY ps.`priority` ASC, ps.`price` ASC LIMIT 1) AS `special` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.`product_id` = pd.`product_id`) LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.`product_id` = p2s.`product_id`) WHERE p.`status` = '1' AND p.`date_available` <= NOW() AND pd.`language_id` = '" . $language_id . "' AND p2s.`store_id` = '" . $store_id . "'" . $master . " ORDER BY p.`sort_order` ASC, LCASE(pd.`name`) ASC LIMIT 9");

		$this->load->model('tool/image');
		$currency = $this->session->data['currency'] ?? $this->config->get('config_currency');

		foreach ($products->rows as $row) {
			if ($row['image'] && is_file(DIR_IMAGE . $row['image'])) {
				$thumb = $this->model_tool_image->resize($row['image'], 480, 480);
			} else {
				$thumb = '';
			}

			$regular = $this->tax->calculate((float)$row['price'], (int)$row['tax_class_id'], (bool)$this->config->get('config_tax'));
			$special_raw = ($row['special'] !== null && $row['special'] !== '') ? (float)$row['special'] : 0.0;
			$special = $special_raw > 0 ? $this->tax->calculate($special_raw, (int)$row['tax_class_id'], (bool)$this->config->get('config_tax')) : 0.0;

			$shelf[] = [
				'name'    => $row['name'],
				'model'   => $row['model'],
				'thumb'   => $thumb,
				'price'   => $this->currency->format($regular, $currency),
				'special' => $special > 0 ? $this->currency->format($special, $currency) : '',
				'href'    => $this->url->link('product/product', 'language=' . $lang . '&product_id=' . (int)$row['product_id']),
				'bulk'    => ($special > 0 ? $special : $regular) >= (float)($this->config->get('theme_befree_bulk') ?: 1000)
			];
		}

		$flag = function (string $key, int $default = 1): bool {
			$value = $this->config->get($key);

			if ($value === null || $value === '') {
				return (bool)$default;
			}

			return (int)$value === 1;
		};

		$hex = function (string $key, string $fallback): string {
			$value = (string)$this->config->get($key);

			return preg_match('/^#[0-9A-Fa-f]{6}$/', $value) ? $value : $fallback;
		};

		return [
			'phone'              => $this->text('theme_befree_phone', ''),
			'email'              => $this->text('theme_befree_email', ''),
			'share_description'  => $this->text('theme_befree_share_description', ''),
			'share_image'        => $this->httpUrl($this->text('theme_befree_share_image', '')),
			'twitter'            => $this->twitterHandle($this->text('theme_befree_twitter', '')),
			'gold'         => $hex('theme_befree_gold', '#d7c785'),
			'navy'         => $hex('theme_befree_navy', '#0a2344'),
			'ink'          => $hex('theme_befree_ink', '#070d14'),
			'hero_kicker'  => $this->text('theme_befree_hero_kicker', 'INDUSTRIAL CATALOG'),
			'hero_title'   => $this->text('theme_befree_hero_title', 'The products you already sell, easier to find.'),
			// Keep this sentence identical to theme_befree_hero_lead in admin/controller/theme/befree.php.
			'hero_lead'    => $this->text('theme_befree_hero_lead', 'Manufacturer categories stay in the bar above. Product Use, underneath, searches the same products by how a buyer asks for them.'),
			'highlight'    => $this->text('theme_befree_highlight', 'Your line'),
			'hub1_name'    => $this->text('theme_befree_hub1_name', 'Hub 1'),
			'hub1_address' => $this->text('theme_befree_hub1_address', ''),
			'hub1_hours'   => $this->text('theme_befree_hub1_hours', ''),
			'hub1_phone'   => $this->text('theme_befree_hub1_phone', ''),
			'hub1_map'     => $this->httpUrl($this->text('theme_befree_hub1_map', '')),
			'hub2_name'    => $this->text('theme_befree_hub2_name', 'Hub 2'),
			'hub2_address' => $this->text('theme_befree_hub2_address', ''),
			'hub2_hours'   => $this->text('theme_befree_hub2_hours', ''),
			'hub2_phone'   => $this->text('theme_befree_hub2_phone', ''),
			'hub2_map'     => $this->httpUrl($this->text('theme_befree_hub2_map', '')),
			'show_hero'    => $flag('theme_befree_show_hero'),
			'show_uses'    => $flag('theme_befree_show_uses'),
			'show_proof'   => $flag('theme_befree_show_proof'),
			'show_matrix'  => $flag('theme_befree_show_matrix'),
			'show_advisor' => $flag('theme_befree_show_advisor'),
			'show_hubs'    => $flag('theme_befree_show_hubs'),
			'contact'      => $this->url->link('information/contact', 'language=' . $lang),
			'home'         => $this->url->link('common/home', 'language=' . $lang),
			'categories'   => $categories,
			'uses'         => $use_rows,
			'shelf'        => $shelf
		];
	}

	/**
	 * OpenCart 4.1 stores variant children on product.master_id. 4.0 has no such column.
	 */
	private function masterSql(string $alias): string {
		$column = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product` LIKE 'master_id'");

		if ($column->num_rows) {
			return " AND " . $alias . ".`master_id` = '0'";
		}

		return '';
	}

	/**
	 * Copy the owner can clear.
	 *
	 * OpenCart 4 config->get() returns '' both when a key was never stored and
	 * when it was saved blank, so config->has() is what tells those apart. A
	 * missing key keeps $fallback, which is how a fresh install and a store
	 * that has not saved this field yet still show the sample sentence. A
	 * saved blank, including whitespace, is returned as '' and the templates
	 * drop the text plus the separator or card around it.
	 *
	 * Colors and the bulk threshold are not handled here. An empty color or
	 * bulk value would break the stylesheet or the shelf flag, so those keep
	 * their own fallbacks.
	 */
	private function text(string $key, string $fallback): string {
		if (!$this->config->has($key)) {
			return $fallback;
		}

		$value = $this->config->get($key);

		if (!is_string($value) && !is_int($value) && !is_float($value)) {
			return $fallback;
		}

		return trim((string)$value);
	}

	/**
	 * Open Graph and X card data for the header.
	 *
	 * Product fields are filled only on an enabled product page. Other pages
	 * use the document title, meta description, and logo in the header template.
	 * A failure leaves product as an empty array so the generic card still renders.
	 *
	 * @return array<string, mixed>
	 */
	private function share(): array {
		try {
			$product = $this->shareProduct();
		} catch (\Throwable $e) {
			$product = [];
		}

		return ['product' => $product];
	}

	/**
	 * @return array<string, string>
	 */
	private function shareProduct(): array {
		$route = str_replace('|', '.', (string)($this->request->get['route'] ?? ''));

		if ($route !== 'product/product' || empty($this->request->get['product_id'])) {
			return [];
		}

		$product_id = (int)$this->request->get['product_id'];

		$this->load->model('catalog/product');

		$info = $this->model_catalog_product->getProduct($product_id);

		if (!$info || (int)($info['status'] ?? 0) !== 1 || trim((string)($info['name'] ?? '')) === '') {
			return [];
		}

		$name = $this->shareText((string)$info['name']);
		$description = $this->shareText((string)($info['meta_description'] ?? ''));

		if ($description === '') {
			$description = $this->shareText((string)($info['description'] ?? ''));
		}

		if (oc_strlen($description) > 160) {
			$cut = oc_substr($description, 0, 160);
			$space = oc_strrpos($cut, ' ');
			$description = rtrim(($space !== false && $space > 100) ? oc_substr($cut, 0, $space) : $cut, " ,;:-") . '…';
		}

		$image = '';
		$width = '';
		$height = '';

		if (!empty($info['image']) && is_file(DIR_IMAGE . (string)$info['image'])) {
			$this->load->model('tool/image');

			$image = $this->absoluteUrl((string)$this->model_tool_image->resize((string)$info['image'], 1200, 1200));

			if ($image !== '') {
				$width = '1200';
				$height = '1200';
			}
		}

		$amount = '';
		$currency = '';

		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			$base = (float)($info['special'] ?? 0) > 0 ? (float)$info['special'] : (float)$info['price'];
			$currency = (string)($this->session->data['currency'] ?? $this->config->get('config_currency'));

			if ($this->currency->has($currency)) {
				$value = $this->currency->format($this->tax->calculate($base, (int)$info['tax_class_id'], (bool)$this->config->get('config_tax')), $currency, 0, false);
				$amount = number_format((float)$value, 2, '.', '');
			} else {
				$currency = '';
			}
		}

		return [
			'name'        => $name,
			'description' => $description,
			'image'       => $image,
			'width'       => $width,
			'height'      => $height,
			'url'         => html_entity_decode((string)$this->url->link('product/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product_id), ENT_QUOTES, 'UTF-8'),
			'amount'      => $amount,
			'currency'    => $amount !== '' ? $currency : ''
		];
	}

	private function shareText(string $text): string {
		for ($i = 0; $i < 3; $i++) {
			$decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

			if ($decoded === $text) {
				break;
			}

			$text = $decoded;
		}

		$with_breaks = preg_replace('/<(br|\/p|\/div|\/li|\/h[1-6])\b[^>]*>/i', ' ', $text);
		$text = strip_tags($with_breaks === null ? $text : $with_breaks);
		$collapsed = preg_replace('/\s+/u', ' ', $text);

		return trim($collapsed === null ? $text : $collapsed);
	}

	private function absoluteUrl(string $url): string {
		$url = trim($url);

		if ($url === '') {
			return '';
		}

		if (strpos($url, '//') === 0) {
			return 'https:' . $url;
		}

		if (preg_match('#^https?://#i', $url) === 1) {
			return $url;
		}

		return rtrim((string)$this->config->get('config_url'), '/') . '/' . ltrim($url, '/');
	}

	/**
	 * A public http or https URL, or '' when the value is blank or not a plain URL.
	 */
	private function httpUrl(string $value): string {
		$value = trim($value);

		if ($value === '' || preg_match('#^https?://[^\s<>"\'\\\\]+$#i', $value) !== 1) {
			return '';
		}

		return $value;
	}

	/**
	 * An X handle for twitter:site, or '' when the value is blank or not a handle.
	 */
	private function twitterHandle(string $value): string {
		$handle = ltrim(trim($value), '@');

		if ($handle === '' || preg_match('/^[A-Za-z0-9_]{1,15}$/', $handle) !== 1) {
			return '';
		}

		return '@' . $handle;
	}

	private function ocVersion(): string {
		return defined('VERSION') ? VERSION : '4.1.0.0';
	}

	/**
	 * @return array<int, array<string, string>>
	 */
	private function defaultUses(): array {
		return [
			['code' => 'engine', 'name' => 'Engine', 'kicker' => 'LUBRICANTS', 'keywords' => 'oil, engine'],
			['code' => 'fuel', 'name' => 'Fuel', 'kicker' => 'CONDITIONER', 'keywords' => 'fuel'],
			['code' => 'gear', 'name' => 'Gear', 'kicker' => 'OILS', 'keywords' => 'gear, oil'],
			['code' => 'bearing', 'name' => 'Bearing', 'kicker' => 'GREASES', 'keywords' => 'grease, bearing'],
			['code' => 'hydraulic', 'name' => 'Hydraulic', 'kicker' => 'FLUIDS', 'keywords' => 'hydraulic'],
			['code' => 'plant', 'name' => 'Plant', 'kicker' => 'HEAVY', 'keywords' => 'plant, industrial'],
			['code' => 'fleet', 'name' => 'Fleet', 'kicker' => 'HEAVY', 'keywords' => 'fleet, diesel'],
			['code' => 'marine', 'name' => 'Marine', 'kicker' => 'HEAVY', 'keywords' => 'marine']
		];
	}
}
