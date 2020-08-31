define([
    '/assets/js/text.js!/admin/block_modal',  //block_modal html
    // '/assets/js/text.js!/admin/test_modal'  //block_modal html
], function (block_modal) {
    return {
        getHello: function () {
            return 'Hello World';
        },

        viewModal: function (_this) {
            var id = $(_this).attr('data');
            var modal = new Custombox.modal({
                content: {
                    effect: 'fadein',
                    target: '#custom-modal'
                }
            });
            $('.custom-modal-text').empty();
            $('.custom-modal-text').append(block_modal);
            $("#block_id").val(id);
            console.log(id)
            modal.open();
        },

        onChangeModal: function (_this) {
            // console.log(_this)
            console.log(_this.val())
        },

        block_save: function (id, hour, day) {
            $.post('/admin/insert_block', {id: id, hour: hour, day: day}, function (res) {
                console.log(res)
            })
        }
    };
});