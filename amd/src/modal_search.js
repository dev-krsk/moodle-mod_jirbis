define(['jquery', 'core/notification', 'core/custom_interaction_events', 'core/modal', 'core/templates', 'core/modal_registry'],
    function ($, Notification, CustomEvents, Modal, Templates, ModalRegistry) {
        var registered = false;
        var SELECTORS = {
            SOURCE_SELECT: '#selectModalSource',
            SEARCH_INPUT: '#inputModalSearch',
            SEARCH_BUTTON: '[data-action="search"]',
            CONTENT_BLOCK: "[data-action='content_block']",
            MODAL_CONTENT: ".modal-content",
        };

        const LIMIT_ON_PAGE = 10;

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

                if (undefined === ModalSearch.prototype.course) {
                    Notification.exception({message: 'Не передан идентификатор курса'});
                }

                let body = this.getBody();

                let parameters = {
                    id: ModalSearch.prototype.course,
                    limit: LIMIT_ON_PAGE,
                    source: body.find(SELECTORS.SOURCE_SELECT).val(),
                    query: body.find(SELECTORS.SEARCH_INPUT).val().trim(),
                };

                if (!parameters.source || parameters.source.length === 0) {
                    Notification.alert('Ошибка', 'Поле источник не может бюыть пустым');
                    return;
                }
                if (!parameters.query || parameters.query.length === 0) {
                    Notification.alert('Ошибка', 'Поле поиска не может бюыть пустым');
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: "/mod/jirbis/api/index.php?" + $.param(parameters),
                    beforeSend: function() {
                        body.find(SELECTORS.CONTENT_BLOCK).empty();
                        body.closest(SELECTORS.MODAL_CONTENT).addClass('loading');
                    },
                    success: function (data) {
                        if (data.data.length === 0) {
                            body.find(SELECTORS.CONTENT_BLOCK).html('<tr><td colspan="3">Не найдено</td></tr>');
                        } else {
                            for (let i = 0; i < data.data.length; i++) {
                                let $tr = $(`<tr><td>${data.data[i].name}</td><td>${data.data[i].url}</td><td><button>Добавить</button></td></tr>`);
                                body.find(SELECTORS.CONTENT_BLOCK).append($tr);
                            }
                        }
                    },
                    error: function (jqXHR) {
                        Notification.exception(jqXHR);
                    },
                    complete: function() {
                        body.closest(SELECTORS.MODAL_CONTENT).removeClass('loading');
                    }
                });

            }.bind(this));
        };

        ModalSearch.prototype.setCourse = function (id) {
            ModalSearch.prototype.course = id;
        }

        // Automatically register with the modal registry the first time this module is imported so that you can create modals
        // of this type using the modal factory.
        if (!registered) {
            ModalRegistry.register(ModalSearch.TYPE, ModalSearch, 'mod_jirbis/modal_search');
            registered = true;
        }

        return ModalSearch;
    });