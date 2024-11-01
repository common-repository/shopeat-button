(function() {
    tinymce.create('tinymce.plugins.shopeat_ingredients', {
        init : function(ed, url) {
            ed.addButton('shopeat_ingredients', {
                title : 'Mark ingredients',
                image : url+'/../images/icon-ingredients.gif',
                onclick : function() {
                     ed.selection.setContent('[shopeat_ingredients]' + ed.selection.getContent() + '[/shopeat_ingredients]');

                }
            });
        },
        createControl : function(n, cm) {
            return null;
        },
    });
    tinymce.PluginManager.add('shopeat_ingredients', tinymce.plugins.shopeat_ingredients);
})();
