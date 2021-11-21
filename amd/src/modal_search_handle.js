define(['jquery', 'core/modal_factory', 'mod_jirbis/modal_search'], function ($, ModalFactory, ModalSearch) {
    return {
        init: function (id, debug) {
            var trigger = $('#id_modal_search_show');

            // Для того чтобы после открытия модалки она была больше
            trigger.click(function () {
                setTimeout(function () {
                    $('.modal-dialog').addClass('modal-xl');
                }, 50);
            });

            ModalFactory.create({type: ModalSearch.TYPE, large: true}, trigger).done(function (modal) {
                ModalSearch.prototype.setCourse(id);
                ModalSearch.prototype.setDebug(debug);
            });
        }
    };
});