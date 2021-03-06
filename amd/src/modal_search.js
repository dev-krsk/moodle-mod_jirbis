define(['jquery', 'core/notification', 'core/custom_interaction_events', 'core/modal', 'core/templates', 'core/modal_registry'],
    function ($, Notification, CustomEvents, Modal, Templates, ModalRegistry) {
        var registered = false;
        var SELECTORS = {
            FORM_CONTENT_NAME: "[name='content_name']",
            FORM_CONTENT_URL: "[name='content_url']",
            SOURCE_SELECT: '#selectModalSource',
            SEARCH_AUTHOR: '#inputModalAuthor',
            SEARCH_TITLE: '#inputModalTitle',
            SEARCH_KEY: '#inputModalKey',
            SEARCH_YEAR: '#inputModalYear',
            SEARCH_BUTTON: '[data-action="search"]',
            HEAD_BLOCK: "[data-table='head_block']",
            CONTENT_BLOCK: "[data-table='content_block']",
            MODAL_CONTENT: ".modal-content",
            PAGE_SELECT: ".selectPageSource",
            ADD_BOOK: "[name='add_book']",
            BOOK_NAME: "td.name",
            DATA_SOURCE: "source",
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
                Notification.exception({name: "Ошибка", message: 'No select source'});
            }
            if (!this.getBody().find(SELECTORS.SEARCH_AUTHOR).length) {
                Notification.exception({name: "Ошибка", message: 'No author search box'});
            }
            if (!this.getBody().find(SELECTORS.SEARCH_TITLE).length) {
                Notification.exception({name: "Ошибка", message: 'No title search box'});
            }
            if (!this.getBody().find(SELECTORS.SEARCH_KEY).length) {
                Notification.exception({name: "Ошибка", message: 'No key search box'});
            }
            if (!this.getBody().find(SELECTORS.SEARCH_YEAR).length) {
                Notification.exception({name: "Ошибка", message: 'No year search box'});
            }
        };

        ModalSearch.TYPE = 'mod_jirbis-search';
        ModalSearch.prototype = Object.create(Modal.prototype);
        ModalSearch.prototype.constructor = ModalSearch;
        ModalSearch.prototype.course = undefined;
        ModalSearch.prototype.debug = false;
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
                let body = this.getBody();

                ModalSearch.prototype.getSource(body, e, data);

            }.bind(this));

            this.getModal().on('change', SELECTORS.PAGE_SELECT, function (e, data) {
                let body = this.getBody();
                ModalSearch.prototype.setPage(e.currentTarget.value);

                ModalSearch.prototype.getSource(body, e, data);

            }.bind(this));

            this.getModal().on(CustomEvents.events.activate, SELECTORS.ADD_BOOK, function (e, data) {
                let name = $(e.target).closest('tr').find(SELECTORS.BOOK_NAME).html();
                let source = $(e.target).closest('tr').data(SELECTORS.DATA_SOURCE);

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
        ModalSearch.prototype.setDebug = function (debug) {
            ModalSearch.prototype.debug = parseInt(debug);
        }
        ModalSearch.prototype.setPage = function (id) {
            ModalSearch.prototype.page = id;
        }
        ModalSearch.prototype.setLimit = function (limit) {
            ModalSearch.prototype.page = limit;
        }

        ModalSearch.prototype.getSource = function (body, e, data) {
            if (undefined === ModalSearch.prototype.course) {
                Notification.exception({name: "Ошибка", message: 'Не передан идентификатор курса'});
            }

            let parameters = {
                id: ModalSearch.prototype.course,
                limit: ModalSearch.prototype.limit,
                page: ModalSearch.prototype.page,
                source: body.find(SELECTORS.SOURCE_SELECT).val(),
                author: body.find(SELECTORS.SEARCH_AUTHOR).val().trim(),
                title: body.find(SELECTORS.SEARCH_TITLE).val().trim(),
                key: body.find(SELECTORS.SEARCH_KEY).val().trim(),
                year: body.find(SELECTORS.SEARCH_YEAR).val().trim(),
            };

            if ((!parameters.author || parameters.author.length === 0)
                && (!parameters.title || parameters.title.length === 0)
                && (!parameters.key || parameters.key.length === 0)
                && (!parameters.year || parameters.year.length === 0)
            ) {
                Notification.alert('Ошибка', 'Одно из полей поиска должно быть заполнено');
                return;
            }
            if (!parameters.source || parameters.source.length === 0) {
                Notification.alert('Ошибка', 'Поле источник не может быть пустым');
                return;
            }

            $.ajax({
                type: "POST",
                url: "/mod/jirbis/api/index.php?" + $.param(parameters),
                beforeSend: function () {
                    body.find(SELECTORS.CONTENT_BLOCK).empty();
                    body.find(SELECTORS.PAGE_SELECT).empty();
                    body.closest(SELECTORS.MODAL_CONTENT).addClass(CLASS.LOADING);
                },
                success: function (data) {
                    let head = '';
                    head += '<th>Наименование</th>';
                    if (ModalSearch.prototype.debug) {
                        head += '<th>URL</th>';
                    }
                    head += '<th></th>';
                    body.find(SELECTORS.HEAD_BLOCK).find('tr').html(head);

                    if (data.data.length === 0) {
                        body.find(SELECTORS.CONTENT_BLOCK).html('<tr><td colspan="3">Не найдено</td></tr>');
                    } else {
                        let result = data.data.map((book) => {
                            if (book.url.indexOf('http://library3knew/') === 0)
                                book.url = book.url.replace('http://library3knew/', 'http://biblioteka.sibsau.ru/');

                            return book;
                        });
                        for (let i = 0; i < result.length; i++) {
                            let $tr = $('<tr></tr>');

                            $tr.data(SELECTORS.DATA_SOURCE, result[i].url);

                            $tr.append(`<td class="name">${result[i].name}</td>`);

                            if (ModalSearch.prototype.debug) {
                                $tr.append(`<td class="source">${result[i].url}</td>`);
                            }

                            if (result[i].url.indexOf('http') === 0) {
                                $tr.append(`<td><button name="add_book">Добавить</button></td>`);
                            } else {
                                $tr.append(`<td>Файл отсутствует на сервере библиотеки или удаленный доступ к файлу не доступен</td>`);
                            }

                            body.find(SELECTORS.CONTENT_BLOCK).append($tr);
                        }

                        for (let i = 0; i < data.max_page; i++) {
                            body.find(SELECTORS.PAGE_SELECT).append($('<option value="' + (i + 1) + '">' + (i + 1) + '</option>'));
                        }

                        body.find(SELECTORS.PAGE_SELECT).val(ModalSearch.prototype.page);
                    }
                },
                error: function (jqXHR) {
                    const exception = {name: "Ошибка", message:"Неизвестная ошибка"}

                    if (jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.error) {
                      exception.message = jqXHR.responseJSON.error;
                    }

                    Notification.exception(exception);
                },
                complete: function () {
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