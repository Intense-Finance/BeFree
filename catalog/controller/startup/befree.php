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
			'product/product'
		];

		if (!in_array($route, $targets, true)) {
			return;
		}

		if (in_array($route, ['common/header', 'common/footer', 'common/home', 'product/category', 'product/product'], true)) {
			$args['befree'] = $this->pack();
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
		$products = $this->db->query("SELECT p.`product_id`, p.`model`, p.`price`, p.`image`, p.`tax_class_id`, pd.`name` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.`product_id` = pd.`product_id`) LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.`product_id` = p2s.`product_id`) WHERE p.`status` = '1' AND p.`date_available` <= NOW() AND pd.`language_id` = '" . $language_id . "' AND p2s.`store_id` = '" . $store_id . "'" . $master . " ORDER BY p.`sort_order` ASC, LCASE(pd.`name`) ASC LIMIT 9");

		$this->load->model('tool/image');

		foreach ($products->rows as $row) {
			if ($row['image'] && is_file(DIR_IMAGE . $row['image'])) {
				$thumb = $this->model_tool_image->resize($row['image'], 480, 480);
			} else {
				$thumb = '';
			}

			$raw = $this->tax->calculate((float)$row['price'], (int)$row['tax_class_id'], (bool)$this->config->get('config_tax'));
			$currency = $this->session->data['currency'] ?? $this->config->get('config_currency');

			$shelf[] = [
				'name'  => $row['name'],
				'model' => $row['model'],
				'thumb' => $thumb,
				'price' => $this->currency->format($raw, $currency),
				'href'  => $this->url->link('product/product', 'language=' . $lang . '&product_id=' . (int)$row['product_id']),
				'bulk'  => $raw >= (float)($this->config->get('theme_befree_bulk') ?: 1000)
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

		$text = function (string $key, string $fallback): string {
			$value = $this->config->get($key);

			if ($value === null || $value === '') {
				return $fallback;
			}

			return (string)$value;
		};

		return [
			'phone'        => $text('theme_befree_phone', ''),
			'email'        => $text('theme_befree_email', ''),
			'gold'         => $hex('theme_befree_gold', '#d7c785'),
			'navy'         => $hex('theme_befree_navy', '#0a2344'),
			'ink'          => $hex('theme_befree_ink', '#070d14'),
			'hero_kicker'  => $text('theme_befree_hero_kicker', 'INDUSTRIAL CATALOG'),
			'hero_title'   => $text('theme_befree_hero_title', 'The products you already sell, easier to find.'),
			'highlight'    => $text('theme_befree_highlight', 'Your line'),
			'hub1_name'    => $text('theme_befree_hub1_name', 'Hub 1'),
			'hub1_address' => $text('theme_befree_hub1_address', ''),
			'hub1_hours'   => $text('theme_befree_hub1_hours', ''),
			'hub1_phone'   => $text('theme_befree_hub1_phone', ''),
			'hub2_name'    => $text('theme_befree_hub2_name', 'Hub 2'),
			'hub2_address' => $text('theme_befree_hub2_address', ''),
			'hub2_hours'   => $text('theme_befree_hub2_hours', ''),
			'hub2_phone'   => $text('theme_befree_hub2_phone', ''),
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
