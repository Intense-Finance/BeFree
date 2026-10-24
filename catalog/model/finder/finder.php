<?php
/**
 * BeFree OpenCart Theme
 * Copyright (C) 2026 BeFree OpenCart Theme contributors
 * SPDX-License-Identifier: GPL-3.0-only
 * Generated with Grok (xAI). See README.md.
 */
namespace Opencart\Catalog\Model\Extension\Befree\Finder;

class Finder extends \Opencart\System\Engine\Model {
	/**
	 * @param array<int, string> $keywords
	 * @return array<int, array<string, mixed>>
	 */
	public function products(array $keywords, int $limit = 24): array {
		$language_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');
		$likes = [];

		foreach ($keywords as $word) {
			$word = trim($word);

			if ($word === '' || strlen($word) > 40) {
				continue;
			}

			$safe = $this->db->escape(str_replace(['%', '_'], '', $word));
			$likes[] = "LCASE(pd.`name`) LIKE '%" . $safe . "%' OR LCASE(p.`model`) LIKE '%" . $safe . "%'";
		}

		if (!$likes) {
			return [];
		}

		$master = '';
		$column = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product` LIKE 'master_id'");

		if ($column->num_rows) {
			$master = " AND p.`master_id` = '0'";
		}

		$sql = "SELECT DISTINCT p.`product_id`, p.`model`, p.`price`, p.`image`, p.`tax_class_id`, pd.`name` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.`product_id` = pd.`product_id`) LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.`product_id` = p2s.`product_id`) WHERE p.`status` = '1' AND p.`date_available` <= NOW() AND pd.`language_id` = '" . $language_id . "' AND p2s.`store_id` = '" . $store_id . "'" . $master . " AND (" . implode(' OR ', $likes) . ") ORDER BY LCASE(pd.`name`) LIMIT " . (int)$limit;

		return $this->db->query($sql)->rows;
	}
}
