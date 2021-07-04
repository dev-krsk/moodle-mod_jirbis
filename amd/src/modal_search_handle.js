define(['jquery', 'core/modal_factory', 'mod_jirbis/modal_search'], function ($, ModalFactory, ModalSearch) {
    return {
        init: function (id) {
            var trigger = $('#id_modal_search_show');

            ModalFactory.create({type: ModalSearch.TYPE, large: true}, trigger).done(function (modal) {
                ModalSearch.prototype.setCourse(id);
            });
        }
    };
});