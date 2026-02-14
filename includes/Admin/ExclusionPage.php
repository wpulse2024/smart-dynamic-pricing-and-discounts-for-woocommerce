<?php

namespace WpulsePricingRules\Includes\Admin;

use WpulsePricingRules\Includes\Exclusions\ExclusionRepository;

/**
 * Admin page: Exclusion List (global exclusions for all pricing rules).
 */
class ExclusionPage {

    public static function register(): void {
        add_submenu_page(
            'wpulse-pricing-rules',
            __('Exclusion List', 'wpulse-pricing-rules-for-woocommerce'),
            __('Exclusion List', 'wpulse-pricing-rules-for-woocommerce'),
            'manage_woocommerce',
            'wpulse-exclusion-list',
            [__CLASS__, 'render']
        );
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueueAssets'], 10, 1);
    }

    public static function enqueueAssets(string $hook): void {
        if ($hook !== 'wpulse-pricing-rules_page_wpulse-exclusion-list') {
            return;
        }
        $url = WPULSE_PRICING_RULES_URL;
        $ver = WPULSE_PRICING_RULES_VERSION;
        wp_enqueue_style(
            'wpulse-exclusion-list',
            $url . 'assets/admin/exclusion-list.css',
            [],
            $ver
        );
        wp_enqueue_script(
            'wpulse-exclusion-list',
            $url . 'assets/admin/exclusion-list.js',
            ['jquery'],
            $ver,
            true
        );
        wp_localize_script('wpulse-exclusion-list', 'wpulseExclusionList', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce(ExclusionAjax::NONCE_ACTION),
            'i18n'    => [
                'addExclusion'     => __('Add exclusion', 'wpulse-pricing-rules-for-woocommerce'),
                'emptyTitle'       => __('Your exclusion list is empty...', 'wpulse-pricing-rules-for-woocommerce'),
                'emptyHelp'        => __('Click on the "Add exclusion" button to exclude a product, a category or a tag!', 'wpulse-pricing-rules-for-woocommerce'),
                'modalTitle'       => __('Add an exclusion to the list', 'wpulse-pricing-rules-for-woocommerce'),
                'exclusionType'    => __('Exclusion type', 'wpulse-pricing-rules-for-woocommerce'),
                'chooseProducts'   => __('Choose products to add', 'wpulse-pricing-rules-for-woocommerce'),
                'chooseCategories' => __('Choose categories to add', 'wpulse-pricing-rules-for-woocommerce'),
                'chooseTags'       => __('Choose tags to add', 'wpulse-pricing-rules-for-woocommerce'),
                'searchProduct'    => __('Search for a product...', 'wpulse-pricing-rules-for-woocommerce'),
                'searchCategory'   => __('Search for a category...', 'wpulse-pricing-rules-for-woocommerce'),
                'searchTag'        => __('Search for a tag...', 'wpulse-pricing-rules-for-woocommerce'),
                'addToList'        => __('Add the exclusion to the list', 'wpulse-pricing-rules-for-woocommerce'),
                'typeProduct'      => __('Product', 'wpulse-pricing-rules-for-woocommerce'),
                'typeCategory'     => __('Category', 'wpulse-pricing-rules-for-woocommerce'),
                'typeTag'          => __('Tag', 'wpulse-pricing-rules-for-woocommerce'),
                'type'             => __('Type', 'wpulse-pricing-rules-for-woocommerce'),
                'name'             => __('Name', 'wpulse-pricing-rules-for-woocommerce'),
                'remove'           => __('Remove', 'wpulse-pricing-rules-for-woocommerce'),
                'loading'          => __('Loading...', 'wpulse-pricing-rules-for-woocommerce'),
                'error'            => __('An error occurred.', 'wpulse-pricing-rules-for-woocommerce'),
                'selectItem'       => __('Please select an item.', 'wpulse-pricing-rules-for-woocommerce'),
            ],
        ]);
    }

    public static function render(): void {
        $exclusions = ExclusionRepository::getAll();
        $with_names = self::attachNames($exclusions);
        ?>
        <div class="wrap wpulse_exclusion_list">
            <h1 class="wp-heading-inline"><?php esc_html_e('Exclusion List', 'wpulse-pricing-rules-for-woocommerce'); ?></h1>
            <p class="wpulse_exclusion_list__description">
                <?php esc_html_e('A list of products to exclude from all rules', 'wpulse-pricing-rules-for-woocommerce'); ?>
            </p>

            <div id="wpulse-exclusion-empty-state" class="wpulse_exclusion_list__empty" <?php echo empty($with_names) ? '' : ' style="display:none;"'; ?>>
                <div class="wpulse_exclusion_list__empty-icon" aria-hidden="true"></div>
                <p class="wpulse_exclusion_list__empty-title"><?php esc_html_e('Your exclusion list is empty...', 'wpulse-pricing-rules-for-woocommerce'); ?></p>
                <p class="wpulse_exclusion_list__empty-help">
                    <?php esc_html_e('Click on the "Add exclusion" button to exclude a product, a category or a tag!', 'wpulse-pricing-rules-for-woocommerce'); ?>
                </p>
                <button type="button" class="button button-primary" id="wpulse-exclusion-btn-add"><?php esc_html_e('Add exclusion', 'wpulse-pricing-rules-for-woocommerce'); ?></button>
            </div>

            <div id="wpulse-exclusion-list-wrap" class="wpulse_exclusion_list__table-wrap" <?php echo empty($with_names) ? ' style="display:none;"' : ''; ?>>
                <p>
                    <button type="button" class="button button-primary" id="wpulse-exclusion-btn-add-2"><?php esc_html_e('Add exclusion', 'wpulse-pricing-rules-for-woocommerce'); ?></button>
                </p>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th class="column-type"><?php esc_html_e('Type', 'wpulse-pricing-rules-for-woocommerce'); ?></th>
                            <th class="column-name"><?php esc_html_e('Name', 'wpulse-pricing-rules-for-woocommerce'); ?></th>
                            <th class="column-actions"><?php esc_html_e('Remove', 'wpulse-pricing-rules-for-woocommerce'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="wpulse-exclusion-tbody">
                        <?php foreach ($with_names as $row) : ?>
                            <tr data-id="<?php echo esc_attr($row['id']); ?>">
                                <td class="column-type"><?php echo esc_html($row['type_label']); ?></td>
                                <td class="column-name"><?php echo esc_html($row['name']); ?></td>
                                <td class="column-actions">
                                    <button type="button" class="button button-small wpulse-exclusion-remove" data-id="<?php echo esc_attr($row['id']); ?>"><?php esc_html_e('Remove', 'wpulse-pricing-rules-for-woocommerce'); ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div id="wpulse-exclusion-modal" class="wpulse_exclusion_modal" role="dialog" aria-modal="true" aria-labelledby="wpulse-exclusion-modal-title" style="display:none;">
            <div class="wpulse_exclusion_modal__backdrop"></div>
            <div class="wpulse_exclusion_modal__box">
                <h2 id="wpulse-exclusion-modal-title" class="wpulse_exclusion_modal__title"><?php esc_html_e('Add an exclusion to the list', 'wpulse-pricing-rules-for-woocommerce'); ?></h2>
                <div class="wpulse_exclusion_modal__body">
                    <p class="wpulse_exclusion_modal__field">
                        <label for="wpulse-exclusion-type"><?php esc_html_e('Exclusion type', 'wpulse-pricing-rules-for-woocommerce'); ?></label>
                        <select id="wpulse-exclusion-type" class="wpulse_exclusion_modal__select">
                            <option value="product"><?php esc_html_e('Product', 'wpulse-pricing-rules-for-woocommerce'); ?></option>
                            <option value="category"><?php esc_html_e('Category', 'wpulse-pricing-rules-for-woocommerce'); ?></option>
                            <option value="tag"><?php esc_html_e('Tag', 'wpulse-pricing-rules-for-woocommerce'); ?></option>
                        </select>
                    </p>
                    <p id="wpulse-exclusion-selector-wrap" class="wpulse_exclusion_modal__field">
                        <label id="wpulse-exclusion-selector-label"><?php esc_html_e('Choose products to add', 'wpulse-pricing-rules-for-woocommerce'); ?></label>
                        <span id="wpulse-exclusion-selector-container"><!-- filled by JS --></span>
                    </p>
                    <p id="wpulse-exclusion-modal-error" class="wpulse_exclusion_modal__error" style="display:none;"></p>
                </div>
                <div class="wpulse_exclusion_modal__footer">
                    <button type="button" class="button" id="wpulse-exclusion-modal-cancel"><?php esc_html_e('Cancel', 'wpulse-pricing-rules-for-woocommerce'); ?></button>
                    <button type="button" class="button button-primary" id="wpulse-exclusion-modal-submit"><?php esc_html_e('Add the exclusion to the list', 'wpulse-pricing-rules-for-woocommerce'); ?></button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Return exclusions with labels and names for JSON (e.g. AJAX refresh).
     *
     * @return array<int, array{id: int, type_label: string, name: string}>
     */
    public static function getListWithNames(): array {
        return self::attachNames(ExclusionRepository::getAll());
    }

    /**
     * @param array<int, array{id: int, exclusion_type: string, object_id: int}> $exclusions
     * @return array<int, array{id: int, exclusion_type: string, object_id: int, type_label: string, name: string}>
     */
    private static function attachNames(array $exclusions): array {
        $result = [];
        foreach ($exclusions as $row) {
            $type = $row['exclusion_type'];
            $id   = (int) $row['object_id'];
            $name = '';
            $label = '';
            if ($type === 'product') {
                $product = wc_get_product($id);
                $name   = $product ? $product->get_name() : sprintf(__('Product #%d', 'wpulse-pricing-rules-for-woocommerce'), $id);
                $label  = __('Product', 'wpulse-pricing-rules-for-woocommerce');
            } elseif ($type === 'category') {
                $term = get_term($id, 'product_cat');
                $name  = ($term && !is_wp_error($term)) ? $term->name : sprintf(__('Category #%d', 'wpulse-pricing-rules-for-woocommerce'), $id);
                $label = __('Category', 'wpulse-pricing-rules-for-woocommerce');
            } else {
                $term  = get_term($id, 'product_tag');
                $name  = ($term && !is_wp_error($term)) ? $term->name : sprintf(__('Tag #%d', 'wpulse-pricing-rules-for-woocommerce'), $id);
                $label = __('Tag', 'wpulse-pricing-rules-for-woocommerce');
            }
            $result[] = [
                'id'         => $row['id'],
                'exclusion_type' => $type,
                'object_id'  => $id,
                'type_label' => $label,
                'name'       => $name,
            ];
        }
        return $result;
    }
}
