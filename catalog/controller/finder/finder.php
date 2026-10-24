<?php
/**
 * BeFree OpenCart Theme
 * Copyright (C) 2026 BeFree OpenCart Theme contributors
 * SPDX-License-Identifier: GPL-3.0-only
 * Generated with Grok (xAI). See README.md.
 */
namespace Opencart\Catalog\Controller\Extension\Befree\Finder;

class Finder extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$lang = (string)$this->config->get('config_language');
		$code = (string)($this->request->get['use'] ?? '');
		$code = preg_replace('/[^a-z0-9\-]/', '', strtolower($code)) ?: '';

		$uses = $this->config->get('theme_befree_use');
		$current = null;

		if (is_array($uses)) {
			foreach ($uses as $use) {
				$use_code = preg_replace('/[^a-z0-9\-]/', '', strtolower((string)($use['code'] ?? '')));

				if ($use_code === $code) {
					$current = $use;
					break;
				}
			}
		}

		if (!$current) {
			$current = [
				'name'     => 'Product use',
				'kicker'   => 'APPLICATION',
				'keywords' => ''
			];
		}

		$this->load->language('extension/befree/finder/finder');
		$this->document->setTitle((string)$current['name'] . ' — Product Use');

		$data['heading'] = (string)$current['name'];
		$data['kicker'] = (string)($current['kicker'] ?? '');
		$data['keywords'] = (string)($current['keywords'] ?? '');
		$data['products'] = [];

		$words = array_filter(array_map('trim', explode(',', $data['keywords'])));

		if ($words) {
			$this->load->model('extension/befree/finder/finder');
			$this->load->model('tool/image');

			$rows = $this->model_extension_befree_finder_finder->products($words);

			foreach ($rows as $row) {
				if ($row['image'] && is_file(DIR_IMAGE . $row['image'])) {
					$thumb = $this->model_tool_image->resize($row['image'], 480, 480);
				} else {
					$thumb = '';
				}

				$raw = $this->tax->calculate((float)$row['price'], (int)$row['tax_class_id'], (bool)$this->config->get('config_tax'));
				$currency = $this->session->data['currency'] ?? $this->config->get('config_currency');

				$data['products'][] = [
					'name'  => $row['name'],
					'model' => $row['model'],
					'thumb' => $thumb,
					'price' => $this->currency->format($raw, $currency),
					'href'  => $this->url->link('product/product', 'language=' . $lang . '&product_id=' . (int)$row['product_id'])
				];
			}
		}

		$data['contact'] = $this->url->link('information/contact', 'language=' . $lang);
		$data['header'] = $this->load->controller('common/header');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/befree/finder/finder', $data));
	}
}
