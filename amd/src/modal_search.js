define(['jquery', 'core/notification', 'core/custom_interaction_events', 'core/modal', 'core/templates', 'core/modal_registry'],
    function ($, Notification, CustomEvents, Modal, Templates, ModalRegistry) {
        var registered = false;
        var SELECTORS = {
            FORM_CONTENT_NAME: "[name='content_name']",
            FORM_CONTENT_URL: "[name='content_url']",
            SOURCE_SELECT: '#selectModalSource',
            SEARCH_INPUT: '#inputModalSearch',
            SEARCH_BUTTON: '[data-action="search"]',
            CONTENT_BLOCK: "[data-action='content_block']",
            MODAL_CONTENT: ".modal-content",
            PAGE_SELECT: ".selectPageSource",
            ADD_BOOK: "[name='add_book']",
            BOOK_NAME: "td.name",
            BOOK_SOURCE: "td.source",
        };

        var CLASS = {
            LOADING: 'mod_jirbis-loading',
        };

        /**
         * Constructor for the Modal.
         *
         * @param {object} root The root jQuery element for the modal
         */
        var ModalSearch = function (root) {
            Modal.call(this, root);
            if (!this.getBody().find(SELECTORS.SOURCE_SELECT).length) {
                Notification.exception({message: 'No select source'});
            }
            if (!this.getBody().find(SELECTORS.SEARCH_INPUT).length) {
                Notification.exception({message: 'No input search box'});
            }
        };

        ModalSearch.TYPE = 'mod_jirbis-search';
        ModalSearch.prototype = Object.create(Modal.prototype);
        ModalSearch.prototype.constructor = ModalSearch;
        ModalSearch.prototype.course = undefined;
        ModalSearch.prototype.page = 1;
        ModalSearch.prototype.limit = 10;

        /**
         * Set up all of the event handling for the modal.
         *
         * @method registerEventListeners
         */
        ModalSearch.prototype.registerEventListeners = function (message) {
            // Apply parent event listeners.
            Modal.prototype.registerEventListeners.call(this);

            this.getModal().on(CustomEvents.events.activate, SELECTORS.SEARCH_BUTTON, function (e, data) {
                // Add your logic for when the cancel button is clicked.
                console.log(e, data);

                let body = this.getBody();

                ModalSearch.prototype.getSource(body, e, data);

            }.bind(this));

            this.getModal().on('change', SELECTORS.PAGE_SELECT, function (e, data) {
                // Add your logic for when the cancel button is clicked.
                console.log(e, data);

                let body = this.getBody();
                ModalSearch.prototype.setPage(e.currentTarget.value);

                ModalSearch.prototype.getSource(body, e, data);

            }.bind(this));

            this.getModal().on(CustomEvents.events.activate, SELECTORS.ADD_BOOK, function (e, data) {
                // Add your logic for when the cancel button is clicked.
                console.log(e, data);

                let name = $(e.target).closest('tr').find(SELECTORS.BOOK_NAME).html();
                let source = $(e.target).closest('tr').find(SELECTORS.BOOK_SOURCE).html();

                if (source.indexOf('http://library3knew/') === 0) {
                    source = source.replace('http://library3knew/', 'http://biblioteka.sibsau.ru/');
                }

                $(SELECTORS.FORM_CONTENT_NAME).val(name);
                $(SELECTORS.FORM_CONTENT_URL).val(source);

                $('[data-action="hide"]').trigger('click');
            }.bind(this));
        };

        ModalSearch.prototype.setCourse = function (id) {
            ModalSearch.prototype.course = id;
        }
        ModalSearch.prototype.setPage = function (id) {
            ModalSearch.prototype.page = id;
        }
        ModalSearch.prototype.setLimit = function (limit) {
            ModalSearch.prototype.page = limit;
        }

        ModalSearch.prototype.getSource = function (body, e, data) {
            if (undefined === ModalSearch.prototype.course) {
                Notification.exception({message: 'Не передан идентификатор курса'});
            }

            let parameters = {
                id: ModalSearch.prototype.course,
                limit: ModalSearch.prototype.limit,
                page: ModalSearch.prototype.page,
                source: body.find(SELECTORS.SOURCE_SELECT).val(),
                query: body.find(SELECTORS.SEARCH_INPUT).val().trim(),
            };

            if (!parameters.source || parameters.source.length === 0) {
                Notification.alert('Ошибка', 'Поле источник не может быть пустым');
                return;
            }
            if (!parameters.query || parameters.query.length === 0) {
                Notification.alert('Ошибка', 'Поле поиска не может быть пустым');
                return;
            }

            $.ajax({
                type: "POST",
                url: "/mod/jirbis/api/index.php?" + $.param(parameters),
                beforeSend: function() {
                    body.find(SELECTORS.CONTENT_BLOCK).empty();
                    body.find(SELECTORS.PAGE_SELECT).empty();
                    body.closest(SELECTORS.MODAL_CONTENT).addClass(CLASS.LOADING);
                },
                success: function (data) {
                    if (data.data.length === 0) {
                        body.find(SELECTORS.CONTENT_BLOCK).html('<tr><td colspan="3">Не найдено</td></tr>');
                    } else {
                        let result = data.data;
                        for (let i = 0; i < result.length; i++) {
                            let $tr = $(`<tr><td class="name">${result[i].name}</td><td class="source">${result[i].url}</td><td><button name="add_book">Добавить</button></td></tr>`);
                            body.find(SELECTORS.CONTENT_BLOCK).append($tr);
                        }

                        for (let i = 0; i < data.max_page; i++) {
                            body.find(SELECTORS.PAGE_SELECT).append($( '<option value="' + (i+1) + '">' + (i+1) + '</option>'));
                        }

                        body.find(SELECTORS.PAGE_SELECT).val(ModalSearch.prototype.page);
                    }
                },
                error: function (jqXHR) {
                    Notification.exception(jqXHR);
                },
                complete: function() {
                    body.closest(SELECTORS.MODAL_CONTENT).removeClass(CLASS.LOADING);
                }
            });
        }

        // Automatically register with the modal registry the first time this module is imported so that you can create modals
        // of this type using the modal factory.
        if (!registered) {
            ModalRegistry.register(ModalSearch.TYPE, ModalSearch, 'mod_jirbis/modal_search');
            registered = true;
        }

        return ModalSearch;
    });