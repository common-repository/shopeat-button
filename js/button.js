(function() {
    tinymce.create('tinymce.plugins.shopeat', {
        init : function(ed, url) {
            ed.addButton('shopeat', {
                title : 'Add ShopEat button',
                image : url+'/../images/icon.png',
                onclick : function() {
                     ed.selection.setContent('[shopeat_button]');
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        },
    });
    tinymce.PluginManager.add('shopeat', tinymce.plugins.shopeat);
})();
