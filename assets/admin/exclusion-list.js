/**
 * WPulse Exclusion List – modal, type-based selector, AJAX add/delete, list refresh.
 */
(function ($) {
    'use strict';

    var config = window.wpulseExclusionList || {};
    var ajaxUrl = config.ajaxUrl || '';
    var nonce = config.nonce || '';
    var i18n = config.i18n || {};

    var searchTimeout;
    var selectedItem = null; // { id, name } for current type

    function openModal() {
        selectedItem = null;
        renderSelectorByType($('#wpulse-exclusion-type').val());
        $('#wpulse-exclusion-modal-error').hide().text('');
        $('#wpulse-exclusion-modal').show();
        $('#wpulse-exclusion-type').trigger('focus');
    }

    function closeModal() {
        $('#wpulse-exclusion-modal').hide();
    }

    function getTypeLabel(type) {
        if (type === 'product') return i18n.typeProduct || 'Product';
        if (type === 'category') return i18n.typeCategory || 'Category';
        if (type === 'tag') return i18n.typeTag || 'Tag';
        return type;
    }

    function renderSelectorByType(type) {
        var container = $('#wpulse-exclusion-selector-container');
        var label = $('#wpulse-exclusion-selector-label');
        container.empty();
        selectedItem = null;

        if (type === 'product') {
            label.text(i18n.chooseProducts || 'Choose products to add');
            container.html(
                '<input type="text" id="wpulse-exclusion-search" class="wpulse_exclusion_modal__input" placeholder="' + (i18n.searchProduct || 'Search for a product...') + '" autocomplete="off">' +
                '<ul id="wpulse-exclusion-results" class="wpulse_exclusion_modal__results"></ul>' +
                '<p id="wpulse-exclusion-selected" class="wpulse_exclusion_modal__selected"></p>'
            );
            bindSearch('wpulse_search_products', function () { return $('#wpulse-exclusion-search').val(); });
        } else if (type === 'category') {
            label.text(i18n.chooseCategories || 'Choose categories to add');
            container.html(
                '<input type="text" id="wpulse-exclusion-search" class="wpulse_exclusion_modal__input" placeholder="' + (i18n.searchCategory || 'Search for a category...') + '" autocomplete="off">' +
                '<ul id="wpulse-exclusion-results" class="wpulse_exclusion_modal__results"></ul>' +
                '<p id="wpulse-exclusion-selected" class="wpulse_exclusion_modal__selected"></p>'
            );
            bindSearch('wpulse_search_categories', function () { return $('#wpulse-exclusion-search').val(); });
        } else {
            label.text(i18n.chooseTags || 'Choose tags to add');
            container.html(
                '<input type="text" id="wpulse-exclusion-search" class="wpulse_exclusion_modal__input" placeholder="' + (i18n.searchTag || 'Search for a tag...') + '" autocomplete="off">' +
                '<ul id="wpulse-exclusion-results" class="wpulse_exclusion_modal__results"></ul>' +
                '<p id="wpulse-exclusion-selected" class="wpulse_exclusion_modal__selected"></p>'
            );
            bindSearch('wpulse_search_tags', function () { return $('#wpulse-exclusion-search').val(); });
        }
    }

    function bindSearch(action, getSearch) {
        $(document).off('input.wpulseExclusionSearch');
        $(document).on('input.wpulseExclusionSearch', '#wpulse-exclusion-search', function () {
            var self = this;
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () {
                var search = getSearch();
                if (search.length < 2) {
                    $('#wpulse-exclusion-results').empty();
                    return;
                }
                $.get(ajaxUrl, {
                    action: action,
                    nonce: nonce,
                    search: search,
                    per_page: 20
                })
                    .done(function (res) {
                        if (res.success && res.data && res.data.length) {
                            var html = res.data.map(function (item) {
                                return '<li class="wpulse_exclusion_modal__result-item" data-id="' + item.id + '" data-name="' + escapeHtml(item.name) + '">' + escapeHtml(item.name) + '</li>';
                            }).join('');
                            $('#wpulse-exclusion-results').html(html).show();
                        } else {
                            $('#wpulse-exclusion-results').html('<li class="wpulse_exclusion_modal__result-item wpulse_exclusion_modal__result-item--none">No results</li>').show();
                        }
                    })
                    .fail(function () {
                        $('#wpulse-exclusion-results').empty();
                    });
            }, 300);
        });

        $(document).off('click.wpulseExclusionResult');
        $(document).on('click.wpulseExclusionResult', '#wpulse-exclusion-results .wpulse_exclusion_modal__result-item[data-id]', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            selectedItem = { id: id, name: name };
            $('#wpulse-exclusion-selected').text(i18n.name || 'Name' + ': ' + name).show();
            $('#wpulse-exclusion-results').empty().hide();
            $('#wpulse-exclusion-search').val('');
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function refreshList() {
        $.post(ajaxUrl, {
            action: 'wpulse_get_exclusions',
            nonce: nonce
        })
            .done(function (res) {
                if (!res.success || !Array.isArray(res.data)) return;
                var list = res.data;
                var tbody = $('#wpulse-exclusion-tbody');
                var emptyState = $('#wpulse-exclusion-empty-state');
                var listWrap = $('#wpulse-exclusion-list-wrap');

                if (list.length === 0) {
                    emptyState.show();
                    listWrap.hide();
                    tbody.empty();
                    return;
                }
                emptyState.hide();
                listWrap.show();
                tbody.empty();
                list.forEach(function (row) {
                    var tr = $('<tr></tr>').attr('data-id', row.id);
                    tr.append($('<td class="column-type"></td>').text(row.type_label));
                    tr.append($('<td class="column-name"></td>').text(row.name));
                    tr.append(
                        $('<td class="column-actions"></td>').html(
                            '<button type="button" class="button button-small wpulse-exclusion-remove" data-id="' + row.id + '">' + (i18n.remove || 'Remove') + '</button>'
                        )
                    );
                    tbody.append(tr);
                });
            })
            .fail(function () {
                location.reload();
            });
    }

    function submitAdd() {
        var type = $('#wpulse-exclusion-type').val();
        if (!selectedItem || !selectedItem.id) {
            $('#wpulse-exclusion-modal-error').text(i18n.selectItem || 'Please select an item.').show();
            return;
        }
        $('#wpulse-exclusion-modal-error').hide();
        var $btn = $('#wpulse-exclusion-modal-submit').prop('disabled', true).text(i18n.loading || 'Loading...');

        $.post(ajaxUrl, {
            action: 'wpulse_add_exclusion',
            nonce: nonce,
            exclusion_type: type,
            object_id: selectedItem.id
        })
            .done(function (res) {
                if (res.success) {
                    closeModal();
                    refreshList();
                } else {
                    $('#wpulse-exclusion-modal-error').text(res.data && res.data.message ? res.data.message : i18n.error).show();
                }
            })
            .fail(function () {
                $('#wpulse-exclusion-modal-error').text(i18n.error || 'An error occurred.').show();
            })
            .always(function () {
                $btn.prop('disabled', false).text(i18n.addToList || 'Add the exclusion to the list');
            });
    }

    $(function () {
        $('#wpulse-exclusion-btn-add, #wpulse-exclusion-btn-add-2').on('click', openModal);
        $('#wpulse-exclusion-modal-cancel, #wpulse-exclusion-modal .wpulse_exclusion_modal__backdrop').on('click', closeModal);
        $('#wpulse-exclusion-modal-submit').on('click', submitAdd);
        $('#wpulse-exclusion-type').on('change', function () {
            renderSelectorByType($(this).val());
        });

        $(document).on('click', '.wpulse-exclusion-remove', function () {
            var id = $(this).data('id');
            var $row = $(this).closest('tr');
            if (!id) return;
            $row.css('opacity', '0.5');
            $.post(ajaxUrl, {
                action: 'wpulse_delete_exclusion',
                nonce: nonce,
                id: id
            })
                .done(function (res) {
                    if (res.success) {
                        refreshList();
                    } else {
                        $row.css('opacity', '1');
                        alert(res.data && res.data.message ? res.data.message : i18n.error);
                    }
                })
                .fail(function () {
                    $row.css('opacity', '1');
                    alert(i18n.error || 'An error occurred.');
                });
        });

        // Load categories/tags on first open when type is category/tag (optional: show all on focus)
        $(document).on('focus.wpulseExclusionSearch', '#wpulse-exclusion-search', function () {
            var $results = $('#wpulse-exclusion-results');
            if ($results.find('li').length === 0 && $(this).val().length === 0) {
                var type = $('#wpulse-exclusion-type').val();
                var action = type === 'category' ? 'wpulse_search_categories' : type === 'tag' ? 'wpulse_search_tags' : null;
                if (action) {
                    $.get(ajaxUrl, { action: action, nonce: nonce })
                        .done(function (res) {
                            if (res.success && res.data && res.data.length) {
                                var html = res.data.map(function (item) {
                                    return '<li class="wpulse_exclusion_modal__result-item" data-id="' + item.id + '" data-name="' + escapeHtml(item.name) + '">' + escapeHtml(item.name) + '</li>';
                                }).join('');
                                $results.html(html).show();
                            }
                        });
                }
            }
        });
    });
})(jQuery);
