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
namespace Opencart\Admin\Controller\Extension\Befree\Theme;

class Befree extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('extension/befree/theme/befree');
		$this->document->setTitle($this->language->get('heading_title'));

		$store_id = isset($this->request->get['store_id']) ? (int)$this->request->get['store_id'] : 0;
		$token = 'user_token=' . $this->session->data['user_token'];

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $token)
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', $token . '&type=theme')
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/befree/theme/befree', $token . '&store_id=' . $store_id)
		];

		$data['save'] = $this->url->link($this->methodRoute('extension/befree/theme/befree', 'save'), $token . '&store_id=' . $store_id);
		$data['back'] = $this->url->link('marketplace/extension', $token . '&type=theme');

		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('theme_befree', $store_id);
		$defaults = $this->defaults();

		foreach ($defaults as $key => $value) {
			$data[$key] = array_key_exists($key, $setting) ? $setting[$key] : $value;
		}

		if (empty($data['theme_befree_use']) || !is_array($data['theme_befree_use'])) {
			$data['theme_befree_use'] = $defaults['theme_befree_use'];
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/befree/theme/befree', $data));
	}

	public function save(): void {
		$this->load->language('extension/befree/theme/befree');

		$store_id = isset($this->request->get['store_id']) ? (int)$this->request->get['store_id'] : 0;
		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/befree/theme/befree')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			if (isset($this->request->post['theme_befree_use']) && is_array($this->request->post['theme_befree_use'])) {
				$clean = [];

				foreach ($this->request->post['theme_befree_use'] as $row) {
					if (empty($row['name'])) {
						continue;
					}

					$clean[] = [
						'code'     => preg_replace('/[^a-z0-9\-]/', '', strtolower((string)($row['code'] ?? $row['name']))),
						'name'     => substr((string)$row['name'], 0, 40),
						'kicker'   => substr((string)($row['kicker'] ?? ''), 0, 24),
						'keywords' => substr((string)($row['keywords'] ?? ''), 0, 180)
					];
				}

				$this->request->post['theme_befree_use'] = $clean;
			}

			$this->model_setting_setting->editSetting('theme_befree', $this->request->post, $store_id);
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function install(): void {
		$this->load->model('setting/startup');
		$this->model_setting_startup->deleteStartupByCode('befree');

		$action = 'catalog/extension/befree/startup/befree';

		// 4.0.0.0 takes separate arguments. 4.0.1.0 and later take one array.
		// The description column exists from 4.1.0.0. Earlier models ignore that key.
		if (version_compare($this->ocVersion(), '4.0.1.0', '<')) {
			$this->model_setting_startup->addStartup('befree', $action, true, 2);
		} else {
			$this->model_setting_startup->addStartup([
				'code'        => 'befree',
				'description' => 'BeFree OpenCart Theme',
				'action'      => $action,
				'status'      => 1,
				'sort_order'  => 2
			]);
		}
	}

	public function uninstall(): void {
		$this->load->model('setting/startup');
		$this->model_setting_startup->deleteStartupByCode('befree');
	}

	/**
	 * OpenCart 4.0.0.0 through 4.0.1.x split controller methods on "|".
	 * 4.0.2.0 and later split on "." and strip a bare "|" from the class name.
	 */
	private function methodRoute(string $route, string $method): string {
		$separator = version_compare($this->ocVersion(), '4.0.2.0', '>=') ? '.' : '|';

		return $route . $separator . $method;
	}

	private function ocVersion(): string {
		return defined('VERSION') ? VERSION : '4.1.0.0';
	}

	/**
	 * @return array<string, mixed>
	 */
	private function defaults(): array {
		return [
			'theme_befree_status'        => 1,
			'theme_befree_phone'         => '',
			'theme_befree_email'         => '',
			'theme_befree_gold'          => '#d7c785',
			'theme_befree_navy'          => '#0a2344',
			'theme_befree_ink'           => '#070d14',
			'theme_befree_hero_kicker'   => 'INDUSTRIAL CATALOG',
			'theme_befree_hero_title'    => 'The products you already sell, easier to find.',
			'theme_befree_highlight'     => 'Your line',
			'theme_befree_hub1_name'     => 'Hub 1',
			'theme_befree_hub1_address'  => 'Street, city, country',
			'theme_befree_hub1_hours'    => 'Mon – Fri: 9:00 – 17:00',
			'theme_befree_hub1_phone'    => '',
			'theme_befree_hub2_name'     => 'Hub 2',
			'theme_befree_hub2_address'  => 'Street, city, country',
			'theme_befree_hub2_hours'    => 'Mon – Fri: 9:00 – 17:00',
			'theme_befree_hub2_phone'    => '',
			'theme_befree_show_hero'     => 1,
			'theme_befree_show_uses'     => 1,
			'theme_befree_show_proof'    => 1,
			'theme_befree_show_matrix'   => 1,
			'theme_befree_show_advisor'  => 1,
			'theme_befree_show_hubs'     => 1,
			'theme_befree_bulk'          => '1000',
			'theme_befree_use'           => $this->defaultUses()
		];
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
