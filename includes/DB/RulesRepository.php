<?php

namespace WpulsePricingRules\Includes\DB;

use WpulsePricingRules\Includes\Admin\Templates;

/**
 * Repository for pricing rules – CRUD with unified rule_json schema.
 */
class RulesRepository {

    public static function getTableName(): string {
        return Installer::getTableName();
    }

    /**
     * @return array{id: int, name: string, type: string, status: string, priority: int, rule_json: string, created_at: string, updated_at: string}|null
     */
    public static function find(int $id): ?array {
        global $wpdb;
        $table = self::getTableName();
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id),
            ARRAY_A
        );
        return $row ?: null;
    }

    /**
     * Get rule with decoded rule_json as 'rule' key.
     *
     * @return array{id: int, name: string, type: string, status: string, priority: int, rule: array, created_at: string, updated_at: string}|null
     */
    public static function findWithDecodedRule(int $id): ?array {
        $row = self::find($id);
        if (!$row) {
            return null;
        }
        $row['rule'] = self::decodeRuleJson($row['rule_json'] ?? '');
        unset($row['rule_json']);
        return $row;
    }

    /**
     * @return array<int, array>
     */
    public static function all(string $orderBy = 'priority', string $order = 'ASC'): array {
        global $wpdb;
        $table = self::getTableName();
        $allowed = ['priority' => 'priority', 'updated_at' => 'updated_at', 'id' => 'id'];
        $col = $allowed[$orderBy] ?? 'priority';
        $dir = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY {$col} {$dir}", ARRAY_A);
        return $rows ?: [];
    }

    /**
     * Insert a new rule. $data must include name, type, status, priority, rule_json (or full rule array).
     *
     * @return int|null Inserted id or null on failure.
     */
    public static function insert(array $data): ?int {
        global $wpdb;
        $table = self::getTableName();
        $rule = $data['rule'] ?? null;
        if (is_array($rule)) {
            $data['rule_json'] = wp_json_encode($rule);
            unset($data['rule']);
        }
        $allowed = ['name', 'type', 'status', 'priority', 'rule_json'];
        $insert = array_intersect_key($data, array_flip($allowed));
        $insert['name'] = $insert['name'] ?? '';
        $insert['type'] = $insert['type'] ?? 'quantity_discount';
        $insert['status'] = $insert['status'] ?? 'draft';
        $insert['priority'] = isset($insert['priority']) ? (int) $insert['priority'] : 10;
        $insert['rule_json'] = $insert['rule_json'] ?? '{}';
        $wpdb->insert($table, $insert);
        return $wpdb->insert_id ? (int) $wpdb->insert_id : null;
    }

    /**
     * Update existing rule by id.
     */
    public static function update(int $id, array $data): bool {
        global $wpdb;
        $table = self::getTableName();
        if (isset($data['rule']) && is_array($data['rule'])) {
            $data['rule_json'] = wp_json_encode($data['rule']);
            unset($data['rule']);
        }
        $allowed = ['name', 'type', 'status', 'priority', 'rule_json'];
        $update = array_intersect_key($data, array_flip($allowed));
        if (empty($update)) {
            return true;
        }
        $result = $wpdb->update($table, $update, ['id' => $id]);
        return $result !== false;
    }

    public static function delete(int $id): bool {
        global $wpdb;
        $table = self::getTableName();
        $result = $wpdb->delete($table, ['id' => $id]);
        return $result !== false;
    }

    /**
     * Create a new draft rule from a template id.
     *
     * @return array{id: int, edit_url: string}|null
     */
    public static function createFromTemplate(string $template_id): ?array {
        $templates = Templates::all();
        $template = null;
        foreach ($templates as $t) {
            if (($t['id'] ?? '') === $template_id) {
                $template = $t;
                break;
            }
        }
        if (!$template || empty($template['defaults'])) {
            return null;
        }
        $rule = $template['defaults'];
        $rule['meta'] = $rule['meta'] ?? [];
        $rule['meta']['template_id'] = $template_id;
        $name = $template['title'] ?? __('New rule', 'wpulse-pricing-rules-for-woocommerce');
        $type = $template['type'] ?? 'quantity_discount';
        $id = self::insert([
            'name' => $name,
            'type' => $type,
            'status' => 'draft',
            'priority' => self::nextPriority(),
            'rule' => $rule,
        ]);
        if (!$id) {
            return null;
        }
        return [
            'id' => $id,
            'edit_url' => self::editUrl($id),
        ];
    }

    /**
     * Create a new draft rule from a scratch type (minimal config).
     *
     * @return array{id: int, edit_url: string}|null
     */
    public static function createFromScratch(string $scratch_type): ?array {
        $scratch = Templates::getScratchDefaults($scratch_type);
        if (!$scratch) {
            return null;
        }
        $id = self::insert([
            'name' => $scratch['name'],
            'type' => $scratch['type'],
            'status' => 'draft',
            'priority' => self::nextPriority(),
            'rule' => $scratch['rule'],
        ]);
        if (!$id) {
            return null;
        }
        return [
            'id' => $id,
            'edit_url' => self::editUrl($id),
        ];
    }

    private static function nextPriority(): int {
        global $wpdb;
        $table = self::getTableName();
        $max = (int) $wpdb->get_var("SELECT COALESCE(MAX(priority), 0) FROM {$table}");
        return $max + 10;
    }

    private static function editUrl(int $id): string {
        return admin_url('admin.php?page=wpulse-pricing-rules') . '#/rules/edit/' . $id;
    }

    private static function decodeRuleJson(string $json): array {
        if ($json === '' || $json === null) {
            return [];
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }
}
